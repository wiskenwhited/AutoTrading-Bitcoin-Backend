<?php

namespace App\Jobs;

use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Services\TradeService;

class BuyJob extends Job
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
     * BuyJob constructor.
     * @param Coin $coin
     * @param ExchangeAccount $account
     * @param $quantity
     * @param $rate
     * @param array $data
     */
    public function __construct(Coin $coin, ExchangeAccount $account, $quantity, $rate, array $data = [])
    {
        $this->queue = config('queue.alias.high');
        $this->coin = $coin;
        $this->account = $account;
        $this->quantity = $quantity;
        $this->rate = $rate;
        $this->data = $data;
    }

    /**
     * @param TradeService $tradeService
     */
    public function handle(TradeService $tradeService)
    {
        $trade = $tradeService->buy(
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