<?php

use App\Models\Coin;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Models\User;
use App\TradingBot\Models\FakeOrder;

class FakeBotTest extends ApiTestCase
{
    public function testFakeCycle()
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

        $this->authenticatedJson('POST', '/api/buy?mode=test', [
            'exchange_account_id' => $account->id,
            'base_coin_id' => 'BTC',
            'target_coin_id' => 'PINK',
            'quantity' => 0.025,
            'rate' => 0.0025
        ], [], $user);

        $response = json_decode($this->response->getContent(), true);

        // At this point, due to sync queue driver, the trade should be complete
        $uuid = array_get($response, 'data.order_uuid');
        $order = FakeOrder::findOrFail($uuid);
        $this->assertEquals(true, array_get($response, 'data.is_test'));
        $this->assertEquals(false, $order->is_open);
        $this->assertEquals(0, $order->quantity_remaining);
        $this->assertEquals(2, Trade::count());
    }
}