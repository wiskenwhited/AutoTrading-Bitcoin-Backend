<?php

namespace App\Console\Commands;

use App\Models\ScratchCode;
use App\Models\Suggestion;
use App\Models\TradingBotJob;
use App\Models\User;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\SuggestionsRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReplaceScratchCardCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scratchcard:fix';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix scratchcard codes';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $codes = ScratchCode::where('type', 'scratch_card')->get();
        $bar = $this->output->createProgressBar($codes->count());
        foreach ($codes as $code){
            $c = substr($code->code, 0, -1);
            $code->code = $c."0";
            $code->save();
            $bar->advance();
        }
        $bar->finish();

    }
}