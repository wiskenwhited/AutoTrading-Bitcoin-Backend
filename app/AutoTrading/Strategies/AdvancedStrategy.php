<?php

namespace App\AutoTrading\Strategies;

use App\Models\CoinMarketData;
use App\Models\Cycle;
use App\Models\ExchangeAccount;
use App\Models\MeanTradeValue;
use App\Models\Round;
use App\Models\Suggestion;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdvancedStrategy extends AbstractStrategy
{
    public function filterCoinsMatchingRoundCriteria(Round $round, Cycle $cycle, ExchangeAccount $account)
    {
        // Find coins that match user defined minimum frugality ratio
        $coins = $this->getCoinsMatchingMinimumFr($account->auto_entry_minimum_fr, $account->exchange_id);
        Log::info('Coins matching miminum FR', [
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);
        $minimumFrCount = count($coins);
        $round->minimum_fr_count = $minimumFrCount;
        if (! $minimumFrCount) {
            return $coins;
        }
        // Filter for coins that match user defined price and volume movement
        $coins = $this->getCoinsMatchingPriceAndVolumeMovement(
            $account->auto_entry_price_movement,
            $account->auto_entry_price_sign,
            $account->auto_entry_volume_movement,
            $account->auto_global_age,
            $account->auto_entry_volume_sign,
            $account->exchange_id,
            $coins
        );
        Log::info('Coins matching price and volume movement', [
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);
        $priceVolumeCount = count($coins);
        $round->price_volume_count = $priceVolumeCount;
        if (! $priceVolumeCount) {
            return $coins;
        }

        // Filter for coins that match user defined price and volume movement
        $coins = $this->getCoinsMatchingAtiMovement(
            $account->auto_entry_ati_movement,
            $account->auto_global_age,
            $account->exchange_id,
            $account->auto_entry_maximum_ati,
            $coins
        );
        Log::info('Coins matching ATI movement', [
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);
        $atiCount = count($coins);
        $round->ati_count = $atiCount;
        if (! $atiCount) {
            return $coins;
        }

        // Filter for coins which satisfy desired liquidity variance and minimum PRR
        $coins = $this->getCoinsMatchingLiquidityVarianceAndMinimumPrr(
            $account->auto_entry_liquidity_variance,
            $account->auto_entry_minimum_prr,
            $account->exchange_id,
            $coins
        );
        Log::info('Coins matching liquidity variance and minimum PRR', [
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);
        $limitersCount = count($coins);
        $round->limiters_count = $limitersCount;

        return $coins;
    }

    public function processPurchases(Round $round, Cycle $cycle, ExchangeAccount $account, array $coins)
    {
        $this->processRoundHoldTimesAndPurchases($round, $cycle, $account, $coins);
    }

    /**
     * @param $liquidityVariance
     * @param $minimumPrr
     * @param $exchange
     * @param $coins
     * @return array
     */
    protected function getCoinsMatchingLiquidityVarianceAndMinimumPrr(
        $liquidityVariance,
        $minimumPrr,
        $exchange,
        $coins
    ) {
        return Suggestion::where('exchange', $exchange)
            ->whereIn('coin', $coins)
            ->where('prr', '>=', $minimumPrr)
            ->whereRaw("(num_buys - num_sells) / num_buys * 100 >= ?" [$liquidityVariance])
            ->get(['coin'])
            ->pluck('coin')
            ->toArray();
    }

    /**
     * @param $frugalityRatio
     * @param $exchange
     * @return array
     */
    protected function getCoinsMatchingMinimumFr($frugalityRatio, $exchange)
    {
        return Suggestion::where('overall_score', '>=', $frugalityRatio)
            ->where('exchange', $exchange)
            ->get(['coin'])
            ->pluck('coin')
            ->toArray();
    }

    /**
     * @param $priceMovement
     * @param $priceSign
     * @param $volumeMovement
     * @param $volumeSign
     * @param $age
     * @param $exchange
     * @param $coins
     * @return array
     */
    protected function getCoinsMatchingPriceAndVolumeMovement(
        $priceMovement,
        $priceSign,
        $volumeMovement,
        $volumeSign,
        $age,
        $exchange,
        $coins
    ) {
        $now = Carbon::now();
        $prices = [];
        $volumes = [];
        $filteredCoins = [];
        foreach ($coins as $coin) {
            $prices = [];
            $volumes = [];
            for ($i = 0; $i <= $age; $i++) {
                $timestamp = $now->subHours($i)->getTimestamp();
                $coinData = $this->getCoinMarketData($coin, $timestamp, $exchange);
                if (! $coinData) {
                    break;
                }
                $prices = object_get($coinData, 'highest_bid');
                $volumes = object_get($coinData, 'btc_bought');
            }
            if (
                $this->valuesSatisfyMovement($priceMovement, $prices, $priceSign) &&
                $this->valuesSatisfyMovement($volumeMovement, $volumes, $volumeSign)
            ) {
                $filteredCoins[] = $coin;
            }
        }

        return $filteredCoins;
    }

    /**
     * @param $coin
     * @param $timestamp
     * @param $exchange
     * @return CoinMarketData|null
     */
    protected function getCoinMarketData($coin, $timestamp, $exchange)
    {
        return CoinMarketData::where('ts', '<=', $timestamp)
            ->where('coin', $coin)
            ->where('exchange', $exchange)
            ->orderBy('ts', 'desc')
            ->limit(1)
            ->first(['highest_bid', 'btc_bought']);
    }

    /**
     * @param $coin
     * @param $exchange
     * @param $level
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    protected function getMeanTradeValue($coin, $exchange, $level)
    {
        return MeanTradeValue::where('coin', $coin)
            ->where('exchange', $exchange)
            ->where('level', $level)
            ->first();
    }

    /**
     * @param Round $round
     * @param array $coins Coins that satisfy round criteria
     */
    protected function processRoundHoldTimesAndPurchases(
        Round $round,
        Cycle $cycle,
        ExchangeAccount $account,
        array $coins
    ) {
        // Remove hold times for coins which no longer satisfy criteria
        $holders = $round->holders ?: [];
        foreach ($holders as $coin => &$holdStartTime) {
            if (! in_array($coin, $coins)) {
                unset($holders[$coin]);
            }
        }
        $round->holders = $holders;

        $coinsToPurchase = [];
        // Check and set hold times
        foreach ($coins as $coin) {
            $holdIsOver = $this->checkAndUpdateHoldTimes(
                $round,
                $account->auto_entry_hold_time_granularity,
                $account->auto_entry_hold_time,
                $account->exchange_id,
                $coin
            );
            if ($holdIsOver) {
                $meanValue = $this->getMeanTradeValue($coin, $account->exchange_id, 'new');
                $coinsToPurchase[] = [
                    'coin' => $coin,
                    'ati' => $meanValue ? $meanValue->mean_buy_time : null
                ];
            }
        }
        foreach ($coinsToPurchase as $coin) {
            $this->purchaseCoin($round, $cycle, $account, $coin);
        }
        Log::info('Coins satisfying criteria to be purchased', [
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coinsToPurchase
        ]);
    }

    /**
     * @param Round $round
     * @param $granularity
     * @param $holdTime
     * @param $exchange
     * @param $coin
     * @return bool
     */
    protected function checkAndUpdateHoldTimes(Round $round, $granularity, $holdTime, $exchange, $coin)
    {
        $holdFor = $holdTime;
        if ($granularity === 'hours') {
            $holdFor *= 60;
        }
        $holdStartTimes = $round->holders;
        $holdStartTime = array_get($holdStartTimes, "$exchange.$coin");
        if (! $holdStartTime) {
            $holdStartTimes[$exchange] = $holdStartTimes[$exchange] ?: [];
            $holdStartTimes[$exchange][$coin] = $holdStartTime = Carbon::now()->getTimestamp();
        }
        $holdStartTime = Carbon::createFromTimestampUTC($holdStartTime);
        $round->holders = $holdStartTimes;

        return Carbon::now()->greaterThanOrEqualTo($holdStartTime->addMinutes($holdFor));
    }

    /**
     * @param Round $round
     * @param ExchangeAccount $account
     */
    public function processExit(Round $round, ExchangeAccount $account)
    {
        // Before processing EXIT conditions and selling coins we make sure to check and
        // update status of cycle.
        $cycle = $round->current_cycle;
        $lastUnprocessedCycle = $round->last_unprocessed_cycle;
        $cycleEnded = false;
        if ($cycle->id > $lastUnprocessedCycle->id) {
            // If we moved to a new cycle and previous cycle is not yet processed, it means we
            // are now at the point where a new cycle has started and end-of-cycle actions
            // should be performed for last unprocessed cycle.
            $cycleEnded = true;
            $lastUnprocessedCycle->update([
                'is_processed' => true
            ]);
            // TODO Pass unsold coins to next cycle?
            $cycle = $lastUnprocessedCycle;
        }
        $exchange = $account->exchange_id;
        $meanTradeValues = MeanTradeValue::where('exchange', $exchange)
            ->where('level', 'new')
            ->get(['coin', 'mean_buy_time'])
            ->keyBy('coin');
        $suggestions = Suggestion::where('exchange', $exchange)
            ->get(['coin', 'highest_bid'])
            ->keyBy('coin');
        $purchases = $cycle->purchases;
        foreach ($purchases as $purchase) {
            $buyTrade = $purchase->buy_trade;
            // If we have an active buy order we don't sell yet
            if ($buyTrade) {
                continue;
            }
            $coin = $purchase->coin;
            foreach ($purchase->boughtTrades as $boughtTrade) {
                // We iterate through trades bought during this purchase and sell it if
                // there already isn't an active sell order for this trade or trade hasn't
                // been sold yet.
                if (
                    ! $boughtTrade->is_open ||
                    Trade::activeByOriginalTrade($boughtTrade)->exists()
                ) {
                    continue;
                }
                $priceBought = $boughtTrade->price_per_unit;
                $meanTradeValue = $meanTradeValues->get($coin);
                $suggestion = $suggestions->get($coin);
                if (! $meanTradeValue || ! $suggestion) {
                    continue;
                }
                $atiDiff = $meanTradeValue->mean_buy_time - $purchase->ati;
                $price = $suggestion->highest_bid;
                $priceDiff = ($price - $priceBought) / $priceBought * 100;

                // EXIT Logic checks conditions based on difference in ATI and price and either sells,
                // smart sells or executes logic specific for end of cycle and/or round.
                if (
                    $atiDiff < 0 && $priceDiff > 0 ||
                    $priceDiff >= $buyTrade->target_percent && $atiDiff >= $account->auto_exit_drops
                ) {
                    // If ATI is lower and difference in price is positive it means coin price grew and coin is
                    // traded more often, so we sell immediately, or, if coin is not being traded more often and
                    // price difference has surpassed our historic target we (smart) sell if drop in ATI exceeded
                    // user-configured number of allowed full point drops.
                    $this->sell($boughtTrade, $price, $cycle);
                }
                // Check end of cycle
                if ($cycleEnded) {
                    $profit = 0;
                    $purchase->soldTrades->each(function ($trade) use (&$profit) {
                        $profit += ($trade->price_per_unit - $trade->originalTrade->price_per_unit) * $trade->quantity;
                    });
                    if (
                        $priceDiff > 0 ||
                        $profit > 0 && $atiDiff < 0
                    ) {
                        $this->sell($boughtTrade, $price, $cycle);
                    }
                }
                if ($round->is_over && $account->auto_exit_action == 'sell') {
                    $this->sell($boughtTrade, $price, $cycle);
                }
            }
        }
    }
}