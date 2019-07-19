<?php

namespace App\Console\Commands;

use App\TradingBot\JobProcessor;
use Illuminate\Console\Command;

class ProcessTradingBotJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process trading bot jobs';
    /**
     * @var JobProcessor
     */
    private $jobProcessor;

    /**
     * Create a new command instance.
     *
     * @param JobProcessor $jobProcessor
     */
    public function __construct(JobProcessor $jobProcessor)
    {
        parent::__construct();
        $this->jobProcessor = $jobProcessor;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->jobProcessor->processJobs();
    }
}