<?php

namespace App\Console\Commands;

use App\AutoTrading\AutoTradingService;
use Illuminate\Console\Command;

class UpdateRoundsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rounds';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to check the status and progress of rounds and cycles';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AutoTradingService $autoTradingService)
    {
        $autoTradingService->processActiveRounds();
    }
}