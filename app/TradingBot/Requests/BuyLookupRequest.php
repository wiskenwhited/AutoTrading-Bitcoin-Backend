<?php

namespace App\TradingBot\Requests;

class BuyLookupRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'coin'
    ];

    protected $channel = self::GLOBAL_CHANNEL;
}