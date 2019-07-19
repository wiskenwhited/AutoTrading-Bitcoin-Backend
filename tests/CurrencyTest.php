<?php

use App\Models\Coin;
use App\Models\CurrencyRate;

class CurrencyTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testIndexRoute()
    {
        factory(CurrencyRate::class, 3)->create();

        $this->authenticatedJson('GET', '/api/currencies');

        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertEquals(200, $this->response->status());
        // Taking into account USD which is added manually
        $this->assertCount(4, $data, "Expected 4 currencies in response");
    }
}