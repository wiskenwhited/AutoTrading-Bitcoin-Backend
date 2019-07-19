<?php

$factory->define(\App\Models\Coin::class, function (Faker\Generator $faker) {
    $name = $faker->unique()->word;

    return [
        'id' => $name,
        'name' => ucfirst($name),
        'symbol' => str_random(3),
        'rank' => rand(1, 100),
        'price_usd' => $faker->randomFloat(2, 100, 10000),
        'price_btc' => $faker->randomFloat(2, 0, 10),
        'volume_usd_24h' => $faker->randomFloat(0, 100000, 10000000),
        'market_cap_usd' => $faker->randomFloat(0, 100000, 10000000),
        'available_supply' => $faker->randomFloat(0, 100000, 10000000),
        'total_supply' => $faker->randomFloat(0, 100000, 10000000),
        'percent_change_1h' => $faker->randomFloat(2, 0, 10),
        'percent_change_24h' => $faker->randomFloat(2, 0, 10),
        'percent_change_7d' => $faker->randomFloat(2, 0, 10),
        'last_updated' => $faker->dateTime
    ];
});
