<?php

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => Hash::Make('letmein'),
            'country' => 'USA',
            'city' => 'Washington DC',
            'verified' => true
        ]);
    }
}
