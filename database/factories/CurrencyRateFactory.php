<?php

$factory->define(\App\Models\CurrencyRate::class, function (Faker\Generator $faker) {
    return [
        'base' => 'USD',
        'target' => $faker->currencyCode,
        'rate' => $faker->randomFloat(null, 1.5, 5)
    ];
});
