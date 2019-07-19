<?php

namespace App\TradingBot\Requests;

class OrderStatusRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'order_id'
    ];

    protected $channel = self::USER_CHANNEL;
}