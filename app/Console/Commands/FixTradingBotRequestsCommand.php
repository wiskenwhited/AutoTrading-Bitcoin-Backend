<?php

namespace App\Console\Commands;

use App\Models\TradingBotRequest;
use App\TradingBot\JobProcessor;
use Illuminate\Console\Command;

class FixTradingBotRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:trading-bot-requests';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix trading bot requests';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $requests = TradingBotRequest::where('request_type', 'cancel')->get();
        $requests->each(function($request) {
            $payload = $request->json_payload;
            if ($orderId = array_get($payload, 'order_id')) {
                $payload['order_uuid'] = $orderId;
                unset($payload['order_id']);
                $request->json_payload = $payload;
                $request->save();
                $this->info("Updating order_uuid for request $request->id");
            }
        });
    }
}