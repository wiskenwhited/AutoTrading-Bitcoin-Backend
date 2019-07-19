<?php

namespace App\Jobs;

use App\Models\Suggestion;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTestTradeJob extends Job
{
    /**
     * @var Trade
     */
    private $trade;

    /**
     * ProcessTradingBotResponseJob constructor.
     * @param Trade $trade
     */
    public function __construct(Trade $trade)
    {
        $this->queue = config('queue.alias.high');
        $this->trade = $trade;
    }

    public function handle()
    {
        $suggestion = Suggestion::where('exchange', $this->trade->exchange_id)
            ->where('coin', $this->trade->target_coin_id)
            ->first();

        $askPrice = object_get($suggestion, 'cpp', null);
        /*
        Log::info("Running UpdateTestTradeJob", [
            'trade_id' => $this->trade->id,
            'exchange' => $this->trade->exchange_id,
            'coin' => $this->trade->target_coin_id,
            'price_per_unit' => $this->trade->price_per_unit,
            'ask_price' => $askPrice
        ]);
        */
        if (
            ! is_null($askPrice) && (
                $this->trade->status == Trade::STATUS_BUY_ORDER &&
                (float)$this->trade->price_per_unit >= (float)$askPrice ||
                $this->trade->status == Trade::STATUS_SELL_ORDER &&
                (float)$this->trade->price_per_unit <= (float)$askPrice
            )
        ) {
            $this->trade->status = $this->trade->status == Trade::STATUS_BUY_ORDER ?
                Trade::STATUS_BOUGHT : Trade::STATUS_SOLD;
            $this->trade->save();
        } else {
            $job = new self($this->trade);
            $job->delay(10);
            dispatch($job);
        }
    }
}