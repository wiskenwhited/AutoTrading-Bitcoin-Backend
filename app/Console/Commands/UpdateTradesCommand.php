<?php

namespace App\Console\Commands;

use App\Events\ExampleEvent;
use App\Events\TradesUpdatedEvent;
use App\Jobs\MarketSummaryJob;
use App\Jobs\TradesUpdatesJob;
use App\Models\Trade;
use App\Models\TradingBotJob;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\SuggestionsRequest;
use App\TradingBot\TradingBot;
use App\Views\TradeView;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:trades';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run trades broadcast every few seconds';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for ($i = 0; $i < 20; $i++) {
            $seconds = $i * 3;
            dispatch((new TradesUpdatesJob())->delay(Carbon::now()->addSeconds($seconds)));
        }
    }
}