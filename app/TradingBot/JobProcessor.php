<?php

namespace App\TradingBot;

use App\Jobs\ProcessTradingBotResponseJob;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\Suggestion;
use App\Models\Trade;
use App\Models\TradingBotJob;
use App\Models\User;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\Requests\ExchangeSuggestionsRequest;
use App\TradingBot\Requests\JobRequest;
use App\TradingBot\Requests\SuggestionsRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;
use Psr\Log\LoggerInterface;

/**
 * Handles processing of trading bot jobs and their results. Uses local queue to
 * dispatch jobs for each job in progress and handles responses.
 *
 * Class JobProcessor
 * @package App\TradingBot
 */
class JobProcessor
{
    /**
     * @var TradingBot
     */
    private $tradingBot;
    /**
     * @var Log
     */
    private $log;

    public function __construct(TradingBot $tradingBot, LoggerInterface $log)
    {
        $this->tradingBot = $tradingBot;
        $this->log = $log;
    }

    /**
     * Processes a TradingBotJob by asking the trading bot API for trading bot job status. If job
     * is completed the response data is processed. If job is still in progress, a local job
     * is dispatched to process the trading bot job again after an incremented delay.
     *
     * @param TradingBotJob $tradingBotJob
     */
    public function processJob(TradingBotJob $tradingBotJob)
    {
        $response = $this->tradingBot->getJob(new JobRequest(['job_id' => $tradingBotJob->job_id]));
        //$this->log->info("Processing trading bot job", array_only($response, ['job_id', 'job_status']));
        $status = array_get($response, 'job_status');
        if ($error = array_get($response, 'error')) {
            $this->handleJobError($tradingBotJob, $error);

            return;
        }
        if ($status == 'in_progress') {
            $this->processInProgressJob($tradingBotJob, array_get($response, 'data'));
            $this->dispatchProcessingJob($tradingBotJob);

            return;
        }
        if ($status == 'completed') {
            $this->processCompletedJob($tradingBotJob, array_get($response, 'data'));
        }
    }

    protected function processInProgressJob(TradingBotJob $tradingBotJob, $data)
    {
        $tradingBotJob->touch();
        if (! $data) {
            return null;
        }
        //$this->log->info("Processing in-progress trading bot job", $tradingBotJob->toArray());
        $result = null;
        switch ($tradingBotJob->job_type) {
            case 'buy':
                $result = $this->processInProgressBuyJob($data);
                break;
            default:
                $this->log->error("Cannot process in-progress trading bot job of type $tradingBotJob->job_type", [
                    'data_count' => count($data)
                ]);
        }

        return $result;
    }

    /**
     * Processes data from a completed job based on the job type.
     *
     * @param TradingBotJob $tradingBotJob
     * @param $data
     */
    protected function processCompletedJob(TradingBotJob $tradingBotJob, $data)
    {
        //$this->log->info("Processing completed trading bot job", $tradingBotJob->toArray());
        $result = null;
        switch ($tradingBotJob->job_type) {
            case 'suggestions':
                $result = $this->processSuggestionsJob($data);
                break;
            case 'buy':
                $result = $this->processBuyJob($data);
                break;
            default:
                $this->log->error("Cannot process trading bot job of type $tradingBotJob->job_type", [
                    'data_count' => count($data)
                ]);
        }

        $tradingBotJob->delete();
        // TODO Log how long it took for job to be processed

        return $result;
    }

    protected function handleJobError(TradingBotJob $tradingBotJob, $error)
    {
        $data = $tradingBotJob->toArray();
        $data['error'] = $error;
        $this->log->error("A trading bot job error occurred", $data);
        $tradingBotJob->delete();
    }

    /**
     * Dispatches all non-dispatched trading bot jobs for a specified job type.
     *
     * @param $jobType
     */
    public function dispatchProcessingJobs($jobType)
    {
        TradingBotJob::byJobType($jobType)
            ->hasNotDispatchedJob()
            ->get()
            ->each(function ($tradingBotJob) {
                $this->dispatchProcessingJob($tradingBotJob);
            });
    }

    /**
     * Dispatches a ProcessTradingBotJob which handles trading bot job response.
     *
     * @param TradingBotJob $tradingBotJob
     */
    protected function dispatchProcessingJob(TradingBotJob $tradingBotJob)
    {
        //$this->log->info("Dispatching processing job for trading bot job", $tradingBotJob->toArray());
        $job = new ProcessTradingBotResponseJob($tradingBotJob);
        if ($tradingBotJob->dispatch_count > 0) {
            $job->delay(Carbon::now()->addSeconds($tradingBotJob->dispatch_count));
        }

        dispatch($job);

        $tradingBotJob->dispatch_count++;
        $tradingBotJob->save();
    }

    /**
     * Creates TradingBotJobs for fetching and updating locally cached suggestions.
     */
    public function createAndProcessSuggestionJobs()
    {

    }

    /**
     * Processes data from a completed suggestion job.
     *
     * @param array $suggestionData
     */
    protected function processSuggestionsJob(array $suggestionData)
    {
        // TODO Implement validator here
        foreach ($suggestionData as $data) {
            $suggestion = Suggestion::firstOrNew(array_only($data, ['exchange', 'coin']));
            $data['impact_1hr'] = array_get($data, '1hr_impact');
            $data['impact_1hr_change_arrow'] = array_get($data, '1hr_impact_change_arrow');
            $data['impact_1hr_change_score'] = array_get($data, '1hr_impact_change_score');
            $suggestion->fill($data);
            $suggestion->save();
        }
    }

    public function createAndProcessBuyJob($arguments, User $user, ExchangeAccount $account)
    {
        // We keep account separate here as to not serialize key and decrypted secret openly in database
        $response = $this->tradingBot->buy(new BuyRequest(array_merge($arguments, [
            'key' => $account->key,
            'secret' => $account->secret
        ])));
        if ($error = array_get($response, 'error')) {
            $this->log->error('A trading bot error occurred while processing job request', $response);
            throw new \Exception($error, 1);
        }
        // We timestamp arguments to allow multiple jobs with same base arguments to exist
        $arguments['timestamp'] = time();

        $this->createOrResetTradingBotJob([
            'job_id' => array_get($response, 'job_id'),
            'job_type' => 'buy',
            'job_arguments' => $arguments
        ]);
        $this->dispatchProcessingJobs('buy');

        // Response should contain order UUID
        // TODO Handle case when not present?
        $data = [
            'exchange_id' => array_get($arguments, 'exchange'),
            'base_coin_id' => array_get($arguments, 'base'),
            'target_coin_id' => array_get($arguments, 'coin'),
            'user_id' => $user->id,
            'status' => Trade::STATUS_BUY_ORDER
        ];
        $trade = Trade::create(array_merge(array_get($response, 'data', []), $data));

        return $trade;
    }

    protected function processInProgressBuyJob(array $tradeData)
    {
        $this->processBuyJob($tradeData);
    }

    protected function processBuyJob(array $tradeData)
    {
        $orderUuid = array_get($tradeData, 'order_uuid');
        $trade = Trade::byOrderUuid($orderUuid)
            ->notPartial()
            ->first();

        $tradeData['status'] = array_get($tradeData, 'is_open', true)
            ? Trade::STATUS_BUY_ORDER : Trade::STATUS_BOUGHT;
        $trade->fill($tradeData);
        $trade->save();

        // TODO See how to handle partial buys
        // Trade::byOrderUuid($orderUuid)->partial()->get()
    }

    /**
     * Creates a new TradingBotJob or resets the dispatch_count if a job
     * with same parameters is already active and long-running. Resetting
     * the dispatch_count will increase the frequency of requests sent to
     * trading bot and thus behave as if a fresh job has been scheduled.
     *
     * @param $data
     */
    protected function createOrResetTradingBotJob($data)
    {
        $arguments = array_get($data, 'job_arguments');
        $tradingBotJob = TradingBotJob::byJobArguments($arguments)->first();
        if ($tradingBotJob) {
            $tradingBotJob->dispatch_count = 1;
            $tradingBotJob->save();
        } else {
            TradingBotJob::create($data);
        }
    }
}