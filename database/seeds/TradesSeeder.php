<?php

use Illuminate\Database\Seeder;

class TradesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::get();
        foreach ($users as $user) {
            for ($i = 0; $i < 50; $i++) {
                \App\Models\Trade::create([
                    'user_id' => $user->id,
                    'order_uuid' => strtoupper(str_random(20)),
                    'suggestion_id' => rand(0,100),
                    'exchange' => 'BTC-NXT',
                    'order_type' => 'LIMIT_BUY',
                    'quantity' => 10000.0 / rand(5000, 10000),
                    'quantity_remaining' => 10000.0 / rand(5000, 10000),
                    'limit' => 10000.0 / rand(5000, 10000),
                    'reserved' => 10000.0 / rand(5000, 10000),
                    'reserved_remaining' => 10000.0 / rand(5000, 10000),
                    'commission_reserved' => 10000.0 / rand(5000, 10000),
                    'commission_reserved_remaining' => 10000.0 / rand(5000, 10000),
                    'commission_paid' => 10000.0 / rand(5000, 10000),
                    'price' => 10000.0 / rand(5000, 10000),
                    'price_per_unit' => 10000.0 / rand(5000, 10000),
                    'opened' => \Carbon\Carbon::now(),
                    'closed' => \Carbon\Carbon::now(),
                    'is_open' => rand(0, 1),
                    'sentinel' => strtoupper(str_random(20)),
                    'cancel_initiated' => rand(0, 1),
                    'immediate_or_cancel' => rand(0, 1),
                    'is_conditional' => rand(0, 1),
                    'condition' => 'NONE',
                    'condition_target' => '',
                    'exchange_id' => 'bittrex',
                    'currency_id' => 'BTC',
                    'price_bought' => 10000.0 / rand(5000, 10000),
                    'cpp' => 10000.0 / rand(5000, 10000),
                    'gap' => rand(1, 10000555),
                    'profit' => 10000.0 / rand(5000, 10000),
                    'suggestion' => rand(0,1),
                    'status' => rand(0,1),
                    'exit_strategy' => rand(0,1),
                    'shrink_differential' =>  100 / rand(5000, 10000),
                    'target_price' =>  10000.0 / rand(5000, 10000),
                ]);
            }
        }
    }
}
