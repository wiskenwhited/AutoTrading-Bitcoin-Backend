<?php

use App\Models\Coin;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Models\User;
use App\TradingBot\Models\FakeOrder;
use App\TradingBot\TradingBot;

class TestModeTest extends ApiTestCase
{
    public function testTestMode()
    {
        $user = factory(User::class)->create();
        Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => 'bittrex'
        ]);
        factory(Coin::class)->create([
            'id' => 'bitcoin',
            'symbol' => 'BTC'
        ]);
        factory(Coin::class)->create([
            'id' => 'pink',
            'symbol' => 'PINK'
        ]);

        $this->app->bind(TradingBot::class, function ($app) {
            $mock = \Mockery::mock(App\TradingBot\TradingBot::class);
            $mock->shouldReceive('getExchangeSuggestions')
                ->andReturn([
                    'data' => [
                        [
                            'coin' => 'PINK',
                            'cpp' => 0.002
                        ]
                    ]
                ]);

            return $mock;
        });

        $this->authenticatedJson('POST', '/api/buy?mode=test', [
            'exchange_account_id' => $account->id,
            'base_coin_id' => 'BTC',
            'target_coin_id' => 'PINK',
            'quantity' => 0.025,
            'rate' => 0.0025
        ], [], $user);

        $response = json_decode($this->response->getContent(), true);
        dd($response);
    }
}