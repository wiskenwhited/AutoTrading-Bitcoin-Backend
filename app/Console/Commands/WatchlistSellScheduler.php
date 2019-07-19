<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Jobs\WatchlistJob;
use App\Models\Watchlist;
use App\Services\WatchListService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WatchlistSellScheduler extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watchlist:check:sell';
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
//            $time = microtime(true);

            //checking items that are not in bought yet
            $notBought = Watchlist::where('follow', false)
                ->where('type', 'sell')
                ->where('stop_follow', false)
                ->join('trades', 'watchlist.trade_id', '=', 'trades.id')
                ->join('watchlist_rules', 'watchlist_rules.watchlist_id', '=', 'watchlist.id')
                ->where('trades.order_type', '=', Trade::STATUS_BOUGHT)
                ->where('watchlist_rules.sold', 0)
                ->get();

            foreach ($notBought as $item) {
                $item->follow = true;
                $item->save();
            }

            //checking watchlist
            $watchlist = Watchlist::with('trade')->where('stop_follow', false)->where('follow', true)->where('type', 'sell')->get();

            foreach ($watchlist as $item) {

                if(!$item->trade)
                    continue;

                //you can uncomment this for debugging
//            $watchListService->handleSellWatchlistProccess($item);
//            continue;
//                Log::info("WatchlistJob - measuring", [
//                    'watchlist_id' => $item->id,
//                    'status' => 'dispatching',
//                    'check_time' => $item->last_check,
//                    'now' => Carbon::now()
//                ]);
                dispatch(new WatchlistJob($item, 'sell'));
                $item->last_check = Carbon::now();
                $item->save();
            }

//            $this->info((microtime(true) - $time));
//            dd((microtime(true) - $time) . ' elapsed');
//            dd((microtime(true) - $time) . ' elapsed');
        });
    }
}
