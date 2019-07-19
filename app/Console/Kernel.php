<?php

namespace App\Console;

use App\Console\Commands\FixOpenTradesCommand;
use App\Console\Commands\FixTradingBotRequestsCommand;
use App\Console\Commands\QueueWorkCommand;
use App\Console\Commands\ReplaceScratchCardCodesCommand;
use App\Console\Commands\SuggestionHistoryDeleteScheduler;
use App\Console\Commands\SuggestionHistoryScheduler;
use App\Console\Commands\UpdateLocalCoinData;
use App\Console\Commands\UpdateLocalCurrencyData;
use App\Console\Commands\UpdateRoundsCommand;
use App\Console\Commands\UpdateTradesCommand;
use App\Console\Commands\WatchlistBuyScheduler;
use App\Console\Commands\WatchlistSellScheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Concerns\ReplacesAttributes;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        UpdateLocalCoinData::class,
        UpdateLocalCurrencyData::class,
        UpdateTradesCommand::class,
        WatchlistBuyScheduler::class,
        WatchlistSellScheduler::class,
        FixTradingBotRequestsCommand::class,
        SuggestionHistoryScheduler::class,
        SuggestionHistoryDeleteScheduler::class,
        FixOpenTradesCommand::class,
        UpdateRoundsCommand::class,
        QueueWorkCommand::class,
        ReplaceScratchCardCodesCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

//        $schedule->command(QueueWorkCommand::class);

        $schedule->command(UpdateRoundsCommand::class)
            ->everyMinute()
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateRoundsCommand::class);
            });
        $schedule->command(SuggestionHistoryScheduler::class)->everyMinute();
        $schedule->command(SuggestionHistoryDeleteScheduler::class)->everyTenMinutes();

//        $schedule->command(WatchlistBuyScheduler::class)
//            ->everyMinute();

        //Disabled temporary
//        $schedule->command(WatchlistSellScheduler::class)
//            ->everyMinute();


        $schedule->command(UpdateLocalCurrencyData::class)
            ->dailyAt('10:30')
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateLocalCurrencyData::class);
            });
        $schedule->command(UpdateLocalCoinData::class)
            ->everyMinute()
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateLocalCoinData::class);
            });

    }
}
