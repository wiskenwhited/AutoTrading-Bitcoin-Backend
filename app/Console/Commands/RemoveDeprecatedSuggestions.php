<?php

namespace App\Console\Commands;

use App\Models\Suggestion;
use App\Models\TradingBotJob;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\SuggestionsRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveDeprecatedSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:deprecated:suggestions';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Deprecated suggestions that were last updated an hour ago';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deprecatedTime = Carbon::now()->subMinutes(61);
        $suggestions = Suggestion::where('updated_at', '<=', $deprecatedTime)->get();
        foreach ($suggestions as $suggestion)
            $suggestion->delete();
    }
}