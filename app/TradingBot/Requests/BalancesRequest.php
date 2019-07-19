<?php

namespace App\TradingBot\Requests;

class BalancesRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'key',
        'secret'
    ];
}