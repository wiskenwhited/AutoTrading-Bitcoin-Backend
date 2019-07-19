<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class HeartbeatController extends ApiController
{
    public function index()
    {
        return response()->json(config('services.heartbeat'));
    }
}