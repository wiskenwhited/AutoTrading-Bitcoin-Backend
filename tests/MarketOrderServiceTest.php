<?php
use App\Services\MarketOrderService;
use GuzzleHttp\Client;

class MarketOrderServiceTest extends ApiTestCase
{
    public function testRetrieveMarketOrder()
    {
        $mock = \Mockery::mock(MarketOrderService::class . '[getBittrexResponse]', [
            new Client(), new Client(), $this->app['log']
        ])->shouldAllowMockingProtectedMethods();
        $expectation = $mock->shouldReceive('getBittrexResponse')
            ->andReturn(['result' => [
                ['Rate' => 0.5, 'Quantity' => 0.2],
                ['Rate' => 0.5, 'Quantity' => 0.1],
                ['Rate' => 0.2, 'Quantity' => 0.2],
                ['Rate' => 0.2, 'Quantity' => 0.3],
                ['Rate' => 0.3, 'Quantity' => 0.2],
                ['Rate' => 0.5, 'Quantity' => 0.2]
            ]]);
        $buyResult = $mock->retrieveMarketOrder('bittrex', 'COIN', false);
        $sellResult = $mock->retrieveMarketOrder('bittrex', 'COIN', true);
        $this->assertEquals(0.2, array_get($buyResult, '0.price'), 'Invalid calculation for lowest ask price');
        $this->assertEquals(0.5, array_get($buyResult, '0.amount'), 'Invalid calculation for lowest ask quantity');
        $this->assertEquals(0.5, array_get($sellResult, '0.price'), 'Invalid calculation for highest bid price');
        $this->assertEquals(0.5, array_get($sellResult, '0.amount'), 'Invalid calculation for highest bid quantity');
    }
}