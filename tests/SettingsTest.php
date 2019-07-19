<?php

use App\Models\ExchangeAccount;
use App\Models\TradingBotRequest;
use App\Models\User;
use App\TradingBot\TradingBot;
use Illuminate\Support\Facades\Redis;

class SettingsTest extends ApiTestCase
{
    use UsesMockTradingBotTrait;

    public function testJobInProgress()
    {
        $user = factory(User::class)->create();

        $this->authenticatedJson('PATCH', '/api/users/current/settings', [
            'entry_price_movement' => 'progressive',
            'entry_price_from' => 2,
            'entry_volume_movement' => 'progressive',
            'entry_volume_from' => 2,
            'entry_ati' => 2.232456,
            'entry_ati_movement' => 'progressive',
            'entry_ati_from' => 2,
            'entry_liquidity_variance' => 2.234567,
            'entry_minimum_prr' => 2.234567
        ], [], $user);

        $this->authenticatedJson('PATCH', '/api/users/current/settings', [
            'exit_target' => 2.231,
            'exit_shrink_differential' => 1.2345,
            'exit_option' => 'Sell',
            'exit_notified_by_email' => true,
            'exit_notified_by_sms' => false,
            'exit_is_auto_trading' => true
        ], [], $user);

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals(2, array_get($response, 'data.entry_price_from'));
        $this->assertEquals(2.231, array_get($response, 'data.exit_target'));
    }
}