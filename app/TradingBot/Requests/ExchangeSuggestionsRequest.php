<?php

namespace App\TradingBot\Requests;

class ExchangeSuggestionsRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange'
    ];
}