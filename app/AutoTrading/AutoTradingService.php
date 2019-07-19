<?php

namespace App\AutoTrading;

use App\AutoTrading\Strategies\AdvancedStrategy;
use App\AutoTrading\Strategies\SimpleStrategy;
use App\Jobs\AutoTradeJob;
use App\Models\Cycle;
use App\Models\ExchangeAccount;
use App\Models\Round;
use App\Models\Trade;
use App\Services\TradeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Class AutoTradingService
 * @package App\Services
 */
class AutoTradingService
{
    /**
     * @var TradeService
     */
    private $tradeService;

    public function __construct(TradeService $tradeService)
    {
        $this->tradeService = $tradeService;
    }

    /**
     * @param $roundLength
     * @param $granularity
     * @param $duration
     * @return float
     * @deprecated Duration is now known
     */
    public function calculateCycleCount($roundLength, $granularity, $duration)
    {
        if ($granularity == 'hours' && $duration > 0) {
            return ceil($roundLength * 24 / $duration);
        } elseif ($granularity == 'days' && $duration > 0) {
            return ceil($roundLength / $duration);
        } else {
            throw new InvalidArgumentException("Invalid combination of round and cycle parameters provided");
        }
    }

    /**
     *
     */
    public function processActiveRounds()
    {
        Round::active()
            ->get()
            ->each(function ($round) {
                Log::info("Dispatching AutoTradeJob for round", [
                    'strategy' => 'simple',
                    'round' => $round->id
                ]);
                dispatch(new AutoTradeJob($round));
            });
    }

    /**
     * Begins the round by creating a Round record and associated Cycle records. Optionally accepts a second
     * parameter in form of Cycle model from which unsold BOUGHT trades should be passed on to first cycle in
     * the new round.
     *
     * @param ExchangeAccount $account
     * @param Cycle|null $previousCycle
     * @return Round
     * @throws \Exception
     */
    public function startRound(ExchangeAccount $account, Cycle $previousCycle = null)
    {
        if (env('APP_ENV') == 'staging' && $account->id != 1281) {
            return false;
        }
        if (! $previousCycle && Round::active()->where('exchange_account_id', $account->id)->exists()) {
            Log::warning("Attempted to start a new round while one active");

            return false;
        }
        // TODO Validate necessary settings before starting round
        try {
            DB::beginTransaction();
            $start = Carbon::now();
            $end = $start->copy();
            $duration = $account->auto_global_round_duration;
            if ($account->auto_global_round_granularity == 'days') {
                $duration = $duration * 24;
            }
            if ($duration < 1) {
                throw new InvalidArgumentException("Cannot start round with less than one hour duration");
            }
            $end->addHours($duration);
            $cycleLength = floor($duration * 60 * 60 / $account->auto_global_cycles);

            $round = Round::create([
                'exchange_account_id' => $account->id,
                'start_at' => $start,
                'end_at' => $end,
                'cycle_count' => $account->auto_global_cycles,
                'cycle_length' => $cycleLength,
                'strategy' => $account->auto_global_strategy
            ]);
            $cycleStart = $start->copy();
            $cycleEnd = $start->copy();
            $cycleEnd->addSeconds($cycleLength);
            $cycles = [];
            for ($i = 0; $i < $account->auto_global_cycles; $i++) {
                $cycles[] = Cycle::create([
                    'round_id' => $round->id,
                    'index' => $i,
                    'start_at' => $cycleStart,
                    'end_at' => $cycleEnd
                ]);
                $cycleStart->addSeconds($cycleLength);
                $cycleEnd->addSeconds($cycleLength);
                if ($cycleEnd->greaterThan($end)) {
                    $cycleEnd = $end->copy();
                }
            }

            // If a previous cycle object was passed in as argument we move unsold trades from that cycle to
            // first cycle of the current round.
            if ($previousCycle) {
                // We move all bought trades and sell orders to first cycle in new round
                $trades = $previousCycle->trades()
                    ->whereIn('status', [Trade::STATUS_BOUGHT, Trade::STATUS_SELL_ORDER])
                    ->where('quantity', '<>', 0)
                    ->get(['target_coin_id', 'id']);
                $previousCycle->purchases()
                    ->whereIn('coin', $trades->pluck('target_coin_id')->toArray())
                    ->update(['cycle_id' => $cycles[0]->id]);
                $previousCycle->trades()
                    ->whereIn('status', [Trade::STATUS_BOUGHT, Trade::STATUS_SELL_ORDER])
                    ->where('quantity', '<>', 0)
                    ->update(['cycle_id' => $cycles[0]->id]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $round;
    }

    protected function getStrategy($key)
    {
        switch ($key) {
            case 'simple':
                return new SimpleStrategy($this->tradeService);
            case 'advanced':
                return new AdvancedStrategy($this->tradeService);
            default:
                throw new InvalidArgumentException("Unknown strategy key \"$key\" provided");
        }
    }

    /**
     * @param Round $round
     */
    public function processRound(Round $round)
    {
        if ($round->is_canceled) {
            return;
        }
        $strategy = $this->getStrategy($round->strategy);

        Log::info("Processing round", [
            'round_id' => $round->id,
            'exchange_account' => $round->exchange_account_id,
            'strategy' => $round->strategy
        ]);

        $account = $round->exchangeAccount;

        // Each time round is processed we reset the counters so only coins that satisfy
        // criteria get to increase counters for respective criteria.
        $round->fill([
            'minimum_fr_count' => 0,
            'price_volume_count' => 0,
            'ati_count' => 0,
            'limiters_count' => 0,
            'ati_pd_count' => 0
        ]);
        // We look for coins which satisfy criteria for as long as the cycle is in it's
        // first half or as long as there are coins in hold time period that need to satisfy
        // criteria during that hold time.
        $cycle = $round->last_unprocessed_cycle;
        if ($cycle) {
            if (! $this->cycleIsPastPurchaseTime($round, $cycle, $account) || count($round->holders)) {
                $coins = $strategy->filterCoinsMatchingRoundCriteria($round, $cycle, $account);
            } else {
                $coins = [];
            }

            $strategy->processPurchases($round, $cycle, $account, $coins);

            $this->processOrderTimeouts($round, $cycle, $account);

            $strategy->processExit($round, $account);
        }

        if ($round->is_over) {
            $round->is_processed = true;
            /*$this->startRound(
                $account,
                $round->last_cycle
            );*/
        }

        $round->save();
    }

    /**
     * @param Round $round
     * @param Cycle $cycle
     * @param ExchangeAccount $account
     * @return bool
     */
    protected function cycleIsPastPurchaseTime(Round $round, Cycle $cycle, ExchangeAccount $account)
    {
        // We consider purchase no longer available if (75% of cycle - hold time) has passed
        $total = $cycle->end_at->diffInMinutes($cycle->start_at);
        $limit = floor(0.75 * $total);
        if ($round->strategy == 'advanced') {
            $holdFor = $account->auto_entry_hold_time;
            if ($account->auto_entry_hold_time_granularity === 'hours') {
                $holdFor *= 60;
            }
            $limit -= $holdFor;
        }
        $passed = Carbon::now()->diffInMinutes($cycle->start_at);

        Log::info("Checking cycle is past purchase time", [
            'total' => $total,
            'passed' => $passed,
            'limit' => $limit
        ]);

        return $passed > $limit;
    }

    /**
     * @param Round $round
     */
    protected function processOrderTimeouts(Round $round, Cycle $cycle, ExchangeAccount $account)
    {
        // TODO Update logic for all strategies, currently just simple timeout aftert 3 min
        $timeoutLimit = 3;
        $cycle->purchases
            ->each(function ($purchase) use ($timeoutLimit, $round) {
                // We check if BUY ORDER and SELL ORDER trades have timed-out, and if so, we proceed to cancel them.
                $purchase->load('sellTrades');
                $purchase->load('buyTrades');
                $trades = $purchase->sellTrades;
                $buyTrade = $purchase->buyTrades->first();
                if ($buyTrade) {
                    $trades->push($buyTrade);
                }

                Log::info("Processing timeout", [
                    'strategy' => $round->strategy,
                    'timeout' => $timeoutLimit,
                    'purchase_id' => $purchase->id,
                    'buy_trade_count' => $purchase->buyTrades->count(),
                    'sell_trade_count' => $purchase->sellTrades->count()
                ]);

                foreach ($trades as $trade) {
                    Log::info("Processing timeout for purchase trade", [
                        'strategy' => $round->strategy,
                        'trade' => [
                            'id' => $trade->id,
                            'status' => $trade->status,
                            'quantity' => $trade->quantity,
                            'is_open' => $trade->is_open,
                            'diff_minutes' => Carbon::now()->diffInMinutes($trade->created_at)
                        ]
                    ]);
                    if (
                        Carbon::now()->diffInMinutes($trade->created_at) > $timeoutLimit &&
                        ($trade->quantity == $trade->quantity_remaining && $trade->is_open)
                    ) {
                        // If BUY ORDER has timed-out we cancel it. This will also update any quantity updates that might've
                        // happened since last check so any BOUGHT trades will be up to date after this point.
                        Log::info("Canceling trade due to timeout", [
                            'strategy' => $round->strategy,
                            'trade_id' => $trade->id
                        ]);
                        try {
                            $this->tradeService->cancel($trade);
                        } catch (\Exception $e) {
                            Log::error('An error occurred while canceling auto trading trade', [
                                'trade_id' => $trade->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                        if ($trades->count() == 1 && $trade->status == Trade::STATUS_BUY_ORDER) {
                            // Delete the purchase record if only a single buy order was made for that coin and
                            // it was canceled due to time out.
                            $purchase->delete();
                        }
                    }
                }
            });
    }
}
