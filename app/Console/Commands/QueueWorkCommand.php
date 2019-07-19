<?php

namespace App\Console\Commands;

use App\Jobs\UpdateTradeOrderJob;
use App\Models\Trade;
use App\Models\TradingBotRequest;
use App\Services\TradeService;
use App\TradingBot\JobProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class QueueWorkCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:queue';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->executeUnderLock(function () {
            if (App::environment('staging'))
                Artisan::call('queue:work --queue=https://sqs.us-east-2.amazonaws.com/699609353526/LaravelQuickiesStage');
            if (App::environment('production'))
                Artisan::call('queue:work --queue=https://sqs.us-east-2.amazonaws.com/699609353526/LaravelQuickies');
        });


    }
}