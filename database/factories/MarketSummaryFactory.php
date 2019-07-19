<?php

$factory->define(\App\Models\MarketSummary::class, function (Faker\Generator $faker) {
    $name = $faker->unique()->word;

    return [
        'market_name' => $name,
        'high' => 0,
        'low' => 0,
        'volume' => 0,
        'last' => 0,
        'base_volume' => 0,
        'time_stamp' => $faker->dateTime,
        'created' => $faker->dateTime,
        'exchange_id' => 'bittrex',
        'base_coin_id' => 'BTC',
        'target_coin_id' => 'PINK',
        'bid' => 0.00002220,
        'ask' => 0.00002218,
        'open_buy_orders' => 0,
        'open_sell_orders' => 0,
        'prev_day' => 0.00002218
    ];
});
