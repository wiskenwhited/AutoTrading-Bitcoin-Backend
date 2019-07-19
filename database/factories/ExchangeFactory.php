<?php

$factory->define(\App\Models\Exchange::class, function (Faker\Generator $faker) {
    return [
        'id' => 'bittrex',
        'name' => 'Bittrex'
    ];
});
