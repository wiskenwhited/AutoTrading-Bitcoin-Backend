<?php

namespace App\Console\Commands;

use App\Jobs\WatchlistJob;
use App\Models\Watchlist;
use App\Services\WatchListService;
use Carbon\Carbon;

class WatchlistBuyScheduler extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watchlist:check:buy';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process watchlist executions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(WatchListService $watchListService)
    {
        $this->executeUnderLock(function () use ($watchListService) {
            $watchlist = Watchlist::where('follow', true)->where('type', 'buy')->whereNotNull('interval')->get();

            foreach ($watchlist as $item) {
                //you can uncomment this for debugging
//            $watchListService->handleWatchlistProccess($item);
//            continue;

                $dispatch = false;
                if (! $item->last_check) {
                    $dispatch = true;
                }
                if (! $dispatch) {
                    $startTime = new \DateTime($item->last_check);
                    $endTime = new \DateTime(Carbon::now());
                    $timeDifference = $startTime->diff($endTime);
                    if ($timeDifference->format('%i') >= $item->interval) {
                        $dispatch = true;
                    }
                }

                if ($dispatch) {
                    dispatch(new WatchlistJob($item, 'buy'));
                    $item->last_check = Carbon::now();
                    $item->save();
                }
            }
        });
    }
}
