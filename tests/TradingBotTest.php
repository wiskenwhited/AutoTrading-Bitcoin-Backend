<?php

use App\Models\TradingBotRequest;
use App\TradingBot\TradingBot;

class TradingBotTest extends ApiTestCase
{
    use UsesMockTradingBotTrait;

    public function testErrorParsing()
    {
        $tradingBotRequest = TradingBotRequest::create([
            'json_response' => [
                'err' => 'An error message',
                'data' => null
            ],
            'json_payload' => [],
            'request_type' => 'buy'
        ]);
        /**
         * @var TradingBot $tradingBot
         */
        $tradingBot = $this->app->make(TradingBot::class);
        dd($tradingBot->getTradingBotRequestResponse($tradingBotRequest));
    }
}