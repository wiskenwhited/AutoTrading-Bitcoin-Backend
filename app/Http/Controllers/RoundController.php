<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\AutoTrading\AutoTradingService;
use App\Models\ExchangeAccount;
use App\Models\Round;
use App\Views\RoundView;
use Illuminate\Http\Request;

class RoundController extends ApiController
{
    /**
     * @param Request $request
     * @param Auth $auth
     * @param AutoTradingService $autoTradingService
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request, Auth $auth, AutoTradingService $autoTradingService)
    {
        $account = ExchangeAccount::findOrFail($request->get('exchange_account_id'));
        $this->validateUserAccess($account->user_id, $auth);

        // TODO Move to service and catch exception here?
        if (Round::active()->byExchangeAccount($account)->exists()) {
            return response()->json('Round already started', 422);
        }
        if (
            ! $account->auto_global_round_duration ||
            ! $account->auto_global_cycles ||
            ! $account->auto_global_round_granularity ||
            ! $account->auto_global_strategy
        ) {
            return response()->json('Missing required configuration to start round', 422);
        }

        $round = $autoTradingService->startRound($account);
        if (! $round) {
            return response()->json('Cannot start a new round, active round already running', 422);
        }

        $view = new RoundView();
        $round->load('exchangeAccount');

        return response()->json($view->render($round));
    }

    /**
     * @param Auth $auth
     * @return \Illuminate\Http\JsonResponse
     * @todo Move logic to service
     */
    public function stop(Request $request, Auth $auth)
    {
        $account = ExchangeAccount::findOrFail($request->get('exchange_account_id'));
        $this->validateUserAccess($account->user_id, $auth);
        $round = Round::active()->byExchangeAccount($account)->first();
        if (! $round) {
            return response()->json('No round started', 422);
        }

        $round->update(['is_canceled' => true]);

        return response()->json([], 200);
    }

    public function status(Auth $auth, $exchangeAccountId)
    {
        $account = ExchangeAccount::findOrFail($exchangeAccountId);
        $this->validateUserAccess($account->user_id, $auth);
        $round = Round::active()->byExchangeAccount($account)->first();
        if (! $round) {
            return response()->json(['active' => false]);
        }

        $view = new RoundView();
        $round->load('exchangeAccount');

        return response()->json($view->render($round));
    }
}