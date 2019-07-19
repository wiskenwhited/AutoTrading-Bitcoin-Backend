<?php

namespace App\Console\Commands;

use App\Models\Suggestion;
use App\Models\TradingBotJob;
use App\Models\User;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\SuggestionsRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignDevRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:dev';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign dev roles to the users';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $emails = [
            'damir.sheremet@gmail.com',
            'damir.seremet@toptal.com',
            'shaojiang@toptal.com',
            'caishaojiang@gmail.com',
            'caishaojiang@126.com',
            'radu.balaban@toptal.com',
            'martin.vrkljan@toptal.com',
            'martin.panevski38@gmail.com',
            'maz.avision@gmail.com',
            'ashwin@toptal.com',
            'chaudhari.manas@gmail.com',
            'manas@toptal.com'
        ];

        User::whereIn('email', $emails)->update(['is_dev' => true]);
    }
}