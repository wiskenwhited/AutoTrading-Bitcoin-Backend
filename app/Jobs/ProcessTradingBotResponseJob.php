<?php

namespace App\Jobs;

use App\Models\TradingBotJob;
use App\TradingBot\JobProcessor;

class ProcessTradingBotResponseJob extends Job
{
    /**
     * @var
     */
    private $tradingBotJob;

    /**
     * ProcessTradingBotResponseJob constructor.
     * @param TradingBotJob $tradingBotJob
     */
    public function __construct(TradingBotJob $tradingBotJob)
    {
        $this->tradingBotJob = $tradingBotJob;
    }

    public function handle(JobProcessor $jobProcessor)
    {
        $jobProcessor->processJob($this->tradingBotJob);
    }
}