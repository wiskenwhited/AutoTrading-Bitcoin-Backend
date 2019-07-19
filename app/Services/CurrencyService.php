<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CurrencyRate;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CurrencyService
{
    /**
     * @var Client
     */
    protected $coinsHttpClient;
    /**
     * @var Client
     */
    protected $currenciesHttpClient;
    /**
     * @var Log
     */
    protected $log;

    public function __construct(Client $coinsHttpClient, Client $currenciesHttpClient, LoggerInterface $log)
    {
        $this->coinsHttpClient = $coinsHttpClient;
        $this->currenciesHttpClient = $currenciesHttpClient;
        $this->log = $log;
    }

    /**
     * Pulls fresh coin data from external API and updates local Coin models
     * and adds new if needed.
     */
    public function updateLocalCoinData()
    {
        $response = $this->coinsHttpClient->request('GET', 'ticker');
        $coinData = new Collection(json_decode($response->getBody()->getContents(), true));
        $coinData = $coinData->map(function($coin) {
            // SQL Standard doesn't allow column name to start with a number
            $coin['volume_usd_24h'] = $coin['24h_volume_usd'];
            unset($coin['24h_volume_usd']);

            return $coin;
        })->keyBy('id');
        $coins = Coin::get(['id']);
        // Update existing coin data
        $coins->each(function ($coin) use ($coinData) {
            if ($data = $coinData->get($coin->id)) {
                $coin->fill($data);
                $coin->save();
            }
        });
        // Insert new coin data
        $coinData->diffKeys($coins->keyBy('id'))->each(function ($coinData) {
            Coin::create($coinData);
        });
    }

    public function findSingleCurrencyData($currencyFrom, $currencyTo)
    {
        $coin = Coin::whereSymbol($currencyFrom)->first();
        if ($coin) {
            return new Collection([
                $coin->id => $coin->toArray()
            ]);
        }

        $uri = 'ticker/'.$coin->id.'/?convert='.$currencyTo;

        $response = $this->coinsHttpClient->request('GET', $uri);
        $coinData = new Collection(json_decode($response->getBody()->getContents(), true));
        $coinData = $coinData->map(function($coin) {
            // SQL Standard doesn't allow column name to start with a number
            $coin['volume_usd_24h'] = $coin['24h_volume_usd'];
            unset($coin['24h_volume_usd']);

            return $coin;
        })->keyBy('id');

        $data = $coinData->get('bitcoin');
        if(!$data)
            return null;

        // Update existing coin data
        if($coin){
            $coin->fill($data);
            $coin->save();
        }

        return $coinData;
    }

    public function updateLocalCurrencyData()
    {
        $response = $this->currenciesHttpClient->request('GET', 'latest.json');
        $currencyData = json_decode($response->getBody()->getContents(), true);
        if ($base = array_get($currencyData, 'base') !== 'USD') {
            throw new Exception("Expected USD as base currency from API response, got $base.");
        }
        $currencyData = new Collection(array_get($currencyData, 'rates'));
        $currencyRates = CurrencyRate::where('base', 'USD')->get();
        // Update existing currency rates
        $currencyRates->each(function ($currencyRate) use ($currencyData) {
            if ($rate = $currencyData->get($currencyRate->target)) {
                $currencyRate->rate = $rate;
                $currencyRate->save();
            }
        });
        // Insert new currency rates
        $currencyData->diffKeys($currencyRates->keyBy('target'))->each(function ($rate, $target) {
            CurrencyRate::create([
                'base' => 'USD',
                'target' => $target,
                'rate' => $rate
            ]);
        });
    }

    public function getCoinCount()
    {
        return Coin::count();
    }

    public function getCoinsWithLocalCurrencyPrice(
        $currency,
        array $page,
        array $sort,
        array $filter
    ) {
        $rate = $this->findLocalCurrencyRate($currency);

        $query = Coin::limitAndOrderBy($page['limit'], $page['offset'], $sort);
        if ($symbol = array_get($filter, 'symbol')) {
            $query->where('symbol', 'LIKE', "$symbol%");
        }

        return $query->get()
            ->map(function ($coin) use ($rate) {
                $this->setLocalCurrencyOnCoin($coin, $rate);

                return $coin;
            });
    }

    public function findCoinWithLocalCurrencyPrice($currency, $id)
    {
        $rate = $this->findLocalCurrencyRate($currency);
        $coin = Coin::findOrFail($id);
        $this->setLocalCurrencyOnCoin($coin, $rate);

        return $coin;
    }

    protected function findLocalCurrencyRate($currency)
    {
        if (! $currency || $currency === 'USD') {
            return null;
        }

        $rate = CurrencyRate::baseAndTarget('USD', $currency)->first();
        if (! $rate) {
            throw new InvalidArgumentException("Unknown currency provided for local price calculation");
        }

        return $rate;
    }

    protected function setLocalCurrencyOnCoin(Coin $coin, CurrencyRate $rate = null)
    {
        $code = 'USD';
        $price = $coin->price_usd;
        if ($rate) {
            $code = $rate->target;
            $price = (float)$rate->rate * (float)$coin->price_usd;
        }
        $coin->setVirtualField('price_local_currency', $price);
        $coin->setVirtualField('local_currency_code', $code);
    }
}