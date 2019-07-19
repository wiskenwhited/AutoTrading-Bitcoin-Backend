<?php

namespace App\Jobs;

use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Services\TradeService;

class SellJob extends Job
{
    /**
     * @var Coin
     */
    private $coin;

    /**
     * @var ExchangeAccount
     */
    private $account;

    /**
     * @var
     */
    private $quantity;

    /**
     * @var
     */
    private $rate;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Trade
     */
    private $trade;

    /**
     * BuyJob constructor.
     * @param Trade $trade
     * @param Coin $coin
     * @param ExchangeAccount $account
     * @param $quantity
     * @param $rate
     * @param array $data
     */
    public function __construct(Trade $trade, Coin $coin, ExchangeAccount $account, $quantity, $rate, array $data = [])
    {
        $this->queue = config('queue.alias.high');
        $this->coin = $coin;
        $this->account = $account;
        $this->quantity = $quantity;
        $this->rate = $rate;
        $this->data = $data;
        $this->trade = $trade;
    }

    /**
     * @param TradeService $tradeService
     */
    public function handle(TradeService $tradeService)
    {
        $trade = $tradeService->sell(
            $this->trade,
            Coin::findBySymbol('BTC'),
            $this->coin,
            $this->account,
            $this->quantity,
            $this->rate,
            $this->data
        );
        $trade->update($this->data);
    }
}