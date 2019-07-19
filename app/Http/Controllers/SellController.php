<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Services\Exceptions\InvalidOrMissingDataException;
use App\Services\Exceptions\TradingBotResponseException;
use App\Services\TradeService;
use App\Views\TradeView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellController extends ApiController
{
    public function post(Request $request, Auth $auth, TradeService $tradeService)
    {
        $validator = Validator::make($request->input(), [
            'exchange_account_id' => 'required|exists:exchange_accounts,id',
            'trade_id' => 'required|exists:trades,id',
            'base_coin_id' => 'required|exists:coins,symbol|in:BTC',
            'target_coin_id' => 'required|exists:coins,symbol',
            'quantity' => 'required',
            'rate' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $exchangeAccount = ExchangeAccount::find($request->input('exchange_account_id'));
        $this->validateUserAccess($exchangeAccount->user_id, $auth);
        $this->hasActivePackage($auth, $exchangeAccount->exchange_id, $request->get('mode') == TradeService::TEST_MODE);

        try {
            $trade = $tradeService->sell(
                Trade::find($request->input('trade_id')),
                Coin::findBySymbol($request->get('base_coin_id')),
                Coin::findBySymbol($request->get('target_coin_id')),
                $exchangeAccount,
                (double)$request->get('quantity'),
                (double)$request->get('rate'),
                $request->get('mode')
            );
            $view = new TradeView();

            return response()->json($view->render($trade));
        } catch (TradingBotResponseException $e) {
            // TODO Map responses to human readable errors in a dedicated class
            if ($e->getMessage() == 'APIKEY_INVALID') {
                return response()->json("Invalid exchange API credentials", 422);
            }

            return response()->json($e->getMessage(), 500);
        } catch (InvalidOrMissingDataException $e) {
            return response()->json($e->getData(), 422);
        }
    }
}