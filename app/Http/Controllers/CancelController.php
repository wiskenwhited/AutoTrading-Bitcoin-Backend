<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Services\Exceptions\InvalidOrMissingDataException;
use App\Services\Exceptions\TradingBotResponseException;
use App\Services\TradeService;
use App\Views\TradeView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CancelController extends ApiController
{
    public function post(Request $request, Auth $auth, TradeService $tradeService)
    {
        $validator = Validator::make($request->input(), [
            'trade_id' => 'required|exists:trades,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $trade = Trade::find($request->input('trade_id'));
        $this->validateUserAccess($trade->exchangeAccount->user_id, $auth);

        try {
            $tradeService->cancel($trade, $request->get('mode'));

            return response()->json([], 200);
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