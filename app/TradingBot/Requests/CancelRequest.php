<?php

namespace App\TradingBot\Requests;

class CancelRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'order_uuid',
        'exchange',
        'key',
        'secret',
        'user_id'
    ];

    protected $channel = self::USER_CHANNEL;
}