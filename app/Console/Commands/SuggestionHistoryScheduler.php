<?php

namespace App\Console\Commands;

use App\Models\Suggestion;
use App\Models\SuggestionHistory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SuggestionHistoryScheduler extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suggestion:history:update';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save suggestion data for every minute';

    public function __construct()
    {
        parent::__construct();
    }

    private function execution()
    {
        $suggestions = Suggestion::get();
        $suggestion_history_date = SuggestionHistory::orderBy('created_at', 'DESC')->first();
        $suggestion_history = SuggestionHistory::where('created_at', '>', $suggestion_history_date->created_at->subMinute(4))->get();

        $progress = $this->output->createProgressBar($suggestions->count());
        foreach ($suggestions as $suggestion) {

            $last_suggestion = $suggestion_history->where('exchange', '=', $suggestion->exchange)->where('coin', '=', $suggestion->coin)->where('base', '=', $suggestion->base)->last();

            $new_suggestion = new SuggestionHistory();
            $new_suggestion->exchange = $suggestion->exchange;
            $new_suggestion->coin = $suggestion->coin;
            $new_suggestion->base = $suggestion->base;
            $new_suggestion->target = $suggestion->target;
            $new_suggestion->exchange_trend = $suggestion->exchange_trend;
            $new_suggestion->btc_impact = $suggestion->btc_impact;
            $new_suggestion->impact_1hr = $suggestion->impact_1hr;
            $new_suggestion->gap = $suggestion->gap;
            $new_suggestion->cpp = $suggestion->cpp;
            $new_suggestion->prr = $suggestion->prr;
            $new_suggestion->btc_liquidity_bought = $suggestion->btc_liquidity_bought;
            $new_suggestion->btc_liquidity_sold = $suggestion->btc_liquidity_sold;
            $new_suggestion->liquidity = $suggestion->btc_liquidity_sold == 0 ? 0 : ($suggestion->btc_liquidity_bought / $suggestion->btc_liquidity_sold) * 100;
            $new_suggestion->lowest_ask = $suggestion->lowest_ask;
            $new_suggestion->highest_bid = $suggestion->highest_bid;
            $new_suggestion->market_cap = $suggestion->market_cap;
            $new_suggestion->target_score = $suggestion->target_score;
            $new_suggestion->exchange_trend_score = $suggestion->exchange_trend_score;
            $new_suggestion->impact_1hr_change_score = $suggestion->impact_1hr_change_score;
            $new_suggestion->btc_impact_score = $suggestion->btc_impact_score;
            $new_suggestion->btc_liquidity_score = $suggestion->btc_liquidity_score;
            $new_suggestion->market_cap_score = $suggestion->market_cap_score;
            $new_suggestion->overall_score = $suggestion->overall_score;

            $new_suggestion->target_change_up = $last_suggestion && ($last_suggestion->target < $suggestion->target);
            $new_suggestion->exchange_trend_change_up = $last_suggestion && ($last_suggestion->exchange_trend < $suggestion->exchange_trend);
            $new_suggestion->btc_impact_change_up = $last_suggestion && ($last_suggestion->btc_impact < $suggestion->btc_impact);
            $new_suggestion->impact_1hr_change_up = $last_suggestion && ($last_suggestion->impact_1hr < $suggestion->impact_1hr);
            $new_suggestion->gap_change_up = $last_suggestion && ($last_suggestion->gap < $suggestion->gap);
            $new_suggestion->cpp_change_up = $last_suggestion && ($last_suggestion->cpp < $suggestion->cpp);
            $new_suggestion->prr_change_up = $last_suggestion && ($last_suggestion->prr < $suggestion->prr);
            $new_suggestion->btc_liquidity_bought_change_up = $last_suggestion && ($last_suggestion->btc_liquidity_bought < $suggestion->btc_liquidity_bought);
            $new_suggestion->btc_liquidity_sold_change_up = $last_suggestion && ($last_suggestion->btc_liquidity_sold < $suggestion->btc_liquidity_sold);
            $new_suggestion->liquidity_change_up = $last_suggestion && ($last_suggestion->liquidity < $new_suggestion->liquidity);
            $new_suggestion->lowest_ask_change_up = $last_suggestion && ($last_suggestion->lowest_ask < $suggestion->lowest_ask);
            $new_suggestion->highest_bid_change_up = $last_suggestion && ($last_suggestion->highest_bid < $suggestion->highest_bid);
            $new_suggestion->market_cap_change_up = $last_suggestion && ($last_suggestion->market_cap < $suggestion->market_cap);
            $new_suggestion->target_score_change_up = $last_suggestion && ($last_suggestion->target_score < $suggestion->target_score);
            $new_suggestion->exchange_trend_score_change_up = $last_suggestion && ($last_suggestion->exchange_trend_score < $suggestion->exchange_trend_score);
            $new_suggestion->impact_1hr_change_score_change_up = $last_suggestion && ($last_suggestion->impact_1hr_change_score < $suggestion->impact_1hr_change_score);
            $new_suggestion->btc_impact_score_change_up = $last_suggestion && ($last_suggestion->btc_impact_score < $suggestion->btc_impact_score);
            $new_suggestion->btc_liquidity_score_change_up = $last_suggestion && ($last_suggestion->btc_liquidity_score < $suggestion->btc_liquidity_score);
            $new_suggestion->market_cap_score_change_up = $last_suggestion && ($last_suggestion->market_cap_score < $suggestion->market_cap_score);
            $new_suggestion->overall_score_change_up = $last_suggestion && ($last_suggestion->overall_score < $suggestion->overall_score);

            $new_suggestion->target_change_down = $last_suggestion && ($last_suggestion->target > $suggestion->target);
            $new_suggestion->exchange_trend_change_down = $last_suggestion && ($last_suggestion->exchange_trend > $suggestion->exchange_trend);
            $new_suggestion->btc_impact_change_down = $last_suggestion && ($last_suggestion->btc_impact > $suggestion->btc_impact);
            $new_suggestion->impact_1hr_change_down = $last_suggestion && ($last_suggestion->impact_1hr > $suggestion->impact_1hr);
            $new_suggestion->gap_change_down = $last_suggestion && ($last_suggestion->gap > $suggestion->gap);
            $new_suggestion->cpp_change_down = $last_suggestion && ($last_suggestion->cpp > $suggestion->cpp);
            $new_suggestion->prr_change_down = $last_suggestion && ($last_suggestion->prr > $suggestion->prr);
            $new_suggestion->btc_liquidity_bought_change_down = $last_suggestion && ($last_suggestion->btc_liquidity_bought > $suggestion->btc_liquidity_bought);
            $new_suggestion->btc_liquidity_sold_change_down = $last_suggestion && ($last_suggestion->btc_liquidity_sold > $suggestion->btc_liquidity_sold);
            $new_suggestion->liquidity_change_down = $last_suggestion && ($last_suggestion->liquidity > $new_suggestion->liquidity);

            $new_suggestion->lowest_ask_change_down = $last_suggestion && ($last_suggestion->lowest_ask > $suggestion->lowest_ask);
            $new_suggestion->highest_bid_change_down = $last_suggestion && ($last_suggestion->highest_bid > $suggestion->highest_bid);
            $new_suggestion->market_cap_change_down = $last_suggestion && ($last_suggestion->market_cap > $suggestion->market_cap);
            $new_suggestion->target_score_change_down = $last_suggestion && ($last_suggestion->target_score > $suggestion->target_score);
            $new_suggestion->exchange_trend_score_change_down = $last_suggestion && ($last_suggestion->exchange_trend_score > $suggestion->exchange_trend_score);
            $new_suggestion->impact_1hr_change_score_change_down = $last_suggestion && ($last_suggestion->impact_1hr_change_score > $suggestion->impact_1hr_change_score);
            $new_suggestion->btc_impact_score_change_down = $last_suggestion && ($last_suggestion->btc_impact_score > $suggestion->btc_impact_score);
            $new_suggestion->btc_liquidity_score_change_down = $last_suggestion && ($last_suggestion->btc_liquidity_score > $suggestion->btc_liquidity_score);
            $new_suggestion->market_cap_score_change_down = $last_suggestion && ($last_suggestion->market_cap_score > $suggestion->market_cap_score);
            $new_suggestion->overall_score_change_down = $last_suggestion && ($last_suggestion->overall_score > $suggestion->overall_score);


            $new_suggestion->save();
            $progress->advance();
        }

        $progress->finish();
    }

    public function handle()
    {
        $this->executeUnderLock(function () {
            $this->execution();
        });
    }
}
