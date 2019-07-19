<?php

use App\Models\TradingBotRequest;
use App\TradingBot\FakeBot;
use App\TradingBot\TradingBot;

trait UsesMockTradingBotTrait
{
    protected function mockTradingBot(array $expectedResponses)
    {
        $methods = array_unique(array_map(function($data) {
            return $data['method'];
        }, $expectedResponses));
        $methods = implode(',', $methods);
        $mock = \Mockery::mock(TradingBot::class . "[$methods]", [new FakeBot()])
            ->shouldAllowMockingProtectedMethods();
        foreach ($expectedResponses as $data) {
            $method = $data['method'];
            $expectedResponse = $data['expected_response'];
            $expectation = $mock->shouldReceive($method);
            $expectation->withArgs(function ($tradingBotRequest) {
                return true;
            })
                ->andReturnUsing($expectedResponse);
        }
        $this->app->bind(TradingBot::class, function () use ($mock) {
            return $mock;
        });
    }
}