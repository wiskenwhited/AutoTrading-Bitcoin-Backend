<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

class ExchangeAccount extends Model
{
    protected $fillable = [
        'user_id',
        'exchange_id',
        'name',
        'key',
        'secret',
        // Auto trading settings per exchange account
        'auto_global_is_auto_trading',
        'auto_global_round_duration',
        'auto_global_round_granularity',
        'auto_global_cycles',
        'auto_global_age',
        'auto_global_strategy',
        'auto_entry_minimum_fr',
        'auto_entry_price_movement',
        'auto_entry_price_sign',
        'auto_entry_volume_movement',
        'auto_entry_volume_sign',
        'auto_entry_maximum_ati',
        'auto_entry_ati_movement',
        'auto_entry_minimum_liquidity_variance',
        'auto_entry_minimum_prr',
        'auto_entry_hold_time_granularity',
        'auto_entry_hold_time',
        'auto_entry_price',
        'auto_entry_position_btc',
        'auto_entry_open_time',
        'auto_exit_action',
        'auto_exit_intervals',
        'auto_exit_drops',
        'auto_exit_no_loss',
        'withdrawal_capital_balance',
        'withdrawal_capital_balance_currency',
        'withdrawal_value',
        'withdrawal_value_coin',
        'withdrawal_address',
        'withdrawal_notified_by_email',
        'withdrawal_notified_by_sms',
        'withdrawal_is_auto_trading'
    ];

    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, $user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        $query->where('user_id', $user);
    }

    public function scopeByExchange($query, $exchange)
    {
        if ($exchange instanceof Exchange) {
            $exchange = $exchange->id;
        }
        $query->where('exchange_id', $exchange);
    }

    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = encrypt($value);
    }

    public function getSecretAttribute()
    {
        return decrypt($this->attributes['secret']);
    }
}