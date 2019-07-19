<?php

namespace App\TradingBot\Requests;

class SellLookupRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'coin'
    ];

    protected $channel = self::GLOBAL_CHANNEL;
}