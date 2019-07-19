<?php

use Illuminate\Support\Facades\Hash;

$factory->define(\App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email,
        'phone' => $faker->phoneNumber,
        'password' => Hash::make('fakepassword'),
        'country' => $faker->country,
        'city' => $faker->city,
        'verified' => true
    ];
});
