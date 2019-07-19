<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Services\Exceptions\TradingBotResponseException;
use App\Services\TradeService;
use App\TradingBot\Requests\BalancesRequest;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\TradingBot;
use App\Views\TradeView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TradeController extends ApiController
{
    public function index(Auth $auth, Request $request, TradeService $tradeService)
    {
        $filters = $this->getFilterData($request);
        $filters['is_test'] = $request->get('mode') == 'test';
        $view = new TradeView();
        list($trades, $total) = $tradeService->getTradesWithCalculatedFields(
            $auth->user(),
            $this->getPaginationData($request),
            $this->getSortingData($request, $view),
            $filters,
            $request->get('exchange_account_id') ? $request->get('exchange_account_id') : null
        );

        return response()->json([
            'data' => $view->render($trades),
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function patch($id, Request $request, Auth $auth)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->user_id != $auth->user()->id) {
            return response()->json("Forbidden", 403);
        }

        if ($request->input('target_shrink_differential')) {
            $trade->target_shrink_differential = $request->input('target_shrink_differential');
            $trade->target_percent = null;
        } elseif ($request->input('target_price')) {
            $trade->target_shrink_differential =null;
            $trade->target_percent = $request->input('target_price');
        }

        $trade->save();
        $producedTrade = Trade::byParentTrade($id)->first();
        if ($producedTrade) {
            $producedTrade->target_shrink_differential = $trade->target_shrink_differential;
            $producedTrade->target_percent = $trade->target_percent;
            $producedTrade->save();
        }
        $view = new TradeView();

        return response()->json($view->render($trade));
    }

    public function total(Request $request, Auth $auth, TradeService $tradeService, $exchangeAccountId)
    {
        /**
         * @var ExchangeAccount $exchangeAccount
         */
        $exchangeAccount =  ExchangeAccount::findOrFail($exchangeAccountId);
        $this->validateUserAccess($exchangeAccount->user_id, $auth);
        $isTestMode = $request->get('mode') == 'test';

        $total_profit = $auth->user()->total_profit($exchangeAccount->exchange_id, $isTestMode);
        $profit_realized = $auth->user()->profit_realized($exchangeAccount->exchange_id, $isTestMode);

        try {
            $totalBtc = $isTestMode ? 0 : $tradeService->getTotalCapital($exchangeAccount);
        } catch (TradingBotResponseException $e) {
            $totalBtc = 0;
        }

        return response()->json(array_merge($total_profit, $profit_realized, [
            'total_capital' => $totalBtc,
            'total_capital_currency' => 'BTC'
        ]));
    }

    public function delete($id)
    {
        $trade = Trade::findOrFail($id);
        $trade->delete();

        return response()->json([], 200);
    }
}