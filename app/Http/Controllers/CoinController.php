<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CoinController extends ApiController
{
    /**
     * @var CurrencyService
     */
    private $currencyService;
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(CurrencyService $currencyService, Auth $auth)
    {
        $this->currencyService = $currencyService;
        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        $coins = $this->currencyService->getCoinsWithLocalCurrencyPrice(
            object_get($this->auth->user(), 'currency'),
            $this->getPaginationData($request),
            $this->getSortingData($request),
            $this->getFilterData($request)
        );

        return response()->json([
            'data' => $coins->toArray(),
            'meta' => $this->getResponseMetadata(
                $request,
                $this->currencyService->getCoinCount()
            )
        ]);
    }

    public function show($id)
    {
        $coin = $this->currencyService->findCoinWithLocalCurrencyPrice(
            object_get($this->auth->user(), 'currency'),
            $id
        );

        return response()->json([
            'data' => $coin->toArray()
        ]);
    }

    public function convert($currencyFrom, $currencyTo)
    {
        try {
            $coin = $this->currencyService->findSingleCurrencyData($currencyFrom, $currencyTo);
        } catch (\Exception $e) {
            Log::error("CoinController.convert", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return response()->json([
            'data' => $coin ? $coin->toArray() : null
        ]);
    }
}