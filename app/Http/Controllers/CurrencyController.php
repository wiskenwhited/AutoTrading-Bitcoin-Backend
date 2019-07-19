<?php

namespace App\Http\Controllers;

use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends ApiController
{
    public function index()
    {
        $currencies = CurrencyRate::get()
            ->pluck('target')
            ->map(function($currency) {
                return ['id' => $currency, 'code' => $currency];
            })
            ->sortBy('id')
            ->values();

        return response()->json($currencies);
    }
}