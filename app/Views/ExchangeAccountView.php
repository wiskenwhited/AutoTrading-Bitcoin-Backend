<?php

namespace App\Views;

class ExchangeAccountView extends AbstractView
{
    protected $fields = [
        'id',
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

    public function getAutoEntryMaximumAtiAttribute($model)
    {
        return is_null($model['auto_entry_maximum_ati']) ? null : (double)$model['auto_entry_maximum_ati'];
    }

    public function getAutoEntryMinimumLiquidityVarianceAttribute($model)
    {
        return is_null($model['auto_entry_minimum_liquidity_variance']) ? null : (double)$model['auto_entry_minimum_liquidity_variance'];
    }

    public function getAutoEntryMinimumPrrAttribute($model)
    {
        return is_null($model['auto_entry_minimum_prr']) ? null : (double)$model['auto_entry_minimum_prr'];
    }

    public function getAutoEntryPositionBtcAttribute($model)
    {
        return is_null($model['auto_entry_position_btc']) ? null : (double)$model['auto_entry_position_btc'];
    }
}