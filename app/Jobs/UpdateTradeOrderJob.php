<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Services\TradeService;
use Illuminate\Support\Facades\Log;

class UpdateTradeOrderJob extends Job
{
    /**
     * @var integer
     */
    private $tradingBotRequestId;

    /**
     * @var Trade
     */
    private $trade;

    /**
     * ProcessTradingBotResponseJob constructor.
     * @param Trade $trade
     * @param $tradingBotRequestId
     */
    public function __construct(Trade $trade, $tradingBotRequestId)
    {
        $this->queue = config('queue.alias.high');
        $this->trade = $trade;
        $this->tradingBotRequestId = $tradingBotRequestId;
    }

    /**
     * @param TradeService $tradeService
     */
    public function handle(TradeService $tradeService)
    {
        /*
        Log::info("Running UpdateTradeOrderJob", [
            'trade_id' => $this->trade->id,
            'trading_bot_request_id' => $this->tradingBotRequestId
        ]);
         */
        $tradeService->updateActiveTrade($this->trade, $this->tradingBotRequestId);
    }

}