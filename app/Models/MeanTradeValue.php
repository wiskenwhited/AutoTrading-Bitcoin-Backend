<?php

namespace App\Models;


class MeanTradeValue extends Model
{
    protected $fillable = [
        'exchange',
        'coin',
        'num_buys',
        'level',
        'mean_buy_time',
        'active',
        'num_sells',
        'mean_sell_time',
        'lowest_price',
        'highest_price'
    ];

    public $timestamps = false;
}