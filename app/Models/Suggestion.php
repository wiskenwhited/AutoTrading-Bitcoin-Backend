<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class Suggestion extends Model
{
    protected $fillable =
        [
            'exchange',
            'coin',
            'target',
            'exchange_trend',
            'btc_impact',
            'impact_1hr',
            'gap',
            'cpp',
            'prr',
            'btc_liquidity_bought',
            'btc_liquidity_sold',
            'target_score',
            'exchange_trend_score',
            'impact_1hr_change_score',
            'btc_impact_score',
            'btc_liquidity_score',
            //'market_cap_score',
            'overall_score',
            'gap_arrow',
            'prr_arrow',
            'cpp_arrow',
            'overall_score_arrow',
            'btc_impact_arrow',
            'market_cap_arrow',
            'impact_1hr_change_arrow',
            'exchange_trend_arrow',
            'created_at',
            'updated_at',
            'target_diff',
            'exchange_trend_diff',
            'btc_impact_diff',
            'impact_1hr_diff',
            'gap_diff',
            'cpp_diff',
            'prr_diff',
            'target_score_diff',
            'exchange_trend_score_diff',
            'btc_impact_score_diff',
            'btc_liquidity_score_diff',
            'btc_liquidity_bought_diff',
            'btc_liquidity_sold_diff',
            'impact_1hr_change_score_diff',
            //'market_cap_score_diff',
            'overall_score_diff',
            'base',
            'lowest_ask',
            'highest_bid',
            'market_cap',
            'market_cap_diff',
            'num_buys',
            'mean_buy_time',
            'num_sells',
            'mean_sell_time',
            'ati_percentage_difference',
            'percentchange_score',
            'marketcap_score',
            'pricebtc_score',
        ];

    protected $diffs = [
        'target',
        'exchange_trend',
        'market_cap',
        'btc_impact',
        'impact_1hr',
        'gap',
        'cpp',
        'prr',
        'target_score',
        'market_cap_score',
        'overall_score',
        'btc_liquidity_bought',
        'btc_liquidity_sold',
        'exchange_trend_score',
        'btc_impact_score',
        'btc_liquidity_score',
        'impact_1hr_change_score',
    ];

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->diffs) && array_has($this->attributes, $key)) {
            $diff = $value - $this->attributes[$key];
            if ($diff != 0) {
                $this->attributes[$key . '_diff'] = $diff;
            }
        }

        return parent::setAttribute($key, $value);
    }
}