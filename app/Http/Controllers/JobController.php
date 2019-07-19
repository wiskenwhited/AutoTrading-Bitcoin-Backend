<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\TradingBot\Requests\JobRequest;
use App\TradingBot\TradingBot;

class JobController extends ApiController
{
    use HandlesTradingBotResponsesTrait;

    public function show($id, TradingBot $tradingBot)
    {
        $response = $tradingBot->getJob(new JobRequest(['job_id' => $id]));
        list ($data, $status) = $this->processTradingBotResponse($response);

        return response()->json($data, $status);
    }
}