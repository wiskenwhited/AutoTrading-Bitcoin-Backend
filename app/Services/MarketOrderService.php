<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CurrencyRate;
use App\Models\Exchange;
use App\Models\MarketSummary;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MarketOrderService
{
    /**
     * @var Client
     */
    protected $bittrexHttpClient;
    /**
     * @var Client
     */
    protected $bitfinexHttpClient;
    /**
     * @var Log
     */
    protected $log;

    public function __construct(Client $bitfinexHttpClient, Client $bittrexHttpClient, LoggerInterface $log)
    {
        $this->bittrexHttpClient = $bittrexHttpClient;
        $this->bitfinexHttpClient = $bitfinexHttpClient;
        $this->log = $log;
    }

    /**
     * Get Market order value for the coin depending on the exchange
     *
     * @param $exchange
     * @param $coin
     * @param bool $isSell
     * @return Collection|null|static
     */
    public function retrieveMarketOrder($exchange, $coin, $isSell = false)
    {
        $data = null;
        if ($exchange == 'bittrex') {
            $url = config('services.marketorder.bittrex_url');
            $url = str_replace('{COIN}', $coin, $url);
            if ($isSell) {
                $url = str_replace('{TYPE}', "buy", $url);
            } else {
                $url = str_replace('{TYPE}', "sell", $url);
            }

            $body = $this->getBittrexResponse('GET', $url);
            if (! array_get($body, 'result')) {
                return null;
            }
            if ($isSell) {
                $data = array_reduce($body['result'], function ($final, $result) {
                    if (! $final) {
                        return $result;
                    }
                    $amount = $final['Quantity'];
                    if ($result['Rate'] == $final['Rate']) {
                        $amount += $result['Quantity'];
                    }
                    return [
                        'Rate' => $final['Rate'] <= $result['Rate'] ? $result['Rate'] : $final['Rate'],
                        'Quantity' => $final['Rate'] >= $result['Rate'] ? $amount : $result['Quantity']
                    ];
                });
            } else {
                $data = array_reduce($body['result'], function ($final, $result) {
                    if (! $final) {
                        return $result;
                    }
                    $amount = $final['Quantity'];
                    if ($result['Rate'] == $final['Rate']) {
                        $amount += $result['Quantity'];
                    }
                    return [
                        'Rate' => $final['Rate'] >= $result['Rate'] ? $result['Rate'] : $final['Rate'],
                        'Quantity' => $final['Rate'] <= $result['Rate'] ? $amount : $result['Quantity']
                    ];
                });
            }
            $data = [
                'price' => $data['Rate'],
                'amount' => $data['Quantity']
            ];
        } elseif ($exchange == 'bitfinex') {
            $url = config('services.marketorder.bitfinex_url');
            $url = str_replace('{COIN}', $coin, $url);
            if ($isSell)
                $url = str_replace('{TYPE}', "limit_asks=0", $url);
            else
                $url = str_replace('{TYPE}', "limit_bids=0", $url);

            $body = $this->getBitfinexResponse('GET', $url);
            $key = 'asks';
            if($isSell)
                $key = 'bids';
            if (! array_get($body, $key)) {
                return null;
            }
            if ($isSell) {
                $data = array_reduce($body[$key], function ($final, $result) {
                    if (! $final) {
                        return $result;
                    }
                    $amount = $final['amount'];
                    if ($result['price'] == $final['price']) {
                        $amount += $result['amount'];
                    }
                    return [
                        'price' => $final['price'] <= $result['price'] ? $result['price'] : $final['price'],
                        'amount' => $final['price'] >= $result['price'] ? $amount : $result['amount']
                    ];
                });
            } else {
                $data = array_reduce($body[$key], function ($final, $result) {
                    if (! $final) {
                        return $result;
                    }
                    $amount = $final['amount'];
                    if ($result['price'] == $final['price']) {
                        $amount += $result['amount'];
                    }
                    return [
                        'price' => $final['price'] >= $result['price'] ? $result['price'] : $final['price'],
                        'amount' => $final['price'] <= $result['price'] ? $amount : $result['amount']
                    ];
                });
            }
        }

        return new Collection([$data]);
    }

    protected function getBittrexResponse($method, $url)
    {
        try {
            $response = $this->bittrexHttpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->log->error($e);
            throw new HttpException(502);
        }
    }

    protected function getBitfinexResponse($method, $url)
    {
        try {
            $response = $this->bitfinexHttpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->log->error($e);
            throw new HttpException(502);
        }
    }

}