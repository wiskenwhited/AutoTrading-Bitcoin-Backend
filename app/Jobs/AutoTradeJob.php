<?php

namespace App\Jobs;

use App\Models\Round;
use App\AutoTrading\AutoTradingService;

class AutoTradeJob extends Job
{
    /**
     * @var Round
     */
    private $round;

    public function __construct(Round $round)
    {
        $this->queue = config('queue.alias.auto_trade');
        $this->round = $round;
    }

    public function handle(AutoTradingService $autoTradingService)
    {
        $autoTradingService->processRound($this->round);
    }
}