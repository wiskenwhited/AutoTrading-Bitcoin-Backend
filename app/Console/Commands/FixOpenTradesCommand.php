<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Models\TradingBotRequest;
use App\Services\TradeService;
use App\TradingBot\JobProcessor;
use Illuminate\Console\Command;

class FixOpenTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:open-trades {--last=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix open trades command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(TradeService $tradeService)
    {
        $query = Trade::whereIn('status', [Trade::STATUS_BUY_ORDER, Trade::STATUS_SELL_ORDER])
            ->where('remaining_quantity', '<>', 0)
            ->whereNotNull('trading_bot_request_id');
        if ($this->option('last')) {
            $query->orderBy('id', 'desc');
            $query->limit($this->option('last'));
        }
        $query->get()
            ->each(function ($trade) use ($tradeService) {
                $this->info("Updating trade $trade->id, trading bot request $trade->trading_bot_request_id");
                $tradeService->updateActiveTrade($trade, $trade->trading_bot_request_id);
            });
    }
}