<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Models\MarketSummary;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\BuyLookupRequest;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\Requests\SellLookupRequest;
use App\TradingBot\TradingBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class MarketDataController extends ApiController
{

    private $marketOrderService;

    public function __construct(MarketOrderService $marketOrderService)
    {
        $this->marketOrderService = $marketOrderService;
    }

    public function marketOrder(TradingBot $tradingBot, $exchange, $coin)
    {

        $response = $tradingBot->buyLookup(new BuyLookupRequest([
            'exchange' => $exchange,
            'coin' => $coin
        ]), TradingBot::WAIT);

        $marketOrder = null;
        if (in_array($exchange, ['bittrex', 'bitfinex'])) {
            $marketOrder = $this->marketOrderService->retrieveMarketOrder($exchange, $coin);
        }


        if ($error = array_get($response, 'error')) {
            return response()->json($error, 422);
        }


        return response()->json([
            'data' => [array_get($response, 'data')],
            'be' => $marketOrder ? $marketOrder->toArray() : null
        ]);
    }
    public function marketOrderSell(TradingBot $tradingBot, $exchange, $coin)
    {

        $response = $tradingBot->sellLookup(new SellLookupRequest([
            'exchange' => $exchange,
            'coin' => $coin
        ]), TradingBot::WAIT);


        $marketOrder = null;
        if (in_array($exchange, ['bittrex', 'bitfinex'])) {
            $marketOrder = $this->marketOrderService->retrieveMarketOrder($exchange, $coin, true);
        }

        if ($error = array_get($response, 'error')) {
            return response()->json($error, 422);
        }


        return response()->json([
            'data' => [array_get($response, 'data')],
            'be' => $marketOrder ? $marketOrder->toArray() : null
        ]);
    }
    
    public function marketSummary($exchange)
    {
        $marketOrder = MarketSummary::whereExchangeId($exchange)->get();

        return response()->json([
            'data' => $marketOrder
        ]);
    }
}