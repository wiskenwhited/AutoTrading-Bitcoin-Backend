<?php

namespace App\Http\Controllers;

use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyRateController extends ApiController
{
    /**
     * @var CurrencyService
     */
    private $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $query = CurrencyRate::getQuery();
        $total = $query->count();
        $rates = $this->applyPaginationData($request, $query)->get();

        return response()->json([
            'data' => $rates,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {
        $rate = CurrencyRate::findOrFail($id);

        return response()->json($rate);
    }
}