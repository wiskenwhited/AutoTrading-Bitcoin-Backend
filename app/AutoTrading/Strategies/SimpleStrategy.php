<?php

namespace App\AutoTrading\Strategies;

use App\Models\Coin;
use App\Models\Cycle;
use App\Models\CyclePurchase;
use App\Models\ExchangeAccount;
use App\Models\MeanTradeValue;
use App\Models\Round;
use App\Models\Suggestion;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SimpleStrategy extends AbstractStrategy
{
    public function filterCoinsMatchingRoundCriteria(Round $round, Cycle $cycle, ExchangeAccount $account)
    {
        // ATI Movement progressive
        $coins = $this->getCoinsMatchingAtiMovement(
            'progressive',
            3,
            $account->exchange_id,
            8//$account->auto_entry_maximum_ati
        );
        Log::info('Coins matching ATI movement', [
            'strategy' => 'simple',
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins,
            'maximum_ati' => $account->auto_entry_maximum_ati
        ]);
        $atiCount = count($coins);
        $round->ati_count = $atiCount;
        if (! $atiCount) {
            return $coins;
        }

        $coins = Suggestion::whereIn('coin', $coins)
            ->where('exchange', $account->exchange_id)
            ->where('impact_1hr', '>', 0)
            ->where('prr', '>', 5)
            ->get(['coin', 'btc_liquidity_bought', 'btc_liquidity_sold'])
            /*
            ->filter(function ($suggestion) {
                return $suggestion->btc_liquidity_bought - $suggestion->btc_liquidity_sold > 0;
            })
            */
            ->sortByDesc(function ($suggestion) {
                return $suggestion->btc_liquidity_bought - $suggestion->btc_liquidity_sold;
            })
            ->pluck('coin')
            ->toArray();

        $pdCount = count($coins);
        $round->ati_pd_count = $pdCount;
        Log::info('Coins matching ATI percentage difference', [
            'strategy' => 'simple',
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);

        return $coins;
    }

    public function processPurchases(Round $round, Cycle $cycle, ExchangeAccount $account, array $coins)
    {
        $sugestions = Suggestion::whereIn('coin', $coins)
            ->where('exchange', $account->exchange_id)
            ->get([
                'coin',
                'lowest_ask',
                'highest_bid',
                'mean_buy_time',
                'ati_percentage_difference',
                'btc_liquidity_bought',
                'btc_liquidity_sold'
            ])
            ->keyBy('coin');
        // Keep ordering of coins and add suggestion data
        $coins = array_map(function ($coin) use ($sugestions) {
            $data = $sugestions->get($coin)->toArray();
            $data['liquidity_diff'] = array_get($data, 'btc_liquidity_bought') - array_get($data, 'btc_liquidity_sold');

            return $data;
        }, $coins);

        Log::info('Coins satisfying criteria to be purchased', [
            'strategy' => 'simple',
            'round_id' => $round->id,
            'user_id' => $account->user_id,
            'exchange_id' => $account->exchange_id,
            'coins' => $coins
        ]);

        // TODO Revisit temp solution; do not buy if 5 buy/bought trades
        $count = 0;
        $purchases = $cycle->purchases;
        $purchasesWithBought = $purchases->filter(function ($purchase) {
            $purchase->load('buyTrades');
            $purchase->load('boughtTrades');
            $count = $purchase->buyTrades->filter(function ($trade) {
                return $trade->quantity > 0;
            })->count();
            $count += $purchase->boughtTrades->filter(function ($trade) {
                return $trade->quantity > 0;
            })->count();

            return $count > 0;
        });
        $count = $purchasesWithBought->count();
        $purchases = $purchasesWithBought->keyBy('coin');

        Log::info("TEMP Purchases count", [
            'strategy' => 'simple',
            'purchases_count' => $count
        ]);

        foreach ($coins as $coin) {
            // TODO Revisit temp solution; temp limit
            if ($count >= 3) {
                break;
            }
            if (! $purchases->get($coin['coin'])) {
                $this->purchaseCoin($round, $cycle, $account, $coin);
                $count++;
            }
            $cycle->load('purchases');
            $purchases = $cycle->purchases;
            $purchases = $purchases->keyBy('coin');
        }

        // Cancel all buy trades for coins that are no longer valid candidates
        // TODO Do not use passed in coins
        $purchases = $cycle->purchases()->whereNotIn('coin', array_pluck($coins, 'coin'));
        foreach ($purchases as $purchase) {
            if ($purchase->buyTrades->first()) {
                $this->tradeService->cancel($purchase->buyTrades->first());
            }
        }
    }

    public function processExit(Round $round, ExchangeAccount $account)
    {
        // Before processing EXIT conditions and selling coins we make sure to check and
        // update status of cycle.
        try {
            $currentCycle = $round->current_cycle;
            $cycle = $round->current_cycle;
            $lastUnprocessedCycle = $round->last_unprocessed_cycle;
            $cycleEnded = false;
            Log::info("process EXIT cycle info", [
                'strategy' => 'simple',
                'round_id' => $round->id,
                'current_cycle_id' => object_get($currentCycle, 'id'),
                'last_unprocessed_cycle_id' => object_get($lastUnprocessedCycle, 'id')
            ]);
            if (! $cycle || $cycle->id > $lastUnprocessedCycle->id) {
                // If we moved to a new cycle and previous cycle is not yet processed, it means we
                // are now at the point where a new cycle has started and end-of-cycle actions
                // should be performed for last unprocessed cycle.
                $cycleEnded = true;
                $lastUnprocessedCycle->update([
                    'is_processed' => true
                ]);
                $cycle = $lastUnprocessedCycle;
            }
        } catch (\Exception $e) {
            Log::error("Error during EXIT cycle check", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        Log::info("Processing EXIT", [
            'strategy' => 'simple',
            'cycle' => $cycle->index,
            'cycle_ended' => $cycleEnded
        ]);

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
            $buyTrade = $purchase->buyTrades->first();
            // If we have an active buy order we don't sell yet
            if ($buyTrade) {
                Log::info("EXIT Purchase has active buy trade", [
                    'strategy' => 'simple',
                    'purchase' => $purchase->id,
                    'coin' => $purchase->coin,
                    'trade_id' => $buyTrade->id,
                ]);
                continue;
            }
            $coin = $purchase->coin;
            // TODO Revisit explicit loads
            $purchase->load('boughtTrades');
            $purchase->load('soldTrades');
            foreach ($purchase->boughtTrades as $boughtTrade) {
                // We iterate through trades bought during this purchase and sell it if
                // there already isn't an active sell order for this trade or trade hasn't
                // been sold yet.
                if (Trade::activeByOriginalTrade($boughtTrade)->exists() || $boughtTrade->quantity == 0) {
                    continue;
                }
                $priceBought = $boughtTrade->price_per_unit;
                if (! $priceBought) {
                    Log::error("Price bought is invalid", [
                        'strategy' => 'simple',
                        'round_id' => $round->id,
                        'cycle_id' => $cycle->id,
                        'trade_id' => $boughtTrade->id,
                        'cycle_ended' => $cycleEnded
                    ]);
                }

                $meanTradeValue = $meanTradeValues->get($coin);
                $suggestion = $suggestions->get($coin);
                if (! $meanTradeValue || ! $suggestion) {
                    continue;
                }
                $atiDiff = $meanTradeValue->mean_buy_time - $purchase->ati;
                $price = $suggestion->highest_bid;
                $priceDiff = ($price - $priceBought) / $priceBought * 100;

                Log::info("EXIT Bought trade going through criteria", [
                    'strategy' => 'simple',
                    'purchase' => $purchase->id,
                    'coin' => $boughtTrade->target_coin_id,
                    'trade_id' => $boughtTrade->id,
                    'ati_diff' => $atiDiff,
                    'price_diff' => $priceDiff,
                    'target_percent' => $boughtTrade->target_percent,
                    'cycle_ended' => $cycleEnded
                ]);

                // EXIT Logic checks conditions based on difference in ATI and price and either sells,
                // smart sells or executes logic specific for end of cycle and/or round.
                if (
                    //$atiDiff < 0 && $priceDiff > 0 ||
                    $priceDiff >= $boughtTrade->target_percent// ||
                    //$round->is_over // TODO TEMP not moving to next round && $account->auto_exit_action == 'sell'
                ) {
                    // If ATI is lower and difference in price is positive it means coin price grew and coin is
                    // traded more often, so we sell immediately, or, if coin is not being traded more often and
                    // price difference has surpassed our historic target we sell.
                    $this->sell($boughtTrade, $price, $cycle);
                    Log::info("Sold coin", [
                        'strategy' => 'simple',
                        'round_id' => $round->id,
                        'cycle_id' => $cycle->id,
                        'trade_id' => $boughtTrade->id,
                        'ati_diff' => $atiDiff,
                        'price_diff' => $priceDiff,
                        'price_bought' => $priceBought,
                        'rate' => $price,
                        'cycle_ended' => $cycleEnded
                    ]);
                } elseif ($cycleEnded) {
                    if ($currentCycle) {
                        // If it is end of cycle and criteria was not met to sell, we move the bought trade to next cycle
                        $purchase->update(['cycle_id' => $currentCycle->id]);
                        $boughtTrade->update(['cycle_id' => $currentCycle->id]);
                    }
                    continue;

                    $profit = 0;
                    $purchase->soldTrades->each(function ($trade) use (&$profit) {
                        $profit += ($trade->price_per_unit - $trade->originalTrade->price_per_unit) * $trade->quantity;
                    });
                    if (
                        $priceDiff > 0 ||
                        $profit > 0 && $atiDiff < 0
                    ) {
                        $this->sell($boughtTrade, $price, $cycle);
                    } elseif ($currentCycle) {
                        // If it is end of cycle and criteria was not met to sell, we move the bought trade to next cycle
                        $purchase->update(['cycle_id' => $currentCycle->id]);
                        $boughtTrade->update(['cycle_id' => $currentCycle->id]);
                    }
                }
            }
        }
        // TODO Refine; Update all active trades and move to next cycle
        Log::info("Checking to move trades to next cycle", [
            'strategy' => 'simple',
            'round_id' => $round->id,
            'last_unprocessed_cycle_id' => $lastUnprocessedCycle->id,
            'curent_cycle_id' => object_get($currentCycle, 'id'),
            'cycle_ended' => $cycleEnded
        ]);
        if ($cycleEnded) {
            Log::info("Moving trades to next cycle", [
                'strategy' => 'simple',
                'round_id' => $round->id,
                'last_unprocessed_cycle_id' => $lastUnprocessedCycle->id,
                'curent_cycle_id' => object_get($currentCycle, 'id'),
                'count' => Trade::where('cycle_id', $cycle->id)
                    ->where('quantity', '>', 0)
                    ->whereNotIn('status', [Trade::STATUS_SOLD])
                    ->count()
            ]);
            if ($currentCycle) {
                $coins = Trade::where('cycle_id', $lastUnprocessedCycle->id)
                    ->where('quantity', '>', 0)
                    ->whereNotIn('status', [Trade::STATUS_SOLD])
                    ->get(['target_coin_id'])
                    ->pluck('target_coin_id')
                    ->toArray();
                try {
                    CyclePurchase::whereIn('coin', $coins)
                        ->update(['cycle_id' => $currentCycle->id]);
                } catch (\Exception $e) {
                    Log::error("An error during moving", [
                        'message' => $e->getMessage()
                    ]);
                }
                Trade::where('cycle_id', $lastUnprocessedCycle->id)
                    ->where('quantity', '>', 0)
                    ->whereNotIn('status', [Trade::STATUS_SOLD])
                    ->update(['cycle_id' => $currentCycle->id]);
            }
        }
    }

    /**
     * @param Round $round
     * @param Cycle $cycle
     * @param ExchangeAccount $account
     * @param $coinData
     */
    protected function purchaseCoin(Round $round, Cycle $cycle, ExchangeAccount $account, $coinData)
    {
        $exchange = $account->exchange_id;
        $purchases = $cycle->purchases->keyBy('coin');
        $coin = array_get($coinData, 'coin');
        $purchase = $purchases->get($coin);

        if ($purchase /* && $purchase->buyTrades->count() */) {
            // If there already is a valid/active BUY ORDER trade for a coin we don't purchase.
            return;
        }

        $rate = (double)array_get($coinData, 'lowest_ask');
        if (! $rate) {
            Log::error("No data available to get recommended price for purchase", [
                'strategy' => 'simple',
                'coin' => $coinData
            ]);

            return;
        }

        $target = max(0.35, array_get($coinData, 'ati_percentage_difference'));
        // TODO Revisit temp solution; override on target
        //$target = 0.95;
        $target = 1.75;

        // We check for any existing BOUGHT orders for this cycle and subtract the quantities purchased
        // from what user defined as max BTC to be spent.
        $btcToSpend = (double)$account->auto_entry_position_btc;
        if ($purchase) {
            $purchase->boughtTrades->each(function ($trade) use (&$btcToSpend) {
                $btcToSpend -= $trade->quantity * $trade->price_per_unit;
            });
        }
        Log::info("BTC to spend per coin", [
            'strategy' => 'simple',
            'coin' => $coin,
            'settings' => (double)$account->auto_entry_position_btc,
            'available' => (double)$btcToSpend
        ]);

        try {
            $purchase = $purchase ?: new CyclePurchase();
            $purchase->fill([
                'last_purchased_at' => Carbon::now(),
                'ati' => array_get($coinData, 'mean_buy_time'),
                'cycle_id' => $cycle->id,
                'coin' => $coin
            ]);
            $purchase->save();

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

            Log::info("Purchased coin", [
                'strategy' => 'simple',
                'coin' => $coin,
                'rate' => $rate,
                'quantity' => $quantity,
                'cycle_purchase_id' => $purchase->id,
                'cycle_id' => $cycle->id,
                'trade_id' => $trade->id,
                'target_percent' => $target
            ]);
        } catch (\Exception $e) {
            Log::error("An error occurred while auto purchasing coin", [
                'strategy' => 'simple',
                'coin' => $coin,
                'round_id' => $round->id,
                'cycle_id' => $cycle->id,
                'exchange_account_id' => $account->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}