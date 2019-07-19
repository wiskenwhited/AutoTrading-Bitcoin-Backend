<?php

namespace App\Models;


class WatchlistHistory extends Model
{
    protected $table = 'watchlist_history';

    protected $fillable =
        [
            'watchlist_id',
            'target',
            'exchange_trend',
            'market_cap',
            'btc_impact',
            'impact_1hr',
            'gap',
            'cpp',
            'prr',
            'base',
            'lowest_ask',
            'highest_bid',
            'btc_liquidity_bought',
            'btc_liquidity_sold',
            'target_score',
            'exchange_trend_score',
            'impact_1hr_change_score',
            'btc_impact_score',
            'btc_liquidity_score',
            'market_cap_score',
            'overall_score',
            'time_of_data',
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
            'market_cap_score_diff',
            'overall_score_diff',
            'market_cap_diff',
            'price_per_unit',
            'market_cap_ups',
            'market_cap_downs',
            'gap_ups',
            'gap_downs',
            'cpp_ups',
            'cpp_downs',
            'prr_ups',
            'prr_downs',
            'liquidity_ups',
            'liquidity_downs',
            ];

    public function watchlist()
    {
        return $this->belongsTo(Watchlist::class, 'watchlist_id');
    }
}
