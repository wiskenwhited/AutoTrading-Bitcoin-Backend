<?php

namespace App\Views;

use App\Models\Trade;
use Illuminate\Support\Collection;

class WatchlistView extends AbstractView
{
    private $profitTargetBtc;
    private $priceBoughtBtc;

    protected $fields = [
        'id',
        'type',
        'exchange',
        'coin',
        'coin_name',
        'market_cap',
        'market_cap_score',
        'btc_liquidity_bought',
        'btc_liquidity_sold',
        'btc_liquidity_score',
        'gap',
        'cpp',
        'prr',
        'gap_ups',
        'gap_downs',
        'cpp_ups',
        'cpp_downs',
        'prr_ups',
        'prr_downs',
        'liquidity_ups',
        'liquidity_downs',
        'market_cap_ups',
        'market_cap_downs',
        'email',
        'execute',
        'sms',
        'interval',
        'has_history',
        'rule',
        'price_per_unit',
        'price_per_unit_usd',
        'price_per_unit_direction',
        'price_bought_btc',
        'price_bought_usd',
        'profit_target_btc',
        'profit_target',
        'current_profit_target',
    ];

    /**
     * @param $model
     * @return mixed|null
     */
    public function getHasHistoryAttribute($model)
    {
        return isset($model['watchlist_history']) && count($model['watchlist_history']) > 0;
    }


    public function getProfitTargetAttribute($model)
    {

        if ($model['type'] == 'buy')
            return null;

        if (isset($model['rule'])) {
            return $model['rule']['sell_target'];
        }
    }

    public function getCurrentProfitTargetAttribute($model)
    {
        if ($model['type'] == 'buy')
            return null;


        if (isset($model['rule']) && isset($model['trade'])) {
            return (($model['price_per_unit'] / $model['trade']['price_per_unit']) - 1) * 100;
        }
    }

//    public function getProfitTargetUsdAttribute($model)
//    {
//        if ($this->profitTargetBtc && isset($model['coin_relation']) && $model['coin_relation']) {
//            return $this->profitTargetBtc * $model['coin_relation']['price_usd'];
//        }
//    }

    public function getProfitTargetBtcAttribute($model)
    {
        if ($model['type'] == 'buy')
            return null;

        if (isset($model['trade']) && isset($model['rule'])) {
            $this->profitTargetBtc = ($model['trade']['price_per_unit'] * (1 + ($model['rule']['sell_target'] / 100)));
            return $this->profitTargetBtc;
        }
    }

    public function getPriceBoughtBtcAttribute($model)
    {
        if ($model['type'] == 'buy')
            return null;

        if (isset($model['trade'])) {
            $this->priceBoughtBtc = $model['trade']['price_per_unit'];
            return $this->priceBoughtBtc;
        }
    }

    public function getPriceBoughtUsdAttribute($model)
    {
        //TODO: check if we need to use btc_price_usd from trades
        if ($this->priceBoughtBtc && isset($model['coin_relation']) && $model['coin_relation']) {
            return $this->priceBoughtBtc * $model['coin_relation']['price_usd'];
        }
    }

    public function getPricePerUnitUsdAttribute($model)
    {
        if ($model['type'] == 'buy')
            return null;
        return $model['price_per_unit'] * $model['coin_relation']['price_usd'];
    }

}