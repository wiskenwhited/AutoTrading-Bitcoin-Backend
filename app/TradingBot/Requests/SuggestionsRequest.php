<?php

namespace App\TradingBot\Requests;

class SuggestionsRequest extends AbstractTradingBotRequest
{
    protected $params = [
        'exchange',
        'base',
        'strategy',
        'number_of_coins',
        'frugality_score',
    ];
}