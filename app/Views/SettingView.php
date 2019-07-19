<?php

namespace App\Views;

class SettingView extends AbstractView
{
    protected $fields = [
        'global_round',
        'global_cycle_granularity',
        'global_cycle',
        'entry_minimum_fr',
        'entry_price_movement',
        'entry_price_from',
        'entry_volume_movement',
        'entry_volume_from',
        'entry_ati',
        'entry_ati_movement',
        'entry_ati_from',
        'entry_liquidity_variance',
        'entry_minimum_prr',
        'entry_hold_time_granularity',
        'entry_hold_time',
        'entry_price',
        'entry_historic_target_from',
        'entry_historic_target',
        'entry_position_btc',
        'entry_open_time',
        'entry_notified_by_email',
        'entry_notified_by_sms',
        'entry_is_auto_trading',
        'exit_target',
        'exit_shrink_differential',
        'exit_option',
        'exit_notified_by_email',
        'exit_notified_by_sms',
        'exit_is_auto_trading',
        'withdrawal_capital_balance',
        'withdrawal_capital_balance_currency',
        'withdrawal_value',
        'withdrawal_value_coin',
        'withdrawal_address',
        'withdrawal_notified_by_email',
        'withdrawal_notified_by_sms',
        'withdrawal_is_auto_trading',
        'smart_sell_enabled',
        'smart_sell_interval',
        'smart_sell_drops',
        'enabled_2fa'
    ];

    public function getEntryAtiAttribute($model)
    {
        return is_null($model['entry_ati']) ? null : (double)$model['entry_ati'];
    }

    public function getEntryLiquidityVarianceAttribute($model)
    {
        return is_null($model['entry_liquidity_variance']) ? null : (double)$model['entry_liquidity_variance'];
    }

    public function getEntryMinimumPrrAttribute($model)
    {
        return is_null($model['entry_minimum_prr']) ? null : (double)$model['entry_minimum_prr'];
    }

    public function getEntryHistoricTargetAttribute($model)
    {
        return is_null($model['entry_historic_target']) ? null : (double)$model['entry_historic_target'];
    }

    public function getEntryPositionBtcAttribute($model)
    {
        return is_null($model['entry_position_btc']) ? null : (double)$model['entry_position_btc'];
    }
}