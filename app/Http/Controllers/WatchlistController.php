<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\Models\Watchlist;
use App\Models\Suggestion;
use App\Models\WatchlistHistory;
use App\Models\WatchlistRule;
use App\Services\TradeService;
use App\Views\WatchlistView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WatchlistController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Request $request, $exchange)
    {
        //TODO: check logic of showing, hiding items - Peter has basic idea
        $filters = $this->getFilterData($request);
        $filters['is_test'] = $request->get('mode') == 'test';
        $filters['type'] = $request->get('type') == 'sell' ? 'sell' : 'buy';

        $watchQuery = Watchlist::where('exchange', $exchange)
            ->select('coins.name as coin_name', 'watchlist.*')
            ->join('coins', 'coins.symbol', '=', 'watchlist.coin')
            ->where('watchlist.user_id', '=', $this->auth->user()->id)
            ->where('type', '=', $filters['type'])
            ->where('is_test', '=', $filters['is_test'])
            ->with('watchlist_history', 'rule');
        if ($filters['type'] == 'sell')
            $watchQuery = $watchQuery->where('follow', '=', true)->with('trade', 'coin_relation');


        $total = $watchQuery->count();
        if ($total == 0) {
            return response()->json([], 200);
        }

        $query = $this->applyPaginationData($request, $watchQuery, ['page' => ['limit' => null]]);
        $watchlist = $query->get();
        $view = new WatchlistView();

        return response()->json([
            'data' => $view->render($watchlist),
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function showHistory($id)
    {
        $watchlist = Watchlist::find($id);
        if ($watchlist->user_id != $this->auth->user()->id)
            return response()->json("Forbidden", 403);

        $history = WatchlistHistory::whereWatchlistId($id)->get();

        return response()->json([
            'data' => $history,
        ]);
    }

    public function store(Auth $auth, Request $request)
    {
        //return response()->json('Temporary disabled', 422);

        //number of interval should be optional unless we follow the rule and rule is 3 or 4
        $validator = Validator::make($request->input(), [
            'exchange' => 'required',
            'exchange_account_id' => 'required|exists:exchange_accounts,id',
            'coin' => 'required|string',
            'interval' => 'required',
            'send_sms' => 'required',
            'send_email' => 'required',
            'execute_buy' => 'required',
            'number_of_intervals' => 'required|numeric',
            'cpp' => 'required_if:follow_cpp,true',
            'prr' => 'required_if:follow_prr,true',
            'gap' => 'required_if:follow_gap,true',
            'market_cap' => 'required_if:follow_market_cap,true',
            'liquidity' => 'required_if:follow_liquidity,true',
            'buy_amount_btc' => 'required_if:execute_buy,true',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $is_test = $request->get('mode') == 'test';
        //check if unique
        $existsWatchlistItem = Watchlist::where('is_test', $is_test)->whereType('buy')->whereUserId($this->auth->user()->id)->where('coin', $request->coin)->where('exchange', $request->exchange)->where('exchange_account_id', $request->exchange_account_id)->count();
        if ($existsWatchlistItem > 0) {
            return response()->json([
                'coin' => [
                    'Coin is already in the watchlist'
                ]
            ], 422);
        }

        $exchangeAccount = ExchangeAccount::find($request->input('exchange_account_id'));
        $this->validateUserAccess($exchangeAccount->user_id, $auth);
        $this->hasActivePackage($auth, $exchangeAccount->exchange_id, $request->get('mode') == TradeService::TEST_MODE);

        $suggestion = Suggestion::where('coin', $request->coin)->where('exchange', $request->exchange)->first();
        if ($suggestion->count() == 0) {
            return response()->json('No suggestions data found for given input.', 422);
        }

        DB::beginTransaction();
        try {
            $watchlist = new Watchlist();
            $user = $this->auth->user();
            $watchlist->is_test = $is_test;
            $watchlist->user_id = $user->id;
            $watchlist->type = 'buy';
            $watchlist->follow = true;
            $watchlist->interval = $request->input('interval');
            $watchlist->exchange = $suggestion->exchange;
            $watchlist->exchange_account_id = $exchangeAccount->id;
            $watchlist->coin = $suggestion->coin;
            $watchlist->target = $suggestion->target;
            $watchlist->exchange_trend = $suggestion->exchange_trend;
            $watchlist->btc_impact = $suggestion->btc_impact;
            $watchlist->impact_1hr = $suggestion->impact_1hr;
            $watchlist->gap = $suggestion->gap;
            $watchlist->cpp = $suggestion->cpp;
            $watchlist->prr = $suggestion->prr;
            $watchlist->btc_liquidity_bought = $suggestion->btc_liquidity_bought;
            $watchlist->btc_liquidity_sold = $suggestion->btc_liquidity_sold;
            $watchlist->market_cap = $suggestion->market_cap;
            $watchlist->base = $suggestion->base;
            $watchlist->lowest_ask = $suggestion->lowest_ask;
            $watchlist->highest_bid = $suggestion->highest_bid;
            $watchlist->target_score = $suggestion->target_score;
            $watchlist->exchange_trend_score = $suggestion->exchange_trend_score;
            $watchlist->impact_1hr_change_score = $suggestion->impact_1hr_change_score;
            $watchlist->btc_impact_score = $suggestion->btc_impact_score;
            $watchlist->btc_liquidity_score = $suggestion->btc_liquidity_score;
            $watchlist->market_cap_score = $suggestion->market_cap_score;
            $watchlist->overall_score = $suggestion->overall_score;
            $watchlist->target_diff = $suggestion->target_diff;
            $watchlist->exchange_trend_diff = $suggestion->exchange_trend_diff;
            $watchlist->btc_impact_diff = $suggestion->btc_impact_diff;
            $watchlist->impact_1hr_diff = $suggestion->impact_1hr_diff;
            $watchlist->gap_diff = $suggestion->gap_diff;
            $watchlist->cpp_diff = $suggestion->cpp_diff;
            $watchlist->prr_diff = $suggestion->prr_diff;
            $watchlist->target_score_diff = $suggestion->target_score_diff;
            $watchlist->exchange_trend_score_diff = $suggestion->exchange_trend_score_diff;
            $watchlist->btc_impact_score_diff = $suggestion->btc_impact_score_diff;
            $watchlist->btc_liquidity_score_diff = $suggestion->btc_liquidity_score_diff;
            $watchlist->btc_liquidity_bought_diff = $suggestion->btc_liquidity_bought_diff;
            $watchlist->btc_liquidity_sold_diff = $suggestion->btc_liquidity_sold_diff;
            $watchlist->impact_1hr_change_score_diff = $suggestion->impact_1hr_change_score_diff;
            $watchlist->market_cap_score_diff = $suggestion->market_cap_score_diff;
            $watchlist->overall_score_diff = $suggestion->overall_score_diff;
            $watchlist->market_cap_diff = $suggestion->market_cap_diff;
            $watchlist->sms = $request->input('send_sms');
            $watchlist->email = $request->input('send_email');
            $watchlist->execute = $request->input('execute_buy');
            $watchlist->created_at = Carbon::now();
            $watchlist->save();

            // Watchlist data entry in history table
//            WatchlistHistory::create($watchlist->toArray() + ['time_of_data' => $watchlist->created_at, 'watchlist_id' => $watchlist->id]);

            $rules = new WatchlistRule();
            $rules->watchlist_id = $watchlist->id;
            $rules->number_of_intervals = $request->input('number_of_intervals');
            $rules->follow_cpp = (boolean)$request->input('follow_cpp');
            $rules->cpp_rule = $request->input('cpp_rule');
            $rules->cpp = $request->input('cpp');
            $rules->follow_prr = (boolean)$request->input('follow_prr');
            $rules->prr_rule = $request->input('prr_rule');
            $rules->prr = $request->input('prr');
            $rules->follow_gap = (boolean)$request->input('follow_gap');
            $rules->gap_rule = $request->input('gap_rule');
            $rules->gap = $request->input('gap');
            $rules->follow_market_cap = (boolean)$request->input('follow_market_cap');
            $rules->market_cap_rule = $request->input('market_cap_rule');
            $rules->market_cap = $request->input('market_cap');
            $rules->follow_liquidity = (boolean)$request->input('follow_liquidity');
            $rules->liquidity_rule = $request->input('liquidity_rule');
            $rules->liquidity = $request->input('liquidity');
            $rules->buy_amount_btc = $request->input('buy_amount_btc');

            $rules->save();

            $watchlist->load('rule');

            $smart_sell_missing = !$this->auth->user()->smart_sell_enabled && is_null($this->auth->user()->smart_sell_interval) && is_null($this->auth->user()->smart_sell_drops);
            $watchlist->setAttribute('smart_sell_missing', $smart_sell_missing);

            DB::commit();
            return response()->json(['data' => $watchlist]);
        } catch (\Exception $ex) {
            Log::error($ex);
            \DB::rollback();
            return response()->json($ex->getMessage(), 500);
        }
    }


    public function storeSell(Auth $auth, Request $request)
    {
        //return response()->json('Temporary disabled', 422);
        $validator = Validator::make($request->input(), [
            'exchange_account_id' => 'required|exists:exchange_accounts,id',
            'trade_id' => 'required',
            'smart_sell_interval' => 'required|integer',
            'exit_target' => 'required|numeric',
            'smart_sell_drops' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trade = Trade::find($request->trade_id);
        if (!$trade)
            return response()->json('Trade does not exist', 422);

        $is_test = $request->get('mode') == 'test';
        //check if unique
        $existsWatchlistItem = Watchlist::where('is_test',  $is_test)->whereType('sell')->whereUserId($this->auth->user()->id)->where('coin', $trade->target_coin_id)->where('exchange', $trade->exchange_id)->where('exchange_account_id', $request->exchange_account_id)->count();
        if ($existsWatchlistItem > 0) {
            return response()->json([
                'coin' => [
                    'Coin is already in the watchlist'
                ]
            ], 422);
        }
        $exchangeAccount = ExchangeAccount::find($request->input('exchange_account_id'));
        $this->validateUserAccess($exchangeAccount->user_id, $auth);
        $this->hasActivePackage($auth, $exchangeAccount->exchange_id, $request->get('mode') == TradeService::TEST_MODE);

        DB::beginTransaction();
        try {
            $watchlist = new Watchlist();
            $user = $this->auth->user();
            $watchlist->follow = $trade->order_type == Trade::STATUS_BOUGHT;
            $watchlist->type = 'sell';
            $watchlist->is_test = $is_test;
            $watchlist->trade_id = $trade->id;
            $watchlist->user_id = $user->id;
            $watchlist->smart_sell_interval = $request->input('smart_sell_interval');
            $watchlist->exchange = $trade->exchange_id;
            $watchlist->exchange_account_id = $exchangeAccount->id;
            $watchlist->coin = $trade->target_coin_id;
            $watchlist->price_per_unit = $trade->price_per_unit;
            $watchlist->follow = true;

            $watchlist->sms = $request->input('exit_notified_by_sms') ?: false;
            $watchlist->email = $request->input('exit_notified_by_email') ?: false;
            $watchlist->execute_sell = true;
            $watchlist->created_at = Carbon::now();
            $watchlist->save();


            $rules = new WatchlistRule();
            $rules->watchlist_id = $watchlist->id;
            $rules->sell_target = $request->input('exit_target');
            $rules->smart_sell_drops = $request->input('smart_sell_drops');

            $rules->save();
            $watchlist->load('rule');

            DB::commit();
            return response()->json(['data' => $watchlist]);
        } catch (\Exception $ex) {
            Log::error($ex);
            \DB::rollback();
            return response()->json($ex->getMessage(), 500);
        }

    }

    public function showSellRule(Request $request, $id)
    {

        $watchlist = Watchlist::with('rule')->findOrFail($id);
        if ($watchlist->user_id != $this->auth->user()->id || $watchlist->type == 'buy')
            return response()->json("Forbidden", 403);

        if (!$watchlist->rule)
            return response()->json("Missing rules", 404);

        $data = [
            'exit_target' => $watchlist->rule->sell_target,
            'smart_sell_drops' => $watchlist->rule->smart_sell_drops,
            'smart_sell_interval' => $watchlist->smart_sell_interval,
            'exit_notified_by_sms' => $watchlist->sms,
            'exit_notified_by_email' => $watchlist->email,
            'is_test' => $watchlist->is_test
        ];

        return response()->json($data);


    }

    public function updateSell(Request $request, $id)
    {
        //return response()->json('Temporary disabled', 422);

        $validator = Validator::make($request->input(), [
            'exit_target' => 'required|numeric',
            'smart_sell_drops' => 'required|integer',
            'smart_sell_interval' => 'required|integer',
            'exit_notified_by_sms' => 'required',
            'exit_notified_by_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $watchlist = Watchlist::findOrFail($id);
        if ($watchlist->user_id != $this->auth->user()->id || $watchlist->type == 'buy')
            return response()->json("Forbidden", 403);

        $rules = WatchlistRule::whereWatchlistId($id)->first();

        $watchlist->smart_sell_interval = $request->input('smart_sell_interval');
        $watchlist->sms = $request->input('exit_notified_by_sms') ?: false;
        $watchlist->email = $request->input('exit_notified_by_email') ?: false;
        $rules->sell_target = $request->input('exit_target');
        $rules->smart_sell_drops = $request->input('smart_sell_drops');
        $rules->save();
        $watchlist->save();
        $watchlist->load('rule');

        return response()->json([
            'data' => $watchlist
        ]);
    }

    public function updateRule(Request $request, $id)
    {
        //number of interval should be optional unless we follow the rule and rule is 3 or 4
        $validator = Validator::make($request->input(), [
            'cpp' => 'required_if:follow_cpp,true',
            'prr' => 'required_if:follow_prr,true',
            'gap' => 'required_if:follow_gap,true',
            'number_of_intervals' => 'numeric',
            'interval' => 'integer',
            'market_cap' => 'required_if:follow_market_cap,true',
            'liquidity' => 'required_if:follow_liquidity,true',
            'buy_amount_btc' => 'required_if:execute_buy,true',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $watchlist = Watchlist::findOrFail($id);
        if ($watchlist->user_id != $this->auth->user()->id || $watchlist->type == 'sell')
            return response()->json("Forbidden", 403);

        if (!is_null($request->input('send_sms')))
            $watchlist->sms = $request->input('send_sms');
        if (!is_null($request->input('send_email')))
            $watchlist->email = $request->input('send_email');
        if (!is_null($request->input('execute_buy')))
            $watchlist->execute = $request->input('execute_buy');
        if (!is_null($request->input('interval')))
            $watchlist->interval = $request->input('interval');
        $watchlist->save();

        $rules = WatchlistRule::whereWatchlistId($id)->first();
        if (!$rules) {
            $rules = new WatchlistRule();
            $rules->watchlist_id = $id;
        }


        if (!is_null($request->input('number_of_intervals')))
            $rules->number_of_intervals = $request->input('number_of_intervals');
        if (!is_null((boolean)$request->input('follow_cpp')))
            $rules->follow_cpp = (boolean)$request->input('follow_cpp');
        if (!is_null($request->input('cpp_rule')))
            $rules->cpp_rule = $request->input('cpp_rule');
        if (!is_null($request->input('cpp')))
            $rules->cpp = $request->input('cpp');
        if (!is_null((boolean)$request->input('follow_prr')))
            $rules->follow_prr = (boolean)$request->input('follow_prr');
        if (!is_null($request->input('prr_rule')))
            $rules->prr_rule = $request->input('prr_rule');
        if (!is_null($request->input('prr')))
            $rules->prr = $request->input('prr');
        if (!is_null((boolean)$request->input('follow_gap')))
            $rules->follow_gap = (boolean)$request->input('follow_gap');
        if (!is_null($request->input('gap_rule')))
            $rules->gap_rule = $request->input('gap_rule');
        if (!is_null($request->input('gap')))
            $rules->gap = $request->input('gap');
        if (!is_null((boolean)$request->input('follow_market_cap')))
            $rules->follow_market_cap = (boolean)$request->input('follow_market_cap');
        if (!is_null($request->input('market_cap_rule')))
            $rules->market_cap_rule = $request->input('market_cap_rule');
        if (!is_null($request->input('market_cap')))
            $rules->market_cap = $request->input('market_cap');
        if (!is_null((boolean)$request->input('follow_liquidity')))
            $rules->follow_liquidity = (boolean)$request->input('follow_liquidity');
        if (!is_null($request->input('liquidity_rule')))
            $rules->liquidity_rule = $request->input('liquidity_rule');
        if (!is_null($request->input('liquidity')))
            $rules->liquidity = $request->input('liquidity');
        if (!is_null($request->input('buy_amount_btc')))
            $rules->buy_amount_btc = $request->input('buy_amount_btc');
//        $rules->buy_quantity = $request->input('buy_quantity');
//        $rules->sell_amount_btc = $request->input('sell_amount_btc');
//        $rules->sell_quantity = $request->input('sell_quantity');
        $rules->save();

        return response()->json([
            'data' => $rules]
        );
    }


    public function update(Request $request, $id)
    {
        $watchlist = Watchlist::where('id', $id)->first();
        if (!$watchlist) {
            return response()->json('No watchlist found for given input.', 422);
        }
        if ($watchlist->user_id != $this->auth->user()->id)
            return response()->json("Forbidden", 403);

        if ($request->get('email') == '' && $request->get('sms') == '' && $request->get('execute') == '') {
            return response()->json('At least one parameter required.', 422);
        }
        if ($watchlist->rule) {
            if ($request->get('email')) {
                $watchlist->rule->email_sent = false;
            }
            if ($request->get('sms')) {
                $watchlist->rule->sms_sent = false;
            }
            if ($request->get('execute') && $watchlist->type == 'buy') {
                $watchlist->rule->bought = false;
            }
//            if ($request->get('execute') && $watchlist->type == 'buy') {
//                $watchlist->rule->sold = false;
            $watchlist->rule->save();
        }

        $watchlist->fill(array_merge($request->input(), ['id' => $id]));
        $watchlist->save();
        return response()->json($watchlist);
    }

    public function delete($id)
    {
        $user = $this->auth->user();
        $watchlist = Watchlist::findOrFail($id);

        if ($watchlist->user_id != $user->id) {
            return response('Forbidden', 403);
        }
        if ($watchlist->rules)
            $watchlist->rules->delete();
        if ($watchlist->history)
            $watchlist->history->delete();
        $watchlist->delete();
        return response()->json([], 200);
    }

}
