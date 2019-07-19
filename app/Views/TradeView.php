<?php

namespace App\Views;

use App\Models\FormatsCurrencyTrait;
use App\Models\Trade;
use Illuminate\Support\Collection;

class TradeView extends AbstractView
{
    use FormatsCurrencyTrait;

    protected $fields = [
        'id',
        'order_uuid',
        'placed_at',
        'base_coin_id',
        'target_coin_id',
        'exchange_id',
        'exchange_account_id',
        'exchange_account_name',
        'quantity',
        'price_bought',
        'cpp',
        'highest_bid',
        'gap',
        'profit',
        'profit_percent',
        'profit_local_currency',
        'status',
        'status_name',
        'shrink_differential',
        'target_percent',
        'target_price',
        'target_shrink_differential',
        'suggestion',
        'is_test',
        'local_currency',
        'price_per_unit',

        'price_per_unit_local_currency',
        'btc_price_local_currency',
        'current_btc_price_local_currency',
        'btc_growth',
        'coin_growth',
        'btc_profit_local_currency',
        'coin_profit_btc',
        'cumulative_profit_local_currency',

        'cycle'
    ];

    protected $map = [
        'created_at' => 'placed_at'
    ];

    protected $calculated = [
        'status_name',
        'exchange_name',
        'profit_local_currency',
        'profit_percent',
        'btc_price_local_currency',
        'current_btc_price_local_currency',
    ];

    /**
     * @param $model
     * @return mixed|null
     */
    public function getStatusNameAttribute($model)
    {
        switch ($model['status']) {
            case Trade::STATUS_BOUGHT:
                return "BOUGHT";
            case Trade::STATUS_BUY_ORDER:
                return "BUY ORDER";
            case Trade::STATUS_SOLD:
                return "SOLD";
            case Trade::STATUS_SELL_ORDER:
                return "SELL ORDER";
        }
    }

    public function getExchangeAccountNameAttribute($model)
    {
        if (array_get($model, 'cycle_id')) {
            return '[ROBOT] ' . $model['exchange_account_name'];
        }

        return $model['exchange_account_name'];
    }

    public function getBtcPriceLocalCurrencyAttribute($model)
    {
        $number = array_get($model, 'btc_price_local_currency');

        return $number ? $this->formatNumber($number) : null;
    }

    public function getCurrentBtcPriceLocalCurrencyAttribute($model)
    {
        $number = array_get($model, 'current_btc_price_local_currency');

        return $number ? $this->formatNumber($number) : null;
    }

    public function getBtcGrowthAttribute($model)
    {
        $number = array_get($model, 'btc_growth') * 100;

        return $number ? $this->formatNumber($number) : null;
    }

    public function getCoinGrowthAttribute($model)
    {
        $number = array_get($model, 'coin_growth') * 100;

        return $number ? $this->formatNumber($number) : null;
    }

    /**
     * @param $model
     * @return mixed|null
     */
    public function getProfitLocalCurrencyAttribute($model)
    {
        $coinPriceBtc = (double)array_get($model, 'coin.price_btc');
        $coinPriceUsd = (double)array_get($model, 'coin.price_usd');
        $rate = (double)array_get($model, 'user.currencyRate.rate');
        if (! $coinPriceBtc) {
            return null;
        }

        return array_get($model, 'profit', 0) * ($coinPriceUsd / $coinPriceBtc) * $rate;
    }

    public function getProfitPercentAttribute($model)
    {
        $priceBought = (double)array_get($model, 'price_per_unit', 0);
        $quantity = (double)array_get($model, 'quantity', 0);
        $totalBought = $priceBought * $quantity;
        if ($totalBought) {
            return array_get($model, 'profit', 0) / $totalBought * 100;
        }

        return 0;
    }

    public function getCycleAttribute($model)
    {
        return array_get($model, 'cycle.index');
    }
}