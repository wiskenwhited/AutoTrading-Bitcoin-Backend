<?php

use App\Models\Coin;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Models\TradingBotRequest;
use App\Models\User;
use App\TradingBot\TradingBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CancelTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testJobInProgress()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
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
        factory(\App\Models\MarketSummary::class)->create([
            'exchange_id' => $exchange->id,
            'target_coin_id' => 'PINK',
            'base_coin_id' => 'BTC'
        ]);
        $originTrade = factory(Trade::class)->create([
            'user_id' => $user->id,
            'exchange_account_id' => $account->id,
            'exchange_id' => $exchange->id,
            'target_coin_id' => 'PINK',
            'base_coin_id' => 'BTC',
            // Snapshot of DB should contain a BOUGHT order which has already been sold from
            'quantity' => 0.05 - 0.025,
            'price_per_unit' => 0.0025,
            'status' => Trade::STATUS_BOUGHT
        ]);
        $sellTrade = factory(Trade::class)->create([
            'user_id' => $user->id,
            'exchange_account_id' => $account->id,
            'exchange_id' => $exchange->id,
            'target_coin_id' => 'PINK',
            'base_coin_id' => 'BTC',
            // Amount user selected to sell from BOUGHT order
            'quantity' => 0.025,
            'price_per_unit' => 0.0025,
            'original_trade_id' => $originTrade->id,
            'status' => Trade::STATUS_SELL_ORDER
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
                            'order_uuid' => 'uuid',
                            'cancel_initiated' => true
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
                            'cancel_initiated' => true
                        ]
                    ];
                }
            ]
        ]);

        $this->authenticatedJson('POST', '/api/cancel', [
            'trade_id' => $sellTrade->id,
            'exchange_account_id' => $account->id
        ], [], $user);

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals(200, $this->response->status());
        $this->seeInDatabase('trades', ['id' => $originTrade->id, 'status' => Trade::STATUS_BOUGHT, 'quantity' => 0.05]);
    }

    protected function mockTradingBot(array $expectedResponses)
    {
        $methods = array_unique(array_map(function($data) {
            return $data['method'];
        }, $expectedResponses));
        $methods = implode(',', $methods);
        $mock = \Mockery::mock(TradingBot::class . "[$methods]", [new \App\TradingBot\FakeBot()])
            ->shouldAllowMockingProtectedMethods();
        foreach ($expectedResponses as $data) {
            $method = $data['method'];
            $expectedResponse = $data['expected_response'];
            $expectation = $mock->shouldReceive($method);
            $expectation->withArgs(function (TradingBotRequest $tradingBotRequest) {
                return true;
            })
                ->andReturnUsing($expectedResponse);
        }
        $this->app->bind(TradingBot::class, function ($app) use ($mock) {
            return $mock;
        });
    }
}