<?php

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class ExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!\App\Models\Exchange::find('bittrex')) {
            \App\Models\Exchange::create([
                'id' => 'bittrex',
                'name' => 'Bittrex'
            ]);
        }
        if (!\App\Models\Exchange::find('bitfinex')) {
            \App\Models\Exchange::create([
                'id' => 'bitfinex',
                'name' => 'Bitfinex'
            ]);
        }
        if (!\App\Models\Exchange::find('yobit')) {
            \App\Models\Exchange::create([
                'id' => 'yobit',
                'name' => 'Yobit'
            ]);
        }
    }
}
