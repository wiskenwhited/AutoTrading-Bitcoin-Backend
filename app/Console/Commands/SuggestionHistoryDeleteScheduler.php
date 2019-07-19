<?php

namespace App\Console\Commands;

use App\Models\Suggestion;
use App\Models\SuggestionHistory;

class SuggestionHistoryDeleteScheduler extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suggestion:history:delete';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete suggestion data every 10 minutes';

    public function __construct()
    {
        parent::__construct();
    }

    private function execution(){
        //Delete old ones -> after 120 they are not needed
        $suggestions = Suggestion::get();
        $progress2 = $this->output->createProgressBar($suggestions->count());
        $idsToDelete = [];
        foreach ($suggestions as $suggestion) {
            $oldSuggestions = SuggestionHistory::select('id')->whereExchange($suggestion->exchange)->whereCoin($suggestion->coin)->whereBase($suggestion->base)->orderBy('created_at', 'DESC')->skip(121)->take(20)->get();
            if ($oldSuggestions->count() > 0) {
                $suggestionIds = array_pluck($oldSuggestions, 'id');
                $idsToDelete[] = $suggestionIds;
            }
            $progress2->advance();
        }
        $idsToDelete = array_flatten($idsToDelete);

        SuggestionHistory::whereIn('id', $idsToDelete)->delete();
        $progress2->finish();
    }

    public function handle()
    {
        $this->executeUnderLock(function () {
            $this->execution();
        });
    }
}
