<?php

namespace App\Models;


class Watchlist extends Model
{
    protected $table = 'watchlist';

    protected $fillable =
        [
            'user_id',
            'interval',
            'follow',
            'exchange',
            'coin',
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
            'last_check',
            'sms',
            'email',
            'execute',
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
        ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coin_relation()
    {
        return $this->belongsTo(Coin::class, 'coin', 'symbol');
    }

    public function watchlist_history()
    {
        return $this->hasMany(WatchlistHistory::class);
    }

    public function rule()
    {
        return $this->hasOne(WatchlistRule::class, 'watchlist_id');
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class, 'trade_id')->withTrashed();
    }

//    public function coin_relation()
//    {
//        return $this->hasOne(Coin::class, 'symbol', 'coin');
//    }

}
