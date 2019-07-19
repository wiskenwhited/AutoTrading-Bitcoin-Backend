<?php

namespace App\TradingBot\Models;

use App\Models\Model;

class FakeOrder extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'order_uuid';

    protected $keyType = 'string';

    protected $fillable = [
        'order_uuid',
        'exchange',
        'order_type',
        'quantity',
        'quantity_remaining',
        'limit',
        'reserved',
        'reserved_remaining',
        'commission_reserved',
        'commission_reserved_remianing',
        'commission_paid',
        'price',
        'price_per_unit',
        'opened',
        'closed',
        'is_open',
        'sentinel',
        'cancel_initiated',
        'immediate_or_cancel',
        'is_conditional',
        'condition',
        'condition_target',

        'leave_open'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'leave_open' => 'boolean'
    ];
}