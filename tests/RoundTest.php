<?php

use App\AutoTrading\Strategies\SimpleStrategy;
use App\Models\Coin;
use App\Models\Cycle;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\MeanTradeValue;
use App\Models\Round;
use App\Models\Suggestion;
use App\Models\Trade;
use App\Models\TradingBotRequest;
use App\Models\User;
use App\Services\TradeService;
use App\TradingBot\FakeBot;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RoundTest extends ApiTestCase
{
    use UsesMockTradingBotTrait;

    /**
     * This test case tests the initial purchase of best candidate coins which pass the filter. We use
     * tailored suggestion data to ensure several coins match criteria, but expect to see the top n to
     * be purchased.
     */
    public function testInitialCyclePurchase()
    {
        // Basic user and exchange data setup
        $user = factory(User::class)->create();
        $exchange = factory(Exchange::class)->create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);
        // MeanTradeValue and Suggestion setup representing current market state
        $maxAti = 3;
        // ETC not progressive
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'mid', 'mean_buy_time' => 0.077]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // ETH not matching max ATI
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'old', 'mean_buy_time' => 10]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'mid', 'mean_buy_time' => 8]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'new', 'mean_buy_time' => $maxAti + 1]);
        // POWR valid
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'mid', 'mean_buy_time' => 9]);
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // OMG valid
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'mid', 'mean_buy_time' => 15]);
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'new', 'mean_buy_time' => $maxAti - 0.01]);
        // CVC valid
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'mid', 'mean_buy_time' => 12]);
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // GMB valid
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'mid', 'mean_buy_time' => 8]);
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // WAVES valid
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'old', 'mean_buy_time' => 70]);
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'mid', 'mean_buy_time' => 9]);
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'new', 'mean_buy_time' => $maxAti - 0.01]);
        // BCC not matching max ATI, not progressive
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'old', 'mean_buy_time' => 12]);
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'mid', 'mean_buy_time' => 13]);
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'new', 'mean_buy_time' => $maxAti + 1]);
        // ETC liquidity diff 0.1
        factory(Suggestion::class)->create([
            'coin' => 'ETC',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.4,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);
        // ETH liquidity diff 0.1
        factory(Suggestion::class)->create([
            'coin' => 'ETH',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.4,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);
        // POWR liquidity diff 0.3
        factory(Suggestion::class)->create([
            'coin' => 'POWR',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.2,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // CVC liquidity diff 0.2
        factory(Suggestion::class)->create([
            'coin' => 'CVC',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);
        // GMB liquidity diff -0.1
        factory(Suggestion::class)->create([
            'coin' => 'GMB',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.6,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);
        // GMB liquidity diff 0.2
        factory(Suggestion::class)->create([
            'coin' => 'WAVES',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);
        factory(Suggestion::class)->create([
            'coin' => 'OMG',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015,
            'impact_1hr' => 0.1,
            'prr' => 0.1
        ]);

        factory(Coin::class)->create(['id' => 'bitcoin', 'symbol' => 'BTC']);
        factory(Coin::class)->create(['id' => 'power', 'symbol' => 'POWR']);
        factory(Coin::class)->create(['id' => 'cvc', 'symbol' => 'CVC']);
        factory(Coin::class)->create(['id' => 'waves', 'symbol' => 'WAVES']);
        factory(Coin::class)->create(['id' => 'gmb', 'symbol' => 'GMB']);
        factory(Coin::class)->create(['id' => 'omg', 'symbol' => 'OMG']);

        // We expect to see some of the coins to match criteria to be candidates for purchase, and also
        // expect them to be ordered by liquidity difference, so coins with higher difference are purchased
        // first.
        $strategy = new SimpleStrategy(new TradeService(new TradingBot(new FakeBot())));
        $coins = $strategy->getCoinsMatchingAtiMovement('progressive', 3, $exchange->id, $maxAti);
        // Assert coins matching ATI movement
        $this->assertEquals('CVC', array_get($coins, 0), 'Expected filtered coin in proper order');
        $this->assertEquals('GMB', array_get($coins, 1), 'Expected filtered coin in proper order');
        $this->assertEquals('OMG', array_get($coins, 2), 'Expected filtered coin in proper order');
        $this->assertEquals('POWR', array_get($coins, 3), 'Expected filtered coin in proper order');
        $this->assertEquals('WAVES', array_get($coins, 4), 'Expected filtered coin in proper order');

        // At this point in time, we have 5 candidates matching ATI movement, and 3 of those passing liquidity diff for
        // purchase on the market, so we expect to see top n of those purchased when the round is started. We mock bot
        // responses for buy orders.
        $buyCoins = ['CVC', 'OMG', 'WAVES'];
        $mockTradeService = \Mockery::mock(TradeService::class . "[buy]", [new TradingBot(new FakeBot())])
            ->shouldAllowMockingProtectedMethods();
        foreach ($buyCoins as $buyCoin) {
            $mockTradeService->shouldReceive('buy')
                ->andReturnUsing(function($baseCoin, $targetCoin, $account, $quantity, $rate) {
                    return factory(Trade::class)->create([
                        'target_coin_id' => $targetCoin->symbol,
                        'base_coin_id' => $baseCoin->symbol,
                        'quantity' => $quantity,
                        'price_per_unit' => $rate,
                        'status' => Trade::STATUS_BUY_ORDER,
                        'user_id' => $account->user_id,
                        'exchange_account_id' => $account->id,
                        'exchange_id' => $account->exchange_id,
                        'is_open' => true
                    ]);
                });
        }
        $this->app->bind(TradeService::class, function () use ($mockTradeService) {
            return $mockTradeService;
        });
        
        $this->authenticatedJson('POST', 'api/round/start', ['exchange_account_id' => $account->id], [], $user);
        //dd($this->response->status(), $this->response->content());

        $cycle = Cycle::first();

        $this->seeInDatabase('trades', [
            'target_coin_id' => 'CVC',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
        $this->seeInDatabase('trades', [
            'target_coin_id' => 'WAVES',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
        $this->seeInDatabase('trades', [
            'target_coin_id' => 'OMG',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
    }

    /**
     *
     */
    public function testNextCycleCancelAndSellPurchase()
    {
        // Basic user and exchange data setup
        $user = factory(User::class)->create();
        $exchange = factory(Exchange::class)->create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);
        // MeanTradeValue and Suggestion setup representing current market state
        $maxAti = 2;
        // ETC not progressive
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'old', 'mean_buy_time' => 12.66]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'mid', 'mean_buy_time' => 0.77]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETC', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // ETH not matching max ATI
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'old', 'mean_buy_time' => 10]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'mid', 'mean_buy_time' => 8]);
        factory(MeanTradeValue::class)->create(['coin' => 'ETH', 'level' => 'new', 'mean_buy_time' => $maxAti + 1]);
        // POWR valid
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'old', 'mean_buy_time' => 10]);
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'mid', 'mean_buy_time' => 9]);
        factory(MeanTradeValue::class)->create(['coin' => 'POWR', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // OMG valid
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'old', 'mean_buy_time' => 20]);
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'mid', 'mean_buy_time' => 15]);
        factory(MeanTradeValue::class)->create(['coin' => 'OMG', 'level' => 'new', 'mean_buy_time' => $maxAti - 1]);
        // CVC valid
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'old', 'mean_buy_time' => 15]);
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'mid', 'mean_buy_time' => 12]);
        factory(MeanTradeValue::class)->create(['coin' => 'CVC', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // GMB valid
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'old', 'mean_buy_time' => 10]);
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'mid', 'mean_buy_time' => 8]);
        factory(MeanTradeValue::class)->create(['coin' => 'GMB', 'level' => 'new', 'mean_buy_time' => $maxAti]);
        // WAVES valid
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'old', 'mean_buy_time' => 12]);
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'mid', 'mean_buy_time' => 9]);
        factory(MeanTradeValue::class)->create(['coin' => 'WAVES', 'level' => 'new', 'mean_buy_time' => $maxAti - 1]);
        // BCC not matching max ATI, not progressive
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'old', 'mean_buy_time' => 12]);
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'mid', 'mean_buy_time' => 13]);
        factory(MeanTradeValue::class)->create(['coin' => 'BCC', 'level' => 'new', 'mean_buy_time' => $maxAti + 1]);
        // ETC liquidity diff 0.1
        factory(Suggestion::class)->create([
            'coin' => 'ETC',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.4,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // ETH liquidity diff 0.1
        factory(Suggestion::class)->create([
            'coin' => 'ETH',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.4,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // POWR liquidity diff 0.3
        factory(Suggestion::class)->create([
            'coin' => 'POWR',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.2,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // CVC liquidity diff 0.2
        factory(Suggestion::class)->create([
            'coin' => 'CVC',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // GMB liquidity diff -0.1
        factory(Suggestion::class)->create([
            'coin' => 'GMB',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.6,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        // GMB liquidity diff 0.2
        factory(Suggestion::class)->create([
            'coin' => 'WAVES',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);
        factory(Suggestion::class)->create([
            'coin' => 'OMG',
            'btc_liquidity_bought' => 0.5,
            'btc_liquidity_sold' => 0.3,
            'lowest_ask' => 0.002,
            'highest_bid' => 0.0015
        ]);

        factory(Coin::class)->create(['id' => 'bitcoin', 'symbol' => 'BTC']);
        factory(Coin::class)->create(['id' => 'power', 'symbol' => 'POWR']);
        factory(Coin::class)->create(['id' => 'cvc', 'symbol' => 'CVC']);
        factory(Coin::class)->create(['id' => 'waves', 'symbol' => 'WAVES']);
        factory(Coin::class)->create(['id' => 'gmb', 'symbol' => 'GMB']);
        factory(Coin::class)->create(['id' => 'omg', 'symbol' => 'OMG']);

        // We expect to see some of the coins to match criteria to be candidates for purchase, and also
        // expect them to be ordered by liquidity difference, so coins with higher difference are purchased
        // first.
        $strategy = new SimpleStrategy(new TradeService(new TradingBot(new FakeBot())));
        $coins = $strategy->getCoinsMatchingAtiMovement('progressive', 3, $exchange->id, $maxAti);
        // Assert coins matching ATI movement
        $this->assertEquals('CVC', array_get($coins, 0), 'Expected filtered coin in proper order');
        $this->assertEquals('GMB', array_get($coins, 1), 'Expected filtered coin in proper order');
        $this->assertEquals('OMG', array_get($coins, 2), 'Expected filtered coin in proper order');
        $this->assertEquals('POWR', array_get($coins, 3), 'Expected filtered coin in proper order');
        $this->assertEquals('WAVES', array_get($coins, 4), 'Expected filtered coin in proper order');

        // At this point in time, we have 5 candidates matching ATI movement, and 3 of those passing liquidity diff for
        // purchase on the market, so we expect to see top n of those purchased when the round is started. We mock bot
        // responses for buy orders.
        $buyCoins = ['CVC', 'GMB', 'OMG'];
        $mockTradeService = \Mockery::mock(TradeService::class . "[buy]", [new TradingBot(new FakeBot())])
            ->shouldAllowMockingProtectedMethods();
        foreach ($buyCoins as $buyCoin) {
            $mockTradeService->shouldReceive('buy')
                ->andReturnUsing(function($baseCoin, $targetCoin, $account, $quantity, $rate) {
                    return factory(Trade::class)->create([
                        'target_coin_id' => $targetCoin->symbol,
                        'base_coin_id' => $baseCoin->symbol,
                        'quantity' => $quantity,
                        'price_per_unit' => $rate,
                        'status' => Trade::STATUS_BUY_ORDER,
                        'user_id' => $account->user_id,
                        'exchange_account_id' => $account->id,
                        'exchange_id' => $account->exchange_id,
                        'is_open' => true
                    ]);
                });
        }
        $this->app->bind(TradeService::class, function () use ($mockTradeService) {
            return $mockTradeService;
        });

        $this->authenticatedJson('POST', 'api/round/start', ['exchange_account_id' => $account->id], [], $user);
        //dd($this->response->status(), $this->response->content());

        $cycle = Cycle::first();

        $this->seeInDatabase('trades', [
            'target_coin_id' => 'CVC',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
        $this->seeInDatabase('trades', [
            'target_coin_id' => 'GMB',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
        $this->seeInDatabase('trades', [
            'target_coin_id' => 'OMG',
            'cycle_id' => $cycle->id,
            'price_per_unit' => 0.002,
            'quantity' => 10,
            'status' => Trade::STATUS_BUY_ORDER
        ]);
    }

    protected function mockTradingBotOrders(array $orders)
    {
        $mockedResponses = [];
        foreach ($orders as $order) {
            $mockedResponses[] = [
                'method' => 'refreshTradingBotRequest',
                'expected_response' => function (TradingBotRequest $tradingBotRequest) {
                    $tradingBotRequest->json_response = [
                        'job_id' => 42,
                        'job_status' => 'completed',
                        'data' => [
                            'order_uuid' => 'uuid'
                        ]
                    ];
                    $tradingBotRequest->save();

                    return $tradingBotRequest;
                }
            ];
            $mockedResponses[] = [
                'method' => 'getTradingBotRequestResponse',
                'expected_response' => function ($id) {
                    return [
                        'trading_bot_request_id' => $id,
                        'is_open' => false,
                        'job_id' => 42,
                        'job_status' => 'completed',
                        'data' => [
                            'order_uuid' => 'uuid',
                            'quantity' => 0.025,
                            'quantity_remaining' => 0
                        ]
                    ];
                }
            ];
        } 
        $this->mockTradingBot($mockedResponses);
    }

    public function testPurchaseTrades()
    {
        $user = factory(User::class)->create();
        $exchange = factory(Exchange::class)->create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
        ]);
        $cycleLength = floor(24 * 60 * 60 / $account->auto_global_cycles);
        $round = Round::create([
            'exchange_account_id' => $account->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDay(),
            'cycle_count' => $account->auto_global_cycles,
            'cycle_length' => $cycleLength,
            'strategy' => $account->auto_global_strategy
        ]);
        $cycleStart = Carbon::now();
        $cycleEnd = Carbon::now();
        $cycleEnd->addSeconds($cycleLength);
        $cycle = Cycle::create([
            'round_id' => $round->id,
            'index' => 0,
            'start_at' => $cycleStart,
            'end_at' => $cycleEnd
        ]);
        $trade = factory(Trade::class)->create(['cycle_id' => $cycle->id, 'target_coin_id' => 'ETH']);
        $trade = factory(Trade::class)->create(['cycle_id' => $cycle->id, 'target_coin_id' => 'OMG', 'is_open' => false, 'status' => Trade::STATUS_BOUGHT]);
        $purchase1 = \App\Models\CyclePurchase::create(['cycle_id' => $cycle->id, 'coin' => 'ETH', 'ati' => 0, 'last_purchased_at' => Carbon::now()]);
        $purchase2 = \App\Models\CyclePurchase::create(['cycle_id' => $cycle->id, 'coin' => 'OMG', 'ati' => 0, 'last_purchased_at' => Carbon::now()]);

        $purchase1->load('buyTrades');
        $purchase2->load('boughtTrades');
        $purchases = new Collection([$purchase1, $purchase2]);
        $purchasesWithBought = $purchases->filter(function ($purchase) {
            $count = $purchase->buyTrades->filter(function ($trade) {
                return $trade->quantity > 0;
            })->count();
            $count += $purchase->boughtTrades->filter(function ($trade) {
                return $trade->quantity > 0;
            })->count();

            return $count > 0;
        });
        $this->assertEquals(2, $purchasesWithBought->count());
    }

    public function testSimpleStrategyProcessExit()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);
        $cycleLength = floor(24 * 60 * 60 / $account->auto_global_cycles);
        $round = Round::create([
            'exchange_account_id' => $account->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDay(),
            'cycle_count' => $account->auto_global_cycles,
            'cycle_length' => $cycleLength,
            'strategy' => $account->auto_global_strategy
        ]);
        $cycleStart = Carbon::now();
        $cycleEnd = Carbon::now();
        $cycleEnd->addSeconds($cycleLength);
        $cycle = Cycle::create([
            'round_id' => $round->id,
            'index' => 0,
            'start_at' => $cycleStart,
            'end_at' => $cycleEnd
        ]);

        $strategy = new SimpleStrategy(new TradeService(new TradingBot(new FakeBot())));
        $strategy->processExit($round, $account);
    }

    public function testRoundStart()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);

        $this->authenticatedJson('POST', "api/round/start", [
            'exchange_account_id' => $account->id
        ], [], $user);

        $data = array_get(json_decode($this->response->content(), true), 'data');
        $this->assertEquals(true, array_get($data, 'active'));
    }

    public function testRoundStatus()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);

        $this->authenticatedJson('GET', "api/round/$account->id", [], [], $user);
        $data = array_get(json_decode($this->response->content(), true), 'data');
        $this->assertEquals(false, array_get($data, 'active'));

        $this->authenticatedJson('POST', "api/round/start", [
            'exchange_account_id' => $account->id
        ], [], $user);

        $this->authenticatedJson('GET', "api/round/$account->id", [], [], $user);
        $data = array_get(json_decode($this->response->content(), true), 'data');
        $this->assertEquals(true, array_get($data, 'active'));
    }

    public function testRoundStop()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => $exchange->id,
            'auto_global_strategy' => 'simple',
            'auto_entry_position_btc' => 0.02
        ]);

        $this->authenticatedJson('GET', "api/round/$account->id", [], [], $user);
        $data = array_get(json_decode($this->response->content(), true), 'data');
        $this->assertEquals(false, array_get($data, 'active'));

        $this->authenticatedJson('POST', "api/round/start", [
            'exchange_account_id' => $account->id
        ], [], $user);

        $this->authenticatedJson('POST', "api/round/stop", [
            'exchange_account_id' => $account->id
        ], [], $user);
        $this->assertEquals(200, $this->response->status());

        $this->authenticatedJson('GET', "api/round/$account->id", [], [], $user);
        $data = array_get(json_decode($this->response->content(), true), 'data');
        $this->assertEquals(false, array_get($data, 'active'));
    }
}