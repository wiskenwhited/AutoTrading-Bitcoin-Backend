<?php

namespace App\Services;

use App\Jobs\UpdateTestTradeJob;
use App\Jobs\UpdateTradeOrderJob;
use App\Models\Coin;
use App\Models\CurrencyRate;
use App\Models\Cycle;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\MarketSummary;
use App\Models\Suggestion;
use App\Models\Trade;
use App\Models\User;
use App\Services\Exceptions\InvalidOrMissingDataException;
use App\Services\Exceptions\TradingBotResponseException;
use App\TradingBot\Requests\BalancesRequest;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\Requests\CancelRequest;
use App\TradingBot\Requests\SellRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class TradeService
{
    const TEST_MODE = 'test';
    const ACTIVE_MODE = 'active';
    const JOB_DELAY_SECONDS = 2;
    const TEST_JOB_DELAY_SECONDS = 5;

    /**
     * @var TradingBot
     */
    private $tradingBot;

    public function __construct(TradingBot $tradingBot)
    {
        $this->tradingBot = $tradingBot;
    }

    /**
     * @param User $user
     * @param array $page
     * @param array $sort
     * @param array $filters
     * @param null $roundExchangeAccountId
     * @return array
     */
    public function getTradesWithCalculatedFields(
        User $user,
        array $page,
        array $sort,
        array $filters = [],
        $roundExchangeAccountId = null
    )
    {
        $query = Trade::with('coin', 'user.currencyRate', 'exchangeAccount')
            ->where('trades.user_id', $user->id)
            ->where('trades.quantity', '>', 0);

        if ($roundExchangeAccountId) {
            $cycleIds = Cycle::whereHas('round', function ($query) use ($roundExchangeAccountId) {
                $query->active()->where('exchange_account_id', $roundExchangeAccountId);
            })->get()
                ->pluck('id')
                ->toArray();
            $query->whereIn('trades.cycle_id', $cycleIds)->with('cycle');
        } else {
            // TODO Revisit; temporarily show robot trades in mtr
            //$query->whereNull('trades.cycle_id');
        }

        $query->select(
            "trades.*",
            DB::raw("CASE WHEN bought.price_per_unit IS NOT NULL THEN
                bought.price_per_unit
            ELSE
                trades.price_per_unit 
            END AS price_bought"),
            "exchange_accounts.name AS exchange_account_name",
            "s.lowest_ask AS cpp",
            "s.highest_bid AS highest_bid",
            "s.gap AS gap",
            DB::raw("btc.price_usd * cr.rate AS current_btc_price_local_currency"),
            // Calculate BTC and coin growth
            DB::raw("@btc_growth:=GREATEST(0, (btc.price_usd * cr.rate - trades.btc_price_local_currency) / trades.btc_price_local_currency) AS btc_growth"),
            DB::raw("@coin_growth:=CASE WHEN trades.status IN ('" . Trade::STATUS_BOUGHT . "', '" . Trade::STATUS_BUY_ORDER . "') THEN  
                    GREATEST(0, (s.highest_bid - trades.price_per_unit) / trades.price_per_unit)
                ELSE
                    GREATEST(0, (trades.price_per_unit - bought.price_per_unit) / bought.price_per_unit)
                END AS coin_growth"),
            // Calculate profit from BTC movement in local currency
            DB::raw("@btc_profit:=CASE WHEN @btc_growth > 0 THEN
                @btc_growth * trades.price_per_unit * trades.quantity * trades.btc_price_local_currency
            ELSE
                0
            END AS btc_profit_local_currency"),
            // Calcualte profit from alt coin movement in BTC
            DB::raw("@coin_profit:=CASE WHEN @coin_growth > 0 THEN
                CASE WHEN trades.status IN ('" . Trade::STATUS_BOUGHT . "', '" . Trade::STATUS_BUY_ORDER . "') THEN  
                    @coin_growth * trades.price_per_unit * trades.quantity
                ELSE
                    @coin_growth * bought.price_per_unit * trades.quantity
                END
            ELSE
                0
            END AS coin_profit_btc"),
            // Calculate cumulative profit
            DB::raw("CASE WHEN @btc_growth > 0 AND @coin_growth > 0 THEN
                @btc_profit + (@coin_profit * btc.price_usd * cr.rate)
            WHEN @btc_growth > 0 THEN
                @btc_profit
            WHEN @coin_profit > 0 THEN
                @coin_profit * btc.price_usd * cr.rate
            ELSE
                0
            END AS cumulative_profit_local_currency"),
            // TODO Temp keep old profit
            DB::raw("CASE WHEN trades.status = '" . Trade::STATUS_BOUGHT . "' 
                OR trades.status = '" . Trade::STATUS_BUY_ORDER . "' THEN 
                    GREATEST(0, (s.highest_bid - trades.price_per_unit) * 0.9975 / 1.0025 * trades.quantity) 
                ELSE
                    GREATEST(0, (trades.price_per_unit - bought.price_per_unit) * 0.9975 / 1.0025 * trades.quantity)
                END AS profit"),
            // Calculate shrink differential
            DB::raw("@sd:=CASE WHEN trades.starting_shrink_differential > 0 THEN
                (s.gap - s.highest_bid) / trades.starting_shrink_differential * 100
                ELSE
                    null
                END AS shrink_differential"),
            // Calculate target price
            DB::raw("@tp:=trades.price_per_unit * (1 + trades.target_percent/100) AS target_price"),
            // Calculate suggestion
            DB::raw("CASE WHEN trades.status = '" . Trade::STATUS_BOUGHT . "' THEN  
                CASE WHEN trades.target_shrink_differential > 0 
                    AND @sd <= trades.target_shrink_differential THEN
                    'Sell'
                WHEN trades.target_percent > 0 
                    AND @tp > trades.price_per_unit 
                    AND s.gap >= @tp THEN
                    'Sell'
                WHEN trades.target_percent > 0 
                    AND @tp < trades.price_per_unit 
                    AND s.gap <= @tp THEN
                    'Sell'
                ELSE
                    'Hold'
                END
            ELSE 
                null 
            END AS suggestion")
        )
            ->leftJoin(DB::raw('trades AS bought'), function ($join) {
                $join->on('trades.original_trade_id', '=', 'bought.id');
            })
            ->leftJoin(DB::raw('suggestions AS s'), function ($join) {
                $join->on('s.coin', '=', 'trades.target_coin_id');
                $join->on('s.base', '=', 'trades.base_coin_id');
                $join->on('s.exchange', '=', 'trades.exchange_id');
            })
            ->leftJoin(DB::raw('coins AS btc'), function ($join) {
                $join->on('btc.symbol', '=', 'trades.base_coin_id');
            })
            ->leftJoin('exchange_accounts', function ($join) {
                $join->on('exchange_accounts.id', '=', 'trades.exchange_account_id');
            })
            ->leftJoin(DB::raw('currency_rates AS cr'), function ($join) {
                $join->on('cr.target', '=', 'trades.local_currency');
            });

        // Apply filters
        $query = $this->applyFilters($query, $filters, $roundExchangeAccountId);

        // Apply pagination
        if (!is_null($page['limit']) && !is_null($page['offset'])) {
            $query->limit($page['limit'])
                ->offset($page['offset']);
        }

        // Apply sorting
        foreach ($sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        if ($roundExchangeAccountId) {
            //Log::info("Trades SQL", ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        }

        $trades = $query->get();

        $query = Trade::with('exchangeAccount')
            ->where('trades.user_id', $user->id)
            ->where('trades.quantity', '>', 0);
        $query = $this->applyFilters($query, $filters, $roundExchangeAccountId);
        $total = $query->count();

        return [$trades, $total];
    }

    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    protected function applyFilters($query, $filters, $roundExchangeAccountId)
    {
        if ($coin = array_get($filters, 'target_coin_id')) {
            $query->where('trades.target_coin_id', trim($coin));
        }
        if ($exchangeAccountId = array_get($filters, 'exchange_account_id')) {
            $query->where('trades.exchange_account_id', trim($exchangeAccountId));
        }
        if ($exchangeAccountId = array_get($filters, 'exchange_id')) {
            $query->where('trades.exchange_id', trim($exchangeAccountId));
        }
        if ($exchangeAccountName = array_get($filters, 'exchange_account_name')) {
            $query->whereHas('exchangeAccount', function ($query) use ($exchangeAccountName) {
                $query->where('exchange_accounts.name', 'LIKE', "$exchangeAccountName%");
            });
        }
        // We can filter by trade status but we only show SOLD trades if specified by filter
        if ($status = array_get($filters, 'status')) {
            $query->where('trades.status', trim($status));
        } elseif (! $roundExchangeAccountId) {
            // We show sold trades if cycle trades are being fetched
            $query->whereNotIn('trades.status', [Trade::STATUS_SOLD]);
        }
        $query->where('trades.is_test', array_get($filters, 'is_test') == 'test');

        return $query;
    }

    protected function getRateInLocalCurrency($rate, $priceUsd, $localCurrency)
    {
        $localCurrencyRate = $rate * (double)$priceUsd;
        if ($localCurrency != 'USD') {
            $currencyRate = CurrencyRate::baseAndTarget('USD', $localCurrency)->first();
            $localCurrencyRate = $currencyRate->rate * $localCurrencyRate;
        }

        return $localCurrencyRate;
    }

    protected function getBtcPriceInLocalCurrency($priceUsd, $localCurrency)
    {
        $localCurrencyBtcPrice = (double)$priceUsd;
        if ($localCurrency != 'USD') {
            $currencyRate = CurrencyRate::baseAndTarget('USD', $localCurrency)->first();
            $localCurrencyBtcPrice = $currencyRate->rate * $localCurrencyBtcPrice;
        }

        return $localCurrencyBtcPrice;
    }

    /**
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $rate
     * @param double $quantity
     * @param string $mode
     * @return Trade
     */
    public function buy(
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate = null,
        $mode = null
    )
    {
        if (!$mode) {
            $mode = self::ACTIVE_MODE;
        }
        $user = $exchangeAccount->user;
        $localCurrencyRate = $this->getRateInLocalCurrency($rate, $targetCoin->price_usd, $user->currency);
        $localCurrencyBtcPrice = $this->getBtcPriceInLocalCurrency($baseCoin->price_usd, $user->currency);
        if ($mode === self::ACTIVE_MODE) {
            return $this->doBuy(
                $baseCoin,
                $targetCoin,
                $exchangeAccount,
                $quantity,
                $rate,
                $user->currency,
                $localCurrencyRate,
                $localCurrencyBtcPrice
            );
        } elseif ($mode === self::TEST_MODE) {
            return $this->doTestModeBuy(
                $baseCoin,
                $targetCoin,
                $exchangeAccount,
                $quantity,
                $rate,
                $user->currency,
                $localCurrencyRate,
                $localCurrencyBtcPrice
            );
        } else {
            throw new InvalidArgumentException("Invalid mode '$mode' argument provided");
        }
    }

    /**
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $quantity
     * @param double $rate
     * @return Trade
     * @throws InvalidOrMissingDataException
     * @throws TradingBotResponseException
     */
    protected function doBuy(
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate,
        $localCurrency,
        $localCurrencyRate,
        $localCurrencyBtcPrice
    )
    {
        $startingSd = $this->getStartingShrinkDifferential(
            $exchangeAccount->exchange,
            $baseCoin,
            $targetCoin,
            $rate
        );

        $response = $this->tradingBot->buy(new BuyRequest([
            'exchange' => $exchangeAccount->exchange_id,
            'base' => $baseCoin->symbol,
            'strategy' => 'trend',
            'coin' => $targetCoin->symbol,
            'quantity' => (double)$quantity,
            'rate' => (double)$rate,
            'key' => $exchangeAccount->key,
            'secret' => $exchangeAccount->secret
        ]), TradingBot::WAIT);
        $tradingBotRequestId = $response['trading_bot_request_id'];

        if ($error = array_get($response, 'error')) {
            throw new TradingBotResponseException($error);
        }
        if (!array_get($response, 'data.order_uuid')) {
            throw new InvalidOrMissingDataException([
                'order_uuid' => 'Missing order UUID for trade record'
            ]);
        }


        $trade = Trade::create(array_merge(
            array_get($response, 'data'),
            [
                'order_uuid' => array_get($response, 'data.order_uuid'),
                'trading_bot_request_id' => $tradingBotRequestId,
                'exchange_id' => $exchangeAccount->exchange_id,
                'exchange_account_id' => $exchangeAccount->id,
                'base_coin_id' => $baseCoin->symbol,
                'target_coin_id' => $targetCoin->symbol,
                'user_id' => $exchangeAccount->user_id,
                'status' => Trade::STATUS_BUY_ORDER,
                'quantity' => (double)$quantity,
                'price_per_unit' => (double)$rate,
                'starting_shrink_differential' => $startingSd,
                'gap_bought' => $targetCoin->price_btc,
                'local_currency' => $localCurrency,
                'price_per_unit_local_currency' => $localCurrencyRate,
                'btc_price_usd' => (double)$baseCoin->price_usd,
                'target_coin_price_btc' => (double)$targetCoin->price_btc,
                'btc_price_local_currency' => $localCurrencyBtcPrice
            ]
        ));

        dispatch(new UpdateTradeOrderJob($trade, $tradingBotRequestId));

        return $trade;
    }

    /**
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $rate
     * @param double $quantity
     * @return Trade
     */
    protected function doTestModeBuy(
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate,
        $localCurrency,
        $localCurrencyRate,
        $localCurrencyBtcPrice
    )
    {
        $startingSd = $this->getStartingShrinkDifferential(
            $exchangeAccount->exchange,
            $baseCoin,
            $targetCoin,
            $rate
        );
        $trade = Trade::create([
            'order_uuid' => Uuid::uuid4()->toString(),
            'exchange_id' => $exchangeAccount->exchange_id,
            'exchange_account_id' => $exchangeAccount->id,
            'base_coin_id' => $baseCoin->symbol,
            'target_coin_id' => $targetCoin->symbol,
            'user_id' => $exchangeAccount->user_id,
            'status' => Trade::STATUS_BUY_ORDER,
            'gap_bought' => $targetCoin ? $targetCoin->price_btc : null,
            'quantity' => (double)$quantity,
            'price_per_unit' => (double)$rate,
            'starting_shrink_differential' => $startingSd,
            'is_test' => true,
            'local_currency' => $localCurrency,
            'price_per_unit_local_currency' => $localCurrencyRate,
            'btc_price_usd' => (double)$baseCoin->price_usd,
            'btc_price_local_currency' => $localCurrencyBtcPrice
        ]);
        (new UpdateTestTradeJob($trade))->handle();

        return $trade;
    }

    /**
     * @param Trade $originTrade
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $rate
     * @param double $quantity
     * @param string $mode
     * @return Trade
     */
    public function sell(
        Trade $originTrade,
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate = null,
        $mode = null
    )
    {
        if (!$mode) {
            $mode = self::ACTIVE_MODE;
        }
        $user = $exchangeAccount->user;
        $localCurrencyRate = $this->getRateInLocalCurrency($rate, $targetCoin->price_usd, $user->currency);
        $localCurrencyBtcPrice = $this->getBtcPriceInLocalCurrency($baseCoin->price_usd, $user->currency);
        // TODO Validate quantity, must be <= quantity in trade for trade_id
        // TODO Validate origin trade has same target/base coins
        if ($mode === self::ACTIVE_MODE) {
            return $this->doSell(
                $originTrade,
                $baseCoin,
                $targetCoin,
                $exchangeAccount,
                $quantity,
                $rate,
                $user->currency,
                $localCurrencyRate,
                $localCurrencyBtcPrice
            );
        } elseif ($mode === self::TEST_MODE) {
            return $this->doTestModeSell(
                $originTrade,
                $baseCoin,
                $targetCoin,
                $exchangeAccount,
                $quantity,
                $rate,
                $user->currency,
                $localCurrencyRate,
                $localCurrencyBtcPrice
            );
        } else {
            throw new InvalidArgumentException("Invalid mode '$mode' argument provided");
        }
    }

    /**
     * @param Trade $originTrade
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $quantity
     * @param double $rate
     * @return Trade
     * @throws Exception
     * @throws TradingBotResponseException
     */
    protected function doSell(
        Trade $originTrade,
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate,
        $localCurrency,
        $localCurrencyRate,
        $localCurrencyBtcPrice
    )
    {
        $startingSd = $this->getStartingShrinkDifferential(
            $exchangeAccount->exchange,
            $baseCoin,
            $targetCoin,
            $rate
        );
        //try {
        //DB::beginTransaction();

        $originTrade->update([
            'quantity' => $originTrade->quantity - (double)$quantity
        ]);

        $response = $this->tradingBot->sell(new SellRequest([
            'original_trade_id' => $originTrade->id,
            'exchange' => $exchangeAccount->exchange_id,
            'base' => $baseCoin->symbol,
            'strategy' => 'trend',
            'coin' => $targetCoin->symbol,
            'quantity' => (double)$quantity,
            'rate' => (double)$rate,
            'key' => $exchangeAccount->key,
            'secret' => $exchangeAccount->secret
        ]), TradingBot::WAIT);
        $tradingBotRequestId = $response['trading_bot_request_id'];
        if ($error = array_get($response, 'error')) {
            throw new TradingBotResponseException($error);
        }
        if (!array_get($response, 'data.order_uuid')) {
            throw new InvalidOrMissingDataException([
                'order_uuid' => 'Missing order UUID for trade record'
            ]);
        }

        $trade = Trade::create(array_merge(
            array_get($response, 'data'),
            [
                'order_uuid' => array_get($response, 'data.order_uuid'),
                'trading_bot_request_id' => $tradingBotRequestId,
                'original_trade_id' => $originTrade->id,
                'exchange_id' => $exchangeAccount->exchange_id,
                'exchange_account_id' => $exchangeAccount->id,
                'base_coin_id' => $baseCoin->symbol,
                'target_coin_id' => $targetCoin->symbol,
                'user_id' => $exchangeAccount->user_id,
                'status' => Trade::STATUS_SELL_ORDER,
                'quantity' => (double)$quantity,
                'price_per_unit' => (double)$rate,
                'starting_shrink_differential' => $startingSd,
                'gap_bought' => $targetCoin->price_btc,
                'target_percent' => $originTrade->target_percent,
                'target_shrink_differential' => $originTrade->target_shrink_differential,
                'local_currency' => $localCurrency,
                'price_per_unit_local_currency' => $localCurrencyRate,
                'btc_price_usd' => (double)$baseCoin->price_usd,
                'target_coin_price_btc' => (double)$targetCoin->price_btc,
                'btc_price_local_currency' => $localCurrencyBtcPrice
            ]
        ));

        dispatch(new UpdateTradeOrderJob($trade, $tradingBotRequestId));
        //DB::commit();
        //} catch (Exception $e) {
        //DB::rollBack();
        //throw $e;
        //}

        return $trade;
    }

    /**
     * @param Trade $originTrade
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param ExchangeAccount $exchangeAccount
     * @param double $rate
     * @param double $quantity
     * @return Trade
     */
    protected function doTestModeSell(
        Trade $originTrade,
        Coin $baseCoin,
        Coin $targetCoin,
        ExchangeAccount $exchangeAccount,
        $quantity,
        $rate,
        $localCurrency,
        $localCurrencyRate,
        $localCurrencyBtcPrice
    )
    {
        $startingSd = $this->getStartingShrinkDifferential(
            $exchangeAccount->exchange,
            $baseCoin,
            $targetCoin,
            $rate
        );
        $trade = Trade::create(array_merge($originTrade->toArray(), [
            'order_uuid' => Uuid::uuid4()->toString(),
            'original_trade_id' => $originTrade->id,
            'exchange_id' => $exchangeAccount->exchange_id,
            'exchange_account_id' => $exchangeAccount->id,
            'base_coin_id' => $baseCoin->symbol,
            'target_coin_id' => $targetCoin->symbol,
            'user_id' => $exchangeAccount->user_id,
            'status' => Trade::STATUS_SELL_ORDER,
            'gap_bought' => $targetCoin->price_btc,
            'target_percent' => $originTrade->target_percent,
            'target_shrink_differential' => $originTrade->target_shrink_differential,
            'quantity' => (double)$quantity,
            'price_per_unit' => (double)$rate,
            'starting_shrink_differential' => $startingSd,
            'is_test' => true,
            'local_currency' => $localCurrency,
            'price_per_unit_local_currency' => $localCurrencyRate,
            'btc_price_usd' => (double)$baseCoin->price_usd,
            'target_coin_price_btc' => (double)$targetCoin->price_btc,
            'btc_price_local_currency' => $localCurrencyBtcPrice
        ]));
        (new UpdateTestTradeJob($trade))->handle();

        return $trade;
    }

    /**
     * @param Trade $trade
     * @param string $mode
     * @throws InvalidOrMissingDataException
     */
    public function cancel(Trade $trade, $mode = null)
    {
        if (!$mode) {
            $mode = self::ACTIVE_MODE;
        }
        if ($mode === self::ACTIVE_MODE) {
            $this->doCancel($trade);
        } elseif ($mode === self::TEST_MODE) {
            $this->doTestModeCancel($trade);
        } else {
            throw new InvalidArgumentException("Invalid mode '$mode' argument provided");
        }
    }

    /**
     * @param Trade $trade
     * @throws Exception
     * @throws TradingBotResponseException
     */
    protected function doCancel(Trade $trade)
    {
        if (!$trade->order_uuid) {
            throw new InvalidOrMissingDataException([
                'order_uuid' => 'Missing order UUID for trade record'
            ]);
        }

        try {
            //DB::beginTransaction();

            $exchangeAccount = $trade->exchangeAccount;
            $response = $this->tradingBot->cancel(new CancelRequest([
                'order_uuid' => $trade->order_uuid,
                'exchange' => $exchangeAccount->exchange_id,
                'exchange_account_id' => $exchangeAccount->id,
                'key' => $exchangeAccount->key,
                'secret' => $exchangeAccount->secret,
                'user_id' => $exchangeAccount->user_id
            ]), TradingBot::WAIT);
            if ($error = array_get($response, 'error')) {
                throw new TradingBotResponseException($error);
            }

            // We update the active trade order with fresh order data in case there
            // was a change in bought or sold quantity since last trade update
            $this->updateActiveTrade($trade, $response['trading_bot_request_id']);

            if ($originTrade = $trade->originalTrade) {
                $trade->refresh();
                // Unsold coin quantity goes back to origin trade so it can be sold again
                $originTrade->update([
                    'quantity' => $originTrade->quantity + $trade->quantity
                ]);
            }

            $trade->delete();
            //DB::commit();
        } catch (Exception $e) {
            //DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Trade $trade
     * @return Trade
     */
    protected function doTestModeCancel(Trade $trade)
    {
        // TODO Implement method
    }

    /**
     * Updates the trade record by checking response from trading bot.
     *
     * @param Trade $trade
     * @param $tradingBotRequestId
     */
    public function updateActiveTrade(Trade $trade, $tradingBotRequestId)
    {
        Log::info("Updating open trade", [
            'trade_id' => $trade->id,
            'trading_bot_request_id' => $tradingBotRequestId
        ]);
        $response = $this->tradingBot->getTradingBotRequestResponse($tradingBotRequestId);
        $orderIsOpen = array_get($response, 'is_open', true);
        $data = array_get($response, 'data');
        $orderIsCanceled = false;
        if (array_has($data, 'is_cancelled')) {
            $orderIsCanceled = array_get($data, 'is_cancelled');
        } elseif (array_has($data, 'cancel_initiated')) {
            $orderIsCanceled = array_get($data, 'cancel_initiated');
        }
        if ($trade->deleted_at) {
            $orderIsCanceled = true;
        }

        // If response results in an error, we stop
        if (array_get($response, 'error')) {
            Log::error("Trading bot order status request resulted in an error", [
                'trade_id' => $trade->id,
                'response_data' => $response
            ]);
            if ($orderIsOpen) {
                $job = new UpdateTradeOrderJob($trade, $tradingBotRequestId);
                $job->delay(30);
                dispatch($job);
            }

            return;
        }

        if (!$data) {
            Log::error("No data from trading bot received for order status", [
                'trade_id' => $trade->id,
                'response_data' => $response
            ]);
            if ($orderIsOpen) {
                $job = new UpdateTradeOrderJob($trade, $tradingBotRequestId);
                $job->delay(10);
                dispatch($job);
            }

            return;
        }

        $quantityRemaining = array_get($data, 'quantity_remaining', array_get($data, 'remaining_amount'));
        // If there is a change in bought or sold quantity we update trade records
        if (!is_null($quantityRemaining) && (double)$trade->quantity != (double)$quantityRemaining) {
            $this->updateTradeRecords($trade, $data);
        }

        if ($orderIsOpen && !$orderIsCanceled) {
            /*
            Log::info("Dispatching UpdateTradeOrderJob again", [
                'trade_id' => $trade->id,
                'trading_bot_request_id' => $tradingBotRequestId
            ]);
            */
            $job = new UpdateTradeOrderJob($trade, $tradingBotRequestId);
            $delay = $trade->is_test ? self::TEST_JOB_DELAY_SECONDS : self::JOB_DELAY_SECONDS;
            $tradeAgeMinutes = Carbon::now()->diffInMinutes($trade->created_at);
            if ($tradeAgeMinutes >= 1 && $tradeAgeMinutes < 5) {
                $delay = 15;
            } elseif ($tradeAgeMinutes > 5) {
                $delay = 30;
            }
            $job->delay($delay);
            dispatch($job);
        } else {
            //$trade->delete();
        }
    }

    protected function updateTradeRecords($trade, $data)
    {
        Log::info("Updating trade data", [
            'trade_id' => $trade->id,
            'data' => $data
        ]);
        $startQuantity = (float)array_get($data, 'quantity', array_get($data, 'original_amount'));
        $remainingQuantity = (float)array_get($data, 'quantity_remaining', array_get($data, 'remaining_amount'));
        $data['quantity'] = $remainingQuantity;
        // Ensure price_per_unit is never set to 0 if is was positive previously
        $data['price_per_unit'] = array_get($data, 'price_per_unit') ?: $trade->price_per_unit;
        $trade->update($data);

        // We make sure trade data is copied to new trade record
        $trade->refresh();
        $data = array_merge($data, array_except($trade->toArray(), [
            'id',
            'partial_trade_id',
            'quantity',
            'quantity_remaining',
            'opened',
            'closed',
            'is_open',
            'cancel_initiated',
            'immediate_or_cancel',
            'is_conditional',
            'condition',
            'condition_target',
            'status',
            'created_at',
            'updated_at',
            'parent_trade_id',
            'starting_shrink_differential'
        ]));
        $data['quantity'] = $startQuantity - $remainingQuantity;
        /**
         * @var Trade $partialTrade
         */
        $partialTrade = Trade::byParentTrade($trade)->first();
        if (!$partialTrade) {
            if (! $trade->cancel_initiated && $data['price_per_unit']) {
                if ($trade->is_buy) {
                    $data['status'] = Trade::STATUS_BOUGHT;
                } elseif ($trade->is_sell) {
                    $data['status'] = Trade::STATUS_SOLD;
                }
                $data['parent_trade_id'] = $trade->id;
                $data['cycle_id'] = $trade->cycle_id;
                $partialTrade = Trade::create($data);
            }
        } else {
            $partialTrade->update($data);
        }
    }

    /**
     * @param Exchange $exchange
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @return MarketSummary
     * @throws InvalidOrMissingDataException
     */
    protected function getSuggestion(Exchange $exchange, Coin $baseCoin, Coin $targetCoin)
    {
        try {
            return Suggestion::where('base', $baseCoin->symbol)
                ->where('coin', $targetCoin->symbol)
                ->where('exchange', $exchange->id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new InvalidOrMissingDataException([
                'target_coin_id' => 'Missing suggestion data',
                'base_coin_id' => 'Missing suggestion data'
            ]);
        }
    }

    /**
     * @param Exchange $exchange
     * @param Coin $baseCoin
     * @param Coin $targetCoin
     * @param double $rate
     * @return double
     */
    protected function getStartingShrinkDifferential(Exchange $exchange, Coin $baseCoin, Coin $targetCoin, $rate)
    {
        $suggestion = $this->getSuggestion($exchange, $baseCoin, $targetCoin);

        return (double)$suggestion->highest_bid - (double)$rate;
    }

    public function getTotalCapital(ExchangeAccount $exchangeAccount)
    {
        $response = $this->tradingBot->getBalances(new BalancesRequest([
            'exchange' => $exchangeAccount->exchange_id,
            'key' => $exchangeAccount->key,
            'secret' => $exchangeAccount->secret
        ]), TradingBot::WAIT);

        if ($error = array_get($response, 'error')) {
            throw new TradingBotResponseException($error);
        }

        $balances = array_get($response, 'data.balances', []);
        $coinSymbols = array_pluck($balances, 'currency');
        $suggestions = Suggestion::whereIn('coin', $coinSymbols)
            ->where('exchange', $exchangeAccount->exchange_id)
            ->get(['highest_bid', 'coin'])
            ->keyBy('coin');
        $totalBtc = 0;
        foreach ($balances as $balance) {
            if (array_get($balance, 'currency') == 'BTC') {
                $coinBtc = 1;
            } else {
                $coinBtc = object_get($suggestions->get(array_get($balance, 'currency')), 'highest_bid');
            }
            $totalBtc += array_get($balance, 'total') * $coinBtc;
        }

        return $totalBtc;
    }
}