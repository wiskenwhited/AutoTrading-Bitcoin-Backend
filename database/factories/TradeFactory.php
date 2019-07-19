<?php

$factory->define(\App\Models\Trade::class, function (Faker\Generator $faker) {
    return [
        'order_uuid' => $faker->uuid,
        'target_coin_id' => 'ETH',
        'base_coin_id' => 'BTC',
        'quantity' => 50,
        'status' => \App\Models\Trade::STATUS_BUY_ORDER,
        'user_id' => 1,
        'exchange_id' => 'bittrex',
        'is_open' => true,
        'cycle_id' => null
    ];
});
