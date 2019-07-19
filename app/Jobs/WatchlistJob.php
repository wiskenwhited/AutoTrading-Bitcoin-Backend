<?php

namespace App\Jobs;


use App\Models\Watchlist;
use App\Services\WatchListService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WatchlistJob extends Job
{
    private $watchlist;
    private $type;

    public function __construct(Watchlist $watchlist, $type)
    {
        $this->watchlist = $watchlist;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WatchListService $watchListService)
    {
        if ($this->type == 'buy')
            $watchListService->handleWatchlistProccess($this->watchlist);
        elseif ($this->type == 'sell') {
            try {

                $watchListService->handleSellWatchlistProccess($this->watchlist);
                echo " --- \n";
            } catch (\Exception $exception) {
                echo $exception->getMessage() . "\n";
            }
        }
    }
}