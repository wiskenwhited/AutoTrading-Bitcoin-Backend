<?php

namespace App\Models;

class MarketSummary extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'exchange_id',
        'market_name',
        'base_coin_id',
        'target_coin_id',
        'high',
        'low',
        'volume',
        'last',
        'base_volume',
        'time_stamp',
        'bid',
        'ask',
        'open_buy_orders',
        'open_sell_orders',
        'prev_day',
        'created'
    ];

    public $timestamps = false;

    protected $table = 'market_summary';

    public function scopeForBase($scope)
    {
        return $scope->where('base_coin_id', '=', $this->base_coin_id);
    }
    public function scopeForExchange($scope)
    {
        return $scope->where('base_coin_id', '=', $this->base_coin_id);
    }
}