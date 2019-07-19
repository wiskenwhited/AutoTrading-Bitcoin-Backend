<?php

namespace App\AutoTrading\Strategies;

use App\Models\Coin;
use App\Models\CoinMarketData;
use App\Models\Cycle;
use App\Models\CyclePurchase;
use App\Models\ExchangeAccount;
use App\Models\MeanTradeValue;
use App\Models\Round;
use App\Models\Suggestion;
use App\Models\Trade;
use App\Services\TradeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

abstract class AbstractStrategy implements StrategyInterface
{
    const HIGH_TO_LOW = 'high-to-low';
    const LOW_TO_HIGH = 'low-to-high';

    /**
     * @var TradeService
     */
    protected $tradeService;

    public function __construct(TradeService $tradeService)
    {
        $this->tradeService = $tradeService;
    }

    abstract public function filterCoinsMatchingRoundCriteria(Round $round, Cycle $cycle, ExchangeAccount $account);

    abstract public function processPurchases(Round $round, Cycle $cycle, ExchangeAccount $account, array $coins);

    abstract public function processExit(Round $round, ExchangeAccount $account);

    /**
     * @param Round $round
     * @param Cycle $cycle
     * @param ExchangeAccount $account
     * @param $coin
     * @param $ati
     */
    protected function purchaseCoin(Round $round, Cycle $cycle, ExchangeAccount $account, $coinData)
    {
        $exchange = $account->exchange_id;
        $purchases = $cycle->purchases->keyBy('coin');
        $coin = array_get($coinData, 'coin');
        $ati = array_get($coinData, 'ati');
        $purchase = $purchases->get($coin);

        if ($purchase && $purchases->buy_trade) {
            // If there already is a valid/active BUY ORDER trade for a coin we don't purchase.
            return;
        }

        if ($account->auto_entry_price == 'low') {
            $rate = $this->getLowestAtiPrice($coin, $exchange);
        } else {
            $rate = $this->getLowestAsk($coin, $exchange);
        }

        $timestamp = Carbon::now()
            ->subHours($account->auto_global_age)
            ->getTimestamp();
        $lowestPrice = $this->getLowestPriceFromCoinMarketData($coin, $timestamp, $exchange);
        $highestPrice = $this->getHighestPriceFromCoinMarketData($coin, $timestamp, $exchange);
        $target = ($highestPrice - $lowestPrice) / $lowestPrice * 100;

        // We check for any existing BOUGHT orders for this cycle and subtract the quantities purchased
        // from what user defined as max BTC to be spent.
        $btcToSpend = $account->auto_entry_position_btc;
        $purchase->boughtTrades->each(function ($trade) use (&$btcToSpend) {
            $btcToSpend -= $trade->quantity * $trade->price_per_unit;
        });

        // Place a buy order for coin and use calculated Historic Target as target value for Exit.
        $quantity = $btcToSpend / $rate;
        $targetCoin = Coin::findBySymbol($coin);
        $trade = $this->tradeService->buy(
            Coin::findBySymbol('BTC'),
            $targetCoin,
            $account,
            $quantity,
            $rate
        );
        $cycle = $round->last_unprocessed_cycle;
        $trade->update([
            'target_percent' => $target,
            'cycle_id' => $cycle->id
        ]);

        $purchase = $purchase ?: new CyclePurchase();
        $purchase->update([
            'ati' => $ati,
            'last_purchased_at' => Carbon::now()
        ]);
    }

    /**
     * @param $coin
     * @param $timestamp
     * @param $exchange
     * @return mixed
     */
    protected function getHighestPriceFromCoinMarketData($coin, $timestamp, $exchange)
    {
        return CoinMarketData::where('ts', '>=', $timestamp)
            ->where('coin', $coin)
            ->where('exchange', $exchange)
            ->max('highest_bid');
    }

    /**
     * @param $coin
     * @param $timestamp
     * @param $exchange
     * @return mixed
     */
    protected function getLowestPriceFromCoinMarketData($coin, $timestamp, $exchange)
    {
        return CoinMarketData::where('ts', '>=', $timestamp)
            ->where('coin', $coin)
            ->where('exchange', $exchange)
            ->min('highest_bid');
    }

    /**
     * @param $coin
     * @param $exchange
     * @return double|null
     */
    protected function getLowestAsk($coin, $exchange)
    {
        $suggestion = Suggestion::where('coin', $coin)
            ->where('exchange', $exchange)
            ->first(['lowest_ask']);

        return $suggestion ? $suggestion->lowest_ask : null;
    }

    /**
     * @param $coin
     * @param $exchange
     * @return double|null
     */
    protected function getLowestAtiPrice($coin, $exchange)
    {
        $meanTradeValue = MeanTradeValue::where('coin', $coin)
            ->where('exchange', $exchange)
            ->get(['lowest_price'])
            ->sort(function ($meanTradeValue) {
                return $meanTradeValue->lowest_price;
            })
            ->first();

        return $meanTradeValue ? $meanTradeValue->lowest_price : null;
    }

    public function getCoinsMatchingAtiMovement(
        $movement,
        $age,
        $exchange,
        $maximumAti,
        $coins = []
    ) {
        $filteredCoins = [];
        $logData = [];
        $map = ['old', 'mid', 'new'];
        $values = $this->getMeanTradeValues($exchange, $coins);
        foreach ($values as $coin => $coinValues) {
            $times = [];
            for ($i = 0; $i < min($age, 3); $i++) {
                if ($value = array_get($coinValues, $map[$i])) {
                    $times[] = array_get($value, 'mean_buy_time');
                }
            }
            $newAti = array_get($coinValues, 'new.mean_buy_time', 0);
            $oldAti = array_get($coinValues, 'old.mean_buy_time', 0);
            if ($newAti == 0 || $newAti > $maximumAti) {
                continue;
            }
            if ($this->valuesSatisfyMovement($movement, $times, 'any', self::HIGH_TO_LOW)) {
                $filteredCoins[] = $coin;
                $logData[$coin] = array_get($coinValues, 'old.mean_buy_time', 0) . ' > ' .
                    array_get($coinValues, 'mid.mean_buy_time', 0) . ' > ' .
                    array_get($coinValues, 'new.mean_buy_time', 0);
            }
        }

        Log::info("Filtered ATI coin data", ['coins' => $logData]);

        return $filteredCoins;
    }

    /**
     * Values are expected to be provided ordered so the latest value is first and oldest is last.
     *
     * @param $movement
     * @param $values
     * @param string $sign
     * @param string $progress
     * @return bool
     */
    protected function valuesSatisfyMovement($movement, $values, $sign = 'any', $progress = self::LOW_TO_HIGH)
    {
        $result = false;
        $count = count($values);
        // Progressive movement is matched when first n - 1 values in array are decremental, meaning
        // value increases over time, assuming progress is value rising from lower to higher, as is
        // case with e.g. value of prices. Regressive movement is matched when first n - 1 values in
        // array are decremental, meaning value increases over time, assuming progress is value lowering
        // from high to low, ais is case with e.g. trade intervals, when an increasing trade interval over
        // time is considered regressive.
        if (
            $movement === 'progressive' && $progress == self::LOW_TO_HIGH ||
            $movement === 'regressive' && $progress == self::HIGH_TO_LOW
        ) {
            for ($i = 1; $i < $count; $i++) {
                if ((double)$values[$i] > (double)$values[$i - 1]) {
                    $result = true;
                } else {
                    $result = false;
                    break;
                }
            }
            if ($result) {
                switch ($sign) {
                    case 'positive':
                        $result = $values[$count - 1] <= $values[$count - 2];
                        break;
                    case 'negative':
                        $result = $values[$count - 1] > $values[$count - 2];
                        break;
                }
            }
        } elseif (
            $movement === 'regressive' && $progress == self::LOW_TO_HIGH ||
            $movement === 'progressive' && $progress == self::HIGH_TO_LOW
        ) {
            for ($i = 1; $i < $count; $i++) {
                if ((double)$values[$i] < (double)$values[$i - 1]) {
                    $result = true;
                } else {
                    $result = false;
                    break;
                }
            }
            if ($result) {
                switch ($sign) {
                    case 'positive':
                        $result = $values[$count - 1] >= $values[$count - 2];
                        break;
                    case 'negative':
                        $result = $values[$count - 1] < $values[$count - 2];
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @param $exchange
     * @param array $coins
     * @return array
     */
    protected function getMeanTradeValues($exchange, $coins = [])
    {
        $query = MeanTradeValue::where('exchange', $exchange);
        if ($coins) {
            $query->whereIn('coin', $coins);
        }
        return $query->orderBy('coin', 'asc')
            ->get()
            ->groupBy('coin')
            ->map(function ($coinGroup) { return $coinGroup->keyBy('level'); })
            ->toArray();
    }

    protected function sell(Trade $trade, $rate, Cycle $cycle)
    {
        $sellTrade = $this->tradeService->sell(
            $trade,
            $trade->base,
            $trade->coin,
            $trade->exchangeAccount,
            $trade->quantity,
            $rate
        );
        $sellTrade->update([
            'cycle_id' => $cycle->id
        ]);

        return $sellTrade;
    }
}