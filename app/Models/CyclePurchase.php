<?php

namespace App\Models;

class CyclePurchase extends Model
{
    protected $fillable = [
        'cycle_id',
        'coin',
        'ati',
        'last_purchased_at'
    ];

    protected $dates = [
        'created_at',
        'modified_at',
        'last_purchased_at'
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function buyTrades()
    {
        return $this->hasMany(Trade::class, 'cycle_id', 'cycle_id')
            ->where('status', Trade::STATUS_BUY_ORDER)
            ->where('target_coin_id', $this->coin)
            ->where('is_open', true);
    }

    public function boughtTrades()
    {
        return $this->hasMany(Trade::class, 'cycle_id', 'cycle_id')
            ->where('target_coin_id', $this->coin)
            ->where('status', Trade::STATUS_BOUGHT)
            ->where('is_open', false);
    }

    public function soldTrades()
    {
        return $this->hasMany(Trade::class, 'cycle_id', 'cycle_id')
            ->where('target_coin_id', $this->coin)
            ->where('status', Trade::STATUS_SOLD)
            ->where('is_open', false);
    }

    public function sellTrades()
    {
        return $this->hasMany(Trade::class, 'cycle_id', 'cycle_id')
            ->where('target_coin_id', $this->coin)
            ->where('status', Trade::STATUS_SELL_ORDER)
            ->where('is_open', true);
    }
}