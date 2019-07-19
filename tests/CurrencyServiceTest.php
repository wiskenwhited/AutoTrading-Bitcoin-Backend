<?php

use App\Models\Coin;
use App\Models\CurrencyRate;

class CurrencyServiceTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testLocalCoinUpdate()
    {
        Coin::create([
            'id' => 'bitcoin_test',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'rank' => '1',
            'price_usd' => '1.0',
            'price_btc' => '1.0',
            '24h_volume_usd' => '1.0',
            'market_cap_usd' => '1.0',
            'available_supply' => '1.0',
            'total_supply' => '1.0',
            'percent_change_1h' => '1.0',
            'percent_change_24h' => '1.0',
            'percent_change_7d' => '1.0',
            'last_updated' => '1501580000'
        ]);

        $this->mockCurrencyServiceHttpClients([
            [
                'expected_method' => 'GET',
                'expected_uri' => 'ticker',
                'expected_response' => [
                    [
                        'id' => 'bitcoin_test',
                        'name' => 'Bitcoin',
                        'symbol' => 'BTC',
                        'rank' => '1',
                        'price_usd' => '2789.85',
                        'price_btc' => '1.0',
                        '24h_volume_usd' => '1042300000.0',
                        'market_cap_usd' => '45981643716.0',
                        'available_supply' => '16481762.0',
                        'total_supply' => '16481762.0',
                        'percent_change_1h' => '1.24',
                        'percent_change_24h' => '0.7',
                        'percent_change_7d' => '5.35',
                        'last_updated' => '1501583083'
                    ],
                    [
                        'id' => 'ethereum_test',
                        'name' => 'Ethereum',
                        'symbol' => 'ETH',
                        'rank' => '2',
                        'price_usd' => '217.24',
                        'price_btc' => '0.0797305',
                        '24h_volume_usd' => '1241380000.0',
                        'market_cap_usd' => '20357233834.0',
                        'available_supply' => '93708497.0',
                        'total_supply' => '93708497.0',
                        'percent_change_1h' => '0.48',
                        'percent_change_24h' => '10.35',
                        'percent_change_7d' => '2.18',
                        'last_updated' => '1501583062'
                    ]
                ]
            ]
        ], []);

        $service = $this->app->make(\App\Services\CurrencyService::class);

        $service->updateLocalCoinData();

        $this->seeInDatabase('coins', ['id' => 'bitcoin_test', 'price_usd' => 2789.85]);
        $this->seeInDatabase('coins', ['id' => 'ethereum_test', 'price_usd' => 217.24]);
    }

    public function testLocalCurrencyUpdate()
    {
        CurrencyRate::create(['base' => 'USD', 'target' => 'AED', 'rate' => 1.0]);
        CurrencyRate::create(['base' => 'USD', 'target' => 'AFN', 'rate' => 1.0]);

        $this->mockCurrencyServiceHttpClients([], [
            [
                'expected_method' => 'GET',
                'expected_uri' => 'latest.json',
                'expected_response' => [
                    'timestamp' => 1501660800,
                    'base' => 'USD',
                    'rates' => [
                        'AED' => 3.672896,
                        'AFN' => 68.449529,
                        'ALL' => 112.39278,
                        'AMD' => 478.975
                    ]
                ]
            ]
        ]);

        $service = $this->app->make(\App\Services\CurrencyService::class);

        $service->updateLocalCurrencyData();

        $this->seeInDatabase('currency_rates', ['base' => 'USD', 'target' => 'AED', 'rate' => 3.672896]);
        $this->seeInDatabase('currency_rates', ['base' => 'USD', 'target' => 'AFN', 'rate' => 68.449529]);
        $this->seeInDatabase('currency_rates', ['base' => 'USD', 'target' => 'ALL', 'rate' => 112.39278]);
        $this->seeInDatabase('currency_rates', ['base' => 'USD', 'target' => 'AMD', 'rate' => 478.975]);
    }

    public function testInvalidLocalCurrencyUpdate()
    {
        $this->mockCurrencyServiceHttpClients([], [
            [
                'expected_method' => 'GET',
                'expected_uri' => 'latest.json',
                'expected_response' => [
                    'timestamp' => 1501660800,
                    'base' => 'EUR'
                ]
            ]
        ]);

        $this->expectException('Exception');

        $service = $this->app->make(\App\Services\CurrencyService::class);
        $service->updateLocalCurrencyData();
    }

    /**
     * Mocks http client so communication with coin and currency APIs can be mocked
     * and only app functionality is tested.
     *
     * @param array $expectedCoinRequests
     * @param array $expectedCurrencyRequests
     */
    protected function mockCurrencyServiceHttpClients(
        array $expectedCoinRequests,
        array $expectedCurrencyRequests
    )
    {
        $httpCoinClient = $this->mockHttpClient($expectedCoinRequests);
        $httpCurrencyClient = $this->mockHttpClient($expectedCurrencyRequests);
        $this->app->bind(\App\Services\CurrencyService::class, function ($app) use (
            $httpCoinClient,
            $httpCurrencyClient
        ) {
            return new \App\Services\CurrencyService(
                $httpCoinClient,
                $httpCurrencyClient,
                $app['log']
            );
        });
    }
}