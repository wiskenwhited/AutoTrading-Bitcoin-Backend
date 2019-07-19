<?php

namespace App\Services;


use App\Helpers\EmailHelper;
use App\Helpers\TwilioHelper;
use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Models\Suggestion;
use App\Models\SuggestionHistory;
use App\Models\Trade;
use App\Models\Watchlist;
use App\Models\WatchlistHistory;
use App\Models\WatchlistRule;
use App\Services\Exceptions\InvalidOrMissingDataException;
use App\Services\Exceptions\TradingBotResponseException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WatchListService
{
    private $watchlist;
    private $oldWatchlist;
    private $tradeService;
    private $trade;
    private $user;
    private $time_general;

    private $rules;
    private $history;
    private $rulesMet = [
        'cpp' => false,
        'prr' => false,
        'gap' => false,
        'market_cap' => false,
        'liquidity' => false,
    ];

    private $friendlyNames = [
        'cpp' => 'CPP',
        'prr' => 'PRR',
        'gap' => 'GAP',
        'market_cap' => 'Market Cap',
        'liquidity' => 'Liquidity',
    ];

    public function __construct(TradeService $tradeService)
    {
        $this->tradeService = $tradeService;
    }

    public function handleWatchlistProccess($watchlist)
    {
//        $time = microtime(true);

        $this->user = $watchlist->user;

        $this->rules = $watchlist->rule;
        $this->watchlist = $this->populateWatchlist($watchlist);
        $this->history = $this->watchlist->watchlist_history;

        if ($this->rules && $this->meetRules()) {
            $matchDate = Carbon::now();
            if ($watchlist->sms && !$this->rules->sms_sent) {
                if ($this->sendText($this->rulesMet, $this->user, $matchDate)) {
                    $this->rules->sms_sent = true;
                    $this->rules->save();
                }
            }
            if ($watchlist->email && !$this->rules->email_sent) {
                if ($this->sendEmail($this->rulesMet, $watchlist, $this->user, $matchDate)) {
                    $this->rules->email_sent = true;
                    $this->rules->save();
                }
            }
            if ($watchlist->execute && !$this->rules->bought) {
                if ($this->executeBuy()) {
                    $this->rules->bought = true;
                    $this->rules->save();

                    $this->watchlist->follow = false;
                    $this->watchlist->save();
                }
            }
        }

//        var_dump((microtime(true) - $time));
    }

    public function handleSellWatchlistProccess($watchlist)
    {
        $this->time_general = microtime(true);
        $status = "Completed\n";

        try {
//            Log::info("WatchlistJob - Measures", [
//                'watchlist_id' => $watchlist->id,
//                'time_passed' => (microtime(true) - $this->time_general),
//                'status' => 'handle',
//                'check_time' => $watchlist->last_check,
//                'now' => Carbon::now()
//            ]);
            $this->user = $watchlist->user;

            if (!$this->user) {
                echo (microtime(true) - $this->time_general) . " - Missing user\n";
                return;
            }


            $this->rules = $watchlist->rule;

            if (!$this->rules) {
                echo (microtime(true) - $this->time_general) . " - Missing rules\n";
                return;
            }
//            Log::info("WatchlistJob - Measures", [
//                'watchlist_id' => $watchlist->id,
//                'status' => 'prepopulate history',
//                'time_passed' => (microtime(true) - $this->time_general),
//                'check_time' => $watchlist->last_check,
//                'now' => Carbon::now()
//            ]);
            $this->watchlist = $this->populateSellWatchlist($watchlist);

            if ($this->watchlist->stop_follow) {

                $this->watchlist->follow = false;
                $this->watchlist->save();

                echo (microtime(true) - $this->time_general) . " - STOPPED\n";
//                Log::info("WatchlistJob - Measures", [
//                    'watchlist_id' => $watchlist->id,
//                    'status' => 'immediately stop',
//                    'time_passed' => (microtime(true) - $this->time_general),
//                    'check_time' => $watchlist->last_check,
//                    'now' => Carbon::now()
//                ]);
                return;
            }

//            Log::info("WatchlistJob - Measures", [
//                'watchlist_id' => $watchlist->id,
//                'status' => 'populated history',
//                'time_passed' => (microtime(true) - $this->time_general),
//                'check_time' => $watchlist->last_check,
//                'now' => Carbon::now()
//            ]);


            $this->history = $this->watchlist->watchlist_history;
            $this->trade = $this->watchlist->trade;

//            Log::info("WatchlistJob - Measures", [
//                'watchlist_id' => $watchlist->id,
//                'status' => 'meeting rules',
//                'time_passed' => (microtime(true) - $this->time_general),
//                'check_time' => $watchlist->last_check,
//                'now' => Carbon::now()
//            ]);

            if ($this->rules && !$this->rules->sold && $this->meetSellRules()) {


//                Log::info("WatchlistJob - Measures", [
//                    'watchlist_id' => $watchlist->id,
//                    'status' => 'rules met',
//                    'time_passed' => (microtime(true) - $this->time_general),
//                    'check_time' => $watchlist->last_check,
//                    'now' => Carbon::now()
//                ]);

                $matchDate = Carbon::now();
                $trade = $this->executeSell();

//                Log::info("WatchlistJob - Measures", [
//                    'watchlist_id' => $watchlist->id,
//                    'status' => 'selling executed',
//                    'time_passed' => (microtime(true) - $this->time_general),
//                    'check_time' => $watchlist->last_check,
//                    'now' => Carbon::now()
//                ]);

                if ($trade) {


                    $watchlist->load('coin_relation');

                    if ($watchlist->sms && $this->sendSoldText($watchlist, $this->user, $matchDate)) {
                        $this->rules->sms_sent = true;
                    }
                    if ($watchlist->email && $this->sendSoldEmail($watchlist, $this->user, $matchDate, $trade)) {
                        $this->rules->email_sent = true;
                    }

                    $this->rules->sold = true;
                    $this->rules->save();

                    $watchlist->follow = false;


//                    Log::info("WatchlistJob - Measures", [
//                        'watchlist_id' => $watchlist->id,
//                        'status' => 'sold completed',
//                        'check_time' => $watchlist->last_check,
//                        'now' => Carbon::now()
//                    ]);

                    $status = "Sold\n";

                } else {

//                    Log::info("WatchlistJob - Measures", [
//                        'watchlist_id' => $watchlist->id,
//                        'status' => 'unfollowed',
//                        'time_passed' => (microtime(true) - $this->time_general),
//                        'check_time' => $watchlist->last_check,
//                        'now' => Carbon::now()
//                    ]);

                    $watchlist->follow = false;
                    $status = "Unfollowed\n";

                }

                $watchlist->stop_follow = true;
                $watchlist->save();

            }

        } catch (\Exception $ex) {
            echo (microtime(true) - $this->time_general) . ' - Error: ' . $ex->getMessage();
        }

        echo (microtime(true) - $this->time_general) . ' - ' . $status;

    }

    private function populateSellWatchlist($watchlist)
    {
        $this->oldWatchlist = clone $watchlist;

        $suggestion = Suggestion::where('coin', $watchlist->coin)->where('exchange', $watchlist->exchange)->first();


        if (!$suggestion) {
            echo (microtime(true) - $this->time_general) . " - Suggestion does not exist\n";
            return $watchlist;
        }

        if ($watchlist->price_per_unit == $suggestion->highest_bid || $suggestion->btc_liquidity_bought == 0 || $suggestion->btc_liquidity_sold == 0) {
            echo (microtime(true) - $this->time_general) . " - Same Price - Liquidity is 0\n";
            return $watchlist;
        }

//        Log::info("WatchlistJob - Measures", [
//            'watchlist_id' => $watchlist->id,
//            'status' => 'creating history',
//            'time_passed' => (microtime(true) - $this->time_general),
//            'check_time' => $watchlist->last_check,
//            'now' => Carbon::now()
//        ]);

        WatchlistHistory::create($watchlist->toArray() + ['time_of_data' => $watchlist->created_at, 'watchlist_id' => $watchlist->id]);

//        Log::info("WatchlistJob - Measures", [
//            'watchlist_id' => $watchlist->id,
//            'status' => 'history created',
//            'time_passed' => (microtime(true) - $this->time_general),
//            'check_time' => $watchlist->last_check,
//            'now' => Carbon::now()
//        ]);

        //Deleting watchlist history outside the selected interval
        $watchlistInsideInterval = WatchlistHistory::select('id')->whereWatchlistId($watchlist->id)->orderBy('created_at', 'DESC')->limit($watchlist->smart_sell_interval)->get();
        if ($watchlistInsideInterval->count()) {
            $watchlistInsideIntervalIds = array_pluck($watchlistInsideInterval, 'id');
            WatchlistHistory::whereNotIn('id', $watchlistInsideIntervalIds)->whereWatchlistId($watchlist->id)->delete();
        }

//        Log::info("WatchlistJob - Measures", [
//            'watchlist_id' => $watchlist->id,
//            'status' => 'deleted old history items',
//            'time_passed' => (microtime(true) - $this->time_general),
//            'check_time' => $watchlist->last_check,
//            'now' => Carbon::now()
//        ]);
        $watchlist->price_per_unit = $suggestion->highest_bid;
        $watchlist->price_per_unit_direction = 0;
        if ($this->oldWatchlist->price_per_unit < $suggestion->highest_bid)
            $watchlist->price_per_unit_direction = 1;
        if ($this->oldWatchlist->price_per_unit > $suggestion->highest_bid)
            $watchlist->price_per_unit_direction = -1;

        $watchlist->save();
        $watchlist->load('watchlist_history');

//        Log::info("WatchlistJob - Measures", [
//            'watchlist_id' => $watchlist->id,
//            'time_passed' => (microtime(true) - $this->time_general),
//            'status' => 'watchlist history loaded',
//            'check_time' => $watchlist->last_check,
//            'now' => Carbon::now()
//        ]);
        return $watchlist;
    }

    private function populateWatchlist($watchlist)
    {
        $this->oldWatchlist = clone $watchlist;
        $suggestion = Suggestion::where('coin', $watchlist->coin)->where('exchange', $watchlist->exchange)->first();
        WatchlistHistory::create($watchlist->toArray() + ['time_of_data' => $watchlist->created_at, 'watchlist_id' => $watchlist->id]);
        if (!$suggestion)
            return $watchlist;

        //Deleting watchlist history outside the selected interval
        $watchlistInsideInterval = WatchlistHistory::select('id')->whereWatchlistId($watchlist->id)->orderBy('created_at', 'DESC')->limit($this->rules->number_of_intervals)->get();
        if ($watchlistInsideInterval->count()) {
            $watchlistInsideIntervalIds = array_pluck($watchlistInsideInterval, 'id');
            WatchlistHistory::whereNotIn('id', $watchlistInsideIntervalIds)->whereWatchlistId($watchlist->id)->delete();
        }

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

        $watchlist->target_diff = $suggestion->target - $this->oldWatchlist->target;
        $watchlist->exchange_trend_diff = $suggestion->exchange_trend - $this->oldWatchlist->exchange_trend;
        $watchlist->btc_impact_diff = $suggestion->btc_impact - $this->oldWatchlist->btc_impact;
        $watchlist->impact_1hr_diff = $suggestion->impact_1hr - $this->oldWatchlist->impact_1hr;
        $watchlist->gap_diff = $suggestion->gap - $this->oldWatchlist->gap;
        $watchlist->cpp_diff = $suggestion->cpp - $this->oldWatchlist->cpp;
        $watchlist->prr_diff = $suggestion->prr - $this->oldWatchlist->prr;
        $watchlist->target_score_diff = $suggestion->target_score - $this->oldWatchlist->target_score;
        $watchlist->exchange_trend_score_diff = $suggestion->exchange_trend_score - $this->oldWatchlist->exchange_trend_score;
        $watchlist->btc_impact_score_diff = $suggestion->btc_impact_score - $this->oldWatchlist->btc_impact_score;
        $watchlist->btc_liquidity_score_diff = $suggestion->btc_liquidity_score - $this->oldWatchlist->btc_liquidity_score;
        $watchlist->btc_liquidity_bought_diff = $suggestion->btc_liquidity_bought - $this->oldWatchlist->btc_liquidity_bought;
        $watchlist->btc_liquidity_sold_diff = $suggestion->btc_liquidity_sold - $this->oldWatchlist->btc_liquidity_sold;
        $watchlist->impact_1hr_change_score_diff = $suggestion->impact_1hr_change_score - $this->oldWatchlist->impact_1hr_change_score;
        $watchlist->market_cap_score_diff = $suggestion->market_cap_score - $this->oldWatchlist->market_cap_score;
        $watchlist->overall_score_diff = $suggestion->overall_score - $this->oldWatchlist->overall_score;
        $watchlist->market_cap_diff = $suggestion->market_cap - $this->oldWatchlist->market_cap;

        $suggestionHistory = SuggestionHistory::where('coin', $watchlist->coin)->where('exchange', $watchlist->exchange)->orderBy('created_at', 'DESC')->limit($this->rules->number_of_intervals)->get();

        $watchlist->market_cap_ups = $suggestionHistory->where('market_cap_change_up', '=', 1)->count();
        $watchlist->market_cap_downs = $suggestionHistory->where('market_cap_change_down', '=', 1)->count();
        $watchlist->gap_ups = $suggestionHistory->where('gap_change_up', '=', 1)->count();
        $watchlist->gap_downs = $suggestionHistory->where('gap_change_down', '=', 1)->count();
        $watchlist->cpp_ups = $suggestionHistory->where('cpp_change_up', '=', 1)->count();
        $watchlist->cpp_downs = $suggestionHistory->where('cpp_change_down', '=', 1)->count();
        $watchlist->prr_ups = $suggestionHistory->where('prr_change_up', '=', 1)->count();
        $watchlist->prr_downs = $suggestionHistory->where('prr_change_down', '=', 1)->count();
        $watchlist->liquidity_ups = $suggestionHistory->where('liquidity_change_up', '=', 1)->count();
        $watchlist->liquidity_downs = $suggestionHistory->where('liquidity_change_down', '=', 1)->count();


        $watchlist->save();
        $watchlist->load('watchlist_history');

        return $watchlist;
    }

//
//    private function populateBuyUpsDowns($watchlist)
//    {
//
//        $gap_ups = $gap_downs = $cpp_ups = $cpp_downs = $prr_ups = $prr_downs = $liquidity_ups = $liquidity_downs = $market_cap_ups = $market_cap_downs = 0
//
//        $comparison_gap = $comparison_cpp = $comparison_prr = $comparison_liquidity = $comparison_market_cap = null;
//        $started = false;
//        foreach ($watchlist->watchlist_history as $history) {
//            if (!$started) {
//                $started = true;
//
//            }
//        }
//
//        return $watchlist;
//    }

    private function sendSoldText($watchlist, $user, $matchDate)
    {
        $billingService = new BillingService();
        if ($billingService->hasSmsRemaining($user->id)) {
            $twilioHelper = new TwilioHelper();
            $message = "The CMB just made a sale of " . ($watchlist->coin_relation ? $watchlist->coin_relation->symbol : $watchlist->coin) . " on " . $matchDate;
            $sent = $twilioHelper->sendText($user->phone, $message);
            if ($sent) {
                $billingService->emailSent($user->id);
            }
            return $sent;
        }
        return true;
    }

    private function sendText($rulesMet, $user, $matchDate)
    {
        $billingService = new BillingService();
        if ($billingService->hasSmsRemaining($user->id)) {
            $twilioHelper = new TwilioHelper();
            $rules = $this->friendlyNameRules($rulesMet);
            $message = "The " . $rules . " was matched with " . $matchDate;
            $sent = $twilioHelper->sendText($user->phone, $message);
            if ($sent) {
                $billingService->smsSent($user->id);
            }
            return $sent;
        }
        return true;
    }

    private function sendSoldEmail($watchlist, $user, $matchDate, $trade)
    {

        $billingService = new BillingService();
        if ($billingService->hasEmailsRemaining($user->id)) {
            $billingService->emailSent($user->id);
            return EmailHelper::SendSellWatchlistEmail($watchlist, $user, $matchDate, $trade);
        }
        return true;
    }

    private function sendEmail($rulesMet, $watchlist, $user, $matchDate)
    {
        $billingService = new BillingService();
        if ($billingService->hasEmailsRemaining($user->id)) {
            $billingService->emailSent($user->id);
            return EmailHelper::SendBuyWatchlistEmail($rulesMet, $watchlist, $user, $matchDate);

        }
        return true;
    }

    private function executeSell()
    {
        if ($this->watchlist->exchange_account_id && !is_null($this->watchlist->exchange_account_id)) {
            $exchangeAccount = ExchangeAccount::where('id', $this->watchlist->exchange_account_id)->where('user_id', $this->watchlist->user_id)->first();
        } else {
            $exchangeAccount = ExchangeAccount::where('exchange_id', $this->watchlist->exchange)->where('user_id', $this->watchlist->user_id)->first();
        }

        $originTrade = Trade::withTrashed()->find($this->watchlist->trade_id);
        if (!$originTrade)
            return false;

        try {
            $trade = $this->tradeService->sell(
                $originTrade,
                Coin::findBySymbol($this->watchlist->base_coin_id),
                Coin::findBySymbol($this->watchlist->coin),
                $exchangeAccount,
                (double)$originTrade->quantity,
                null,
                'test' //$this->watchlist->is_test ? 'test' : null
            );

            return $trade;
        } catch (TradingBotResponseException $e) {
            Log::warning("Trading bot response error occurred in watchlist sell", [
                'error' => $e->getMessage(),
                'watchlist' => $this->watchlist->id
            ]);
        } catch (InvalidOrMissingDataException $e) {
            Log::warning("Invalid data error occurred in watchlist sell", [
                'error' => $e->getMessage(),
                'data' => $e->getData(),
                'watchlist' => $this->watchlist->id
            ]);
        }

        return false;
    }

    public function addAutoSell($trade)
    {
        if (!$this->user->smart_sell_enabled)
            return false;


        $watchlist = new Watchlist();
        $watchlist->type = 'sell';
        $watchlist->is_test = $trade->is_test;
        $watchlist->user_id = $this->user->id;
        $watchlist->exchange = $trade->exchange_id;
        $watchlist->exchange_account_id = $trade->exchange_account_id;
        $watchlist->coin = $trade->target_coin_id;
        $watchlist->price_per_unit = $trade->price_per_unit;
        $watchlist->follow = $trade->order_type == Trade::STATUS_BOUGHT;
        $watchlist->trade_id = $trade->id;
        $watchlist->sms = $this->user->exit_notified_by_sms ?: false;
        $watchlist->email = $this->user->exit_notified_by_email ?: false;
        $watchlist->execute_sell = true;
        $watchlist->created_at = Carbon::now();
        $watchlist->smart_sell_interval = $this->user->smart_sell_interval;
        $watchlist->save();

        $rules = new WatchlistRule();
        $rules->watchlist_id = $watchlist->id;
        $rules->sell_target = $trade->target_percent;
        $rules->smart_sell_drops = $this->user->smart_sell_drops;
        $rules->save();
        $watchlist->load('rule');

        return $watchlist;
    }

    private function executeBuy()
    {
        $quantity = $this->rules->buy_amount_btc / $this->watchlist->cpp;

        if ($this->watchlist->exchange_account_id && !is_null($this->watchlist->exchange_account_id)) {
            $exchangeAccount = ExchangeAccount::where('id', $this->watchlist->exchange_account_id)->where('user_id', $this->watchlist->user_id)->first();
        } else {
            $exchangeAccount = ExchangeAccount::where('exchange_id', $this->watchlist->exchange)->where('user_id', $this->watchlist->user_id)->first();
        }
        if (!$exchangeAccount)
            return false;

        try {
            $trade = $this->tradeService->buy(
                Coin::findBySymbol($this->watchlist->base_coin_id),
                Coin::findBySymbol($this->watchlist->coin),
                $exchangeAccount,
                (double)$quantity,
                null,
                $this->watchlist->is_test ? 'test' : null
            );

            //$this->addAutoSell($trade);

            return true;
        } catch (TradingBotResponseException $e) {
            Log::warning("Trading bot response error occurred in watchlist buy", [
                'error' => $e->getMessage(),
                'watchlist' => $this->watchlist->id
            ]);
        } catch (InvalidOrMissingDataException $e) {
            Log::warning("Invalid data error occurred in watchlist buy", [
                'error' => $e->getMessage(),
                'data' => $e->getData(),
                'watchlist' => $this->watchlist->id
            ]);
        }
        return false;
    }

    private function friendlyNameRules($rulesMet)
    {
        $names = [];
        foreach ($rulesMet as $k => $v) {
            if ($v) {
                $names[] = $this->friendlyNames[$k];
            }
        }

        return implode('/', $names);
    }

    private function meetSellRules()
    {
        if ($this->watchlist->execute_sell && $this->rules->sell_target) {
            $smartSell = $this->checkSmartSell($this->history, $this->watchlist->smart_sell_interval, $this->rules->sell_target, $this->rules->smart_sell_drops);

            return $smartSell;
        }

        return false;
    }

    private function meetRules()
    {
        $ruleMet = true;
        if (!$this->rules->follow_cpp &&
            !$this->rules->follow_prr &&
            !$this->rules->follow_gap &&
            !$this->rules->follow_market_cap &&
            !$this->rules->follow_liquidity
        )
            return false;

        if ($this->rules->follow_cpp) {
            if ($this->singleRule($this->rules->cpp_rule, 'cpp')) {
                $this->rulesMet['cpp'] = true;
            } else {
                $ruleMet = false;
            }
        }

        if ($this->rules->follow_prr) {
            if ($this->singleRule($this->rules->prr_rule, 'prr')) {
                $this->rulesMet['prr'] = true;
            } else {
                $ruleMet = false;
            }
        }

        if ($this->rules->follow_gap) {
            if ($this->singleRule($this->rules->gap_rule, 'gap')) {
                $this->rulesMet['gap'] = true;
            } else {
                $ruleMet = false;
            }
        }

        if ($this->rules->follow_market_cap) {
            if ($this->singleRule($this->rules->market_cap_rule, 'market_cap')) {
                $this->rulesMet['market_cap'] = true;
            } else {
                $ruleMet = false;
            }
        }

        if ($this->rules->follow_liquidity) {
            if ($this->singleRule($this->rules->liquidity_rule, 'liquidity')) {
                $this->rulesMet['liquidity'] = true;
            } else {
                $ruleMet = false;
            }
        }

        return $ruleMet;
    }

    protected function singleRule($rule, $column)
    {
        $ruleMet = false;

        switch ($rule) {
            case WatchlistRule::GreaterThanNoRule:
                if (($column == 'cpp' || $column == 'market_cap' || $column == 'gap') && $this->oldWatchlist->$column != 0) {
                    $change = (($this->watchlist->$column / $this->oldWatchlist->$column) - 1) * 100;
                    $ruleMet = $change > $this->rules->$column;
                } elseif ($column == 'prr') {
                    $ruleMet = $this->watchlist->$column > $this->rules->$column;
                } elseif ($column == 'liquidity' && $this->watchlist->btc_liquidity_sold != 0) {
                    $liquidity = ($this->watchlist->btc_liquidity_bought / $this->watchlist->btc_liquidity_sold) * 100;
                    $ruleMet = $liquidity > $this->rules->liquidity;
                }
                break;

            case WatchlistRule::LesserThanNoRule:

                if (($column == 'cpp' || $column == 'market_cap' || $column == 'gap') && $this->oldWatchlist->$column != 0) {
                    $change = (($this->watchlist->$column / $this->oldWatchlist->$column) - 1) * 100;
                    $ruleMet = $change > 0 && $change < $this->rules->$column;
                } elseif ($column == 'prr') {
                    $ruleMet = $this->watchlist->$column > 0 && $this->watchlist->$column < $this->rules->$column;
                } elseif ($column == 'liquidity' && $this->watchlist->btc_liquidity_sold != 0) {
                    $liquidity = ($this->watchlist->btc_liquidity_bought / $this->watchlist->btc_liquidity_sold) * 100;
                    $ruleMet = $liquidity < $this->rules->liquidity;
                }

                break;

            case WatchlistRule::GreaterThanProgressively:
                if (($column == 'cpp' || $column == 'market_cap' || $column == 'gap') && $this->oldWatchlist->$column != 0) {
                    $change = (($this->watchlist->$column / $this->oldWatchlist->$column) - 1) * 100;
                    if ($change > $this->rules->$column && $this->checkIfProgressive($this->history, $column, true, $this->rules->number_of_intervals)) {
                        $ruleMet = true;
                    }
                } elseif ($column == 'prr') {
                    $ruleMet = ($this->watchlist->$column > $this->rules->$column) && $this->checkIfProgressive($this->history, $column, true, $this->rules->number_of_intervals);
                } elseif ($column == 'liquidity' && $this->watchlist->btc_liquidity_sold != 0) {
                    $liquidity = ($this->watchlist->btc_liquidity_bought / $this->watchlist->btc_liquidity_sold) * 100;
                    $ruleMet = ($liquidity > $this->rules->liquidity) && $this->checkLiquidityProgressive($this->history, true, $this->rules->number_of_intervals);

                }
                break;

            case WatchlistRule::LesserThanRegressively:
                if (($column == 'cpp' || $column == 'market_cap' || $column == 'gap') && $this->watchlist->$column != 0) {
                    $change = (($this->oldWatchlist->$column / $this->watchlist->$column) - 1) * 100;
                    if ($change < 0 && (abs($change) < $this->rules->$column) && $this->checkIfProgressive($this->history, $column, false, $this->rules->number_of_intervals)) {
                        $ruleMet = true;
                    }
                } elseif ($column == 'prr') {
                    $ruleMet = ($this->watchlist->$column < $this->rules->$column) && $this->checkIfProgressive($this->history, $column, false, $this->rules->number_of_intervals);
                } elseif ($column == 'liquidity' && $this->watchlist->btc_liquidity_sold != 0) {
                    $liquidity = ($this->watchlist->btc_liquidity_bought / $this->watchlist->btc_liquidity_sold) * 100;
                    $ruleMet = ($liquidity < $this->rules->liquidity) && $this->checkLiquidityProgressive($this->history, false, $this->rules->number_of_intervals);

                }
                break;
        }

        return $ruleMet;

    }

    protected function checkIfProgressive($list, $column, $up, $intervals)
    {
        $comparison = null;
        $progressive = true;
        if ($list->count() < $intervals)
            return false;

        foreach ($list as $item) {

            if (is_null($comparison)) {
                $comparison = $item->$column;
                continue;
            }

            if (($up && $item->$column > $comparison) || (!$up && $item->$column < $comparison)) {
                $progressive = true;
            } else {
                return false;
            }
            $comparison = $item->$column;

        }

        return $progressive;
    }

    protected function checkLiquidityProgressive($list, $up, $intervals)
    {
        $comparison = null;
        $progressive = true;
        if ($list->count() < $intervals)
            return false;

        foreach ($list as $item) {
            $value = ($item->btc_liquidity_bought / $item->btc_liquidity_sold) * 100;
            if (is_null($comparison)) {
                $comparison = $value;
                continue;
            }

            if (($up && $value > $comparison) || (!$up && $value < $comparison)) {
                $progressive = true;
            } else {
                return false;
            }
            $comparison = $value;

        }

        return $progressive;
    }

    protected function checkSmartSell($list, $sell_interval, $target, $drops)
    {

        if (($list->count() + 1) < $sell_interval)
            return false;
        if ($this->trade->price_per_unit == 0)
            return false;

        $comparison = null;
        $currentDropsNo = 0;
        foreach ($list as $item) {
            if (!$this->trade)
                return false;

            //((current price / price bought) - 1 ) * 100
            $current = (($item->price_per_unit / $this->trade->price_per_unit) - 1) * 100;

            if (is_null($comparison)) {
                // started with value greater than target
                if ($current < $target)
                    return false;

                $comparison = $current;
                continue;
            }
            //increasing number of drops
            if ($current < $comparison)
                $currentDropsNo++;

            $comparison = $current;
        }

        $last_current_change = (($this->watchlist->price_per_unit / $this->trade->price_per_unit) - 1) * 100;

        //check if last value greater than target and if drops was up to the number of drops defined
        if ($last_current_change >= $target && $currentDropsNo == $drops)
            return true;

        return false;
    }
}