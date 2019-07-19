<?php

use App\Models\Coin;
use App\Models\CurrencyRate;

class CoinTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testConvertRoute()
    {
        $this->authenticatedJson('GET', '/api/coins/convert/BTC/USD');
        dd(json_decode($this->response->getContent(), true));
    }

    public function testIndexRoute()
    {
        factory(Coin::class, 3)->create();

        $this->authenticatedJson('GET', '/api/coins');
        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertEquals(200, $this->response->status());
        $this->assertCount(3, $data, "Expected 3 coins in response");
        $this->assertArrayHasKey('formatted_price_local_currency', $data[0]);
        $this->assertArrayHasKey('formatted_price_usd', $data[0]);
        $this->assertEquals($data[0]['formatted_price_local_currency'], $data[0]['formatted_price_usd']);
    }

    public function testShowRoute()
    {
        $coin = factory(Coin::class)->create();

        $this->authenticatedJson('GET', "/api/coins/$coin->id");

        $this->assertEquals(200, $this->response->status());
    }

    public function testShowRouteWithLocalCurrency()
    {
        $coin = factory(Coin::class)->create();
        $rate = factory(CurrencyRate::class)->create();
        $user = factory(\App\Models\User::class)->create([
            'currency' => $rate->target
        ]);

        $this->authenticatedJson('GET', "/api/coins/$coin->id", [], [], $user);

        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertEquals($rate->target, $data['local_currency_code']);
        $this->assertEquals((float)$rate->rate * (float)$coin->price_usd, $data['price_local_currency']);
        $this->assertArrayHasKey('formatted_price_local_currency', $data);
        $this->assertArrayHasKey('formatted_price_usd', $data);
        $this->assertArrayHasKey('price_local_currency', $data);
        $this->assertArrayHasKey('local_currency_code', $data);

        $this->assertEquals(200, $this->response->status());
    }

    public function testNotFoundShowRoute()
    {
        $coin = factory(Coin::class)->create();

        $this->authenticatedJson('GET', "/api/coins/_$coin->id");

        $this->assertEquals(404, $this->response->status());
    }
}