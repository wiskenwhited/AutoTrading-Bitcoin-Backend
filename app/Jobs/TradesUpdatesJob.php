<?php

namespace App\Jobs;

use App\Events\TradesUpdatedEvent;
use App\Models\Trade;
use App\Models\TradingBotJob;
use App\Models\User;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;
use App\Views\TradeView;

class TradesUpdatesJob extends Job
{
    /**
     * @var
     */
    private $trades;

    public function __construct()
    {
    }

    public function handle()
    {
//        foreach (User::get() as $user) {
        $all_trades = Trade::with('coin', 'user', 'currency_rates')->withMarket()->get();
        $users = array_pluck($all_trades, 'user_id');
        $users = array_unique($users);
        foreach ($users as $user_id) {
            $trades = $all_trades->where('user_id', $user_id);
            $view = new TradeView();
            $trade_list = $view->render($trades);

            event(new TradesUpdatedEvent($trade_list, $user_id));
        }
    }
}