<?php

use App\Models\Country;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class SuggestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 50; $i++) {
            \App\Models\Suggestion::create([
                'exchange' => 'bittrex',
                'coin' => strtoupper(str_random(3)),
                'target' => 10000.0 / rand(50000, 500000),
                'exchange_trend' => 10000.0 / rand(500, 1000),
                'market_cap' => rand(10000, 60000),
                'btc_impact' => 10000.0 / rand(5000, 10000),
                'impact_1hr' => 10000.0 / rand(5000, 10000),
                'gap' => 10000.0 / rand(5000000, 10000000),
                'cpp' => 10000.0 / rand(5000000, 10000000),
                'prr' => 10000.0 / rand(500, 1000) * (rand(1,10) % 2 == 1 ? -1 : 1),
                'target_score' => 0,
                'percentchange_score' => 25,
                'marketcap_score' => 25,
                'pricebtc_score' => 25,
                'overall_score' => 75
            ]);
        }
    }
}
