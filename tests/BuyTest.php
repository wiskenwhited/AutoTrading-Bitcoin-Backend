<?php

use App\Models\ExchangeAccount;
use App\Models\TradingBotRequest;
use App\Models\User;
use App\TradingBot\TradingBot;
use Illuminate\Support\Facades\Redis;

class BuyTest extends ApiTestCase
{
    use UsesMockTradingBotTrait;

    public function testJobInProgress()
    {
        $user = factory(User::class)->create();
        $exchange = \App\Models\Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => 'bittrex'
        ]);
        factory(\App\Models\Coin::class)->create([
            'id' => 'bitcoin',
            'symbol' => 'BTC'
        ]);
        factory(\App\Models\Coin::class)->create([
            'id' => 'pink',
            'symbol' => 'PINK'
        ]);
        factory(\App\Models\Suggestion::class)->create([
            'exchange' => $exchange->id,
            'coin' => 'PINK',
            'base' => 'BTC'
        ]);

        $tradingBotRequestId = null;
        Redis::shouldReceive('publish')
            ->once()
            ->withArgs(function ($channel, $data) use (&$tradingBotRequestId) {
                $tradingBotRequestId = array_get($data, 'trading_bot_request_id');

                return true;
            });

        $this->mockTradingBot([
            [
                'method' => 'refreshTradingBotRequest',
                'expected_response' => function (TradingBotRequest $tradingBotRequest) {
                    $tradingBotRequest->json_response = [
                        'job_id' => 42,
                        'job_status' => 'completed',
                        'data' => [
                            'order_uuid' => 'uuid'
                        ]
                    ];
                    $tradingBotRequest->save();

                    return $tradingBotRequest;
                }
            ],
            [
                'method' => 'getTradingBotRequestResponse',
                'expected_response' => function ($id) {
                    return [
                        'trading_bot_request_id' => $id,
                        'is_open' => false,
                        'job_id' => 42,
                        'job_status' => 'completed',
                        'data' => [
                            'order_uuid' => 'uuid',
                            'quantity' => 0.025,
                            'quantity_remaining' => 0
                        ]
                    ];
                }
            ]
        ]);

        $this->authenticatedJson('POST', '/api/buy', [
            'exchange_account_id' => $account->id,
            'base_coin_id' => 'BTC',
            'target_coin_id' => 'PINK',
            'quantity' => 0.025,
            'rate' => 0.0025
        ], [], $user);

        $response = json_decode($this->response->getContent(), true);
        $tradeId = array_get($response, 'data.id');
        $this->assertEquals('uuid', array_get($response, 'data.order_uuid'));
        $this->assertEquals('BTC', array_get($response, 'data.base_coin_id'));
        $this->assertEquals('PINK', array_get($response, 'data.target_coin_id'));
        $this->assertEquals(0.025, array_get($response, 'data.quantity'));
        $this->assertEquals(0.0025, array_get($response, 'data.price_bought'));
        $this->seeInDatabase('trades', ['parent_trade_id' => $tradeId, 'status' => 'Bought']);
    }
}