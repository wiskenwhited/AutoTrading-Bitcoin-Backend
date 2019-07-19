<?php

$factory->define(\App\Models\ExchangeAccount::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'exchange_id' => 'bittrex',
        'user_id' => $faker->randomNumber(1),
        'key' => str_random(10),
        'secret' => str_random(32),
        'auto_global_round_duration' => 1,
        'auto_global_round_granularity' => 'days',
        'auto_global_cycles' => 4,
        'auto_global_age' => 3,
        'auto_global_strategy' => 'simple',
        'auto_entry_minimum_fr' => 0.1234,
        'auto_entry_price_movement' => 'progressive',
        'auto_entry_price_sign' => 'positive',
        'auto_entry_volume_movement' => 'progressive',
        'auto_entry_volume_sign' => 'positive',
        'auto_entry_maximum_ati' => 3,
        'auto_entry_ati_movement' => 'progressive',
        'auto_entry_minimum_liquidity_variance' => 0.1234,
        'auto_entry_minimum_prr' => 0.1234,
        'auto_entry_hold_time_granularity' => 'minutes',
        'auto_entry_hold_time' => 10,
        'auto_entry_price' => 0.1234,
        'auto_entry_position_btc' => 1,
        'auto_entry_open_time' => 1,
        'auto_exit_action' => 'move',
        'auto_exit_intervals' => 3,
        'auto_exit_drops' => 2
    ];
});
