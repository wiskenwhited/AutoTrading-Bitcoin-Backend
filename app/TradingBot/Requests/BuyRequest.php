<?php

namespace App\TradingBot\Requests;

class BuyRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'base',
        'strategy',
        'coin',
        'quantity',
        'rate',
        'key',
        'secret',
        'user_id'
    ];

    protected $channel = self::USER_CHANNEL;
}