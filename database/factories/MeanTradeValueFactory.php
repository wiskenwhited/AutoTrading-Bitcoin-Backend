<?php

$factory->define(\App\Models\MeanTradeValue::class, function (Faker\Generator $faker) {
    return [
        'exchange' => 'bittrex',
        'coin' => 'ETH',
        'mean_buy_time' => $faker->randomFloat(2, 1, 20),
        'level' => 'old',
        'num_buys' => $faker->numberBetween(0, 100),
        'num_sells' => $faker->numberBetween(0, 100),
        'mean_sell_time' => $faker->randomFloat(2, 1, 20),
        'lowest_price' => $faker->randomFloat(10, 0, 0.02),
        'highest_price' => $faker->randomFloat(10, 0, 0.02)
    ];
});
