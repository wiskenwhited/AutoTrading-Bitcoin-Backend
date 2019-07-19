<?php

namespace App\Models;

use App\Helpers\EmailHelper;
use App\Helpers\GeneralHelper;
use App\Helpers\TwilioHelper;
use App\Services\BillingService;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, FormatsCurrencyTrait;

    const StandardRole = 1;
    const ManagerRole = 2;
    const AdminRole = 3;

    const TwoFANone = 0;
    const TwoFAEmail = 1;
    const TwoFASMS = 2;
    const TwoFAGA = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country',
        'city',
        'phone',
        'currency',
        // Settings
        'global_round',
        'global_cycle_granularity',
        'global_cycle',
        'entry_minimum_fr',
        'entry_price_movement',
        'entry_price_from',
        'entry_volume_movement',
        'entry_volume_from',
        'entry_ati',
        'entry_ati_movement',
        'entry_ati_from',
        'entry_liquidity_variance',
        'entry_minimum_prr',
        'entry_hold_time_granularity',
        'entry_hold_time',
        'entry_price',
        'entry_historic_target_from',
        'entry_historic_target',
        'entry_position_btc',
        'entry_open_time',
        'entry_notified_by_email',
        'entry_notified_by_sms',
        'entry_is_auto_trading',
        'exit_target',
        'exit_shrink_differential',
        'exit_option',
        'exit_notified_by_email',
        'exit_notified_by_sms',
        'exit_is_auto_trading',
        'smart_sell_enabled',
        'smart_sell_interval',
        'smart_sell_drops',
        'withdrawal_capital_balance',
        'withdrawal_capital_balance_currency',
        'withdrawal_value',
        'withdrawal_value_coin',
        'withdrawal_address',
        'withdrawal_notified_by_email',
        'withdrawal_notified_by_sms',
        'withdrawal_is_auto_trading'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'verification_link'
    ];

    public function currencyRate()
    {
        return $this->belongsTo(CurrencyRate::class, 'currency', 'target');
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function autoTradeExchangeAccounts()
    {
        return $this->hasMany(ExchangeAccount::class)->where('auto_global_is_auto_trading', true);
    }

    public function total_profit($exchange, $isTest)
    {
        $totalFlat = $totalBtc = 0;
        $trades = Trade::where('trades.exchange_id', $exchange)
            ->where('trades.user_id', $this->id)
            ->where('trades.status', '<>', Trade::STATUS_SOLD)
            ->where('trades.is_test', $isTest)
            ->select(
                "trades.*",
                DB::raw("CASE WHEN trades.status = '" . Trade::STATUS_BOUGHT . "' 
                OR trades.status = '" . Trade::STATUS_BUY_ORDER . "' THEN 
                    GREATEST(0, (s.highest_bid - trades.price_per_unit) * 0.9975 / 1.0025 * trades.quantity) 
                ELSE
                    GREATEST(0, (trades.price_per_unit - bought.price_per_unit) * 0.9975 / 1.0025 * trades.quantity)
                END AS profit")
            )
            ->leftJoin(DB::raw('trades AS bought'), function ($join) {
                $join->on('trades.original_trade_id', '=', 'bought.id');
            })
            ->leftJoin(DB::raw('suggestions AS s'), function ($join) {
                $join->on('s.coin', '=', 'trades.target_coin_id');
                $join->on('s.base', '=', 'trades.base_coin_id');
                $join->on('s.exchange', '=', 'trades.exchange_id');
            })
            ->get();
        if ($trades->count() > 0) {
            foreach ($trades as $trade) {
                $totalBtc += $trade->profit;
            }

            $rate = CurrencyRate::baseAndTarget('USD', $this->currency)->first();
            $coin = Coin::find('bitcoin');
            $totalFlat = $coin->price_usd * $rate->rate * $totalBtc;
        }

        return [
            'total_profit' => $totalBtc,
            'total_profit_currency' => 'BTC',
            'total_flat' => $this->formatNumber($totalFlat),
            'total_flat_currency' => $this->currency,
        ];
    }

    public function profit_realized($exchange, $isTest)
    {
        $totalFlat = $totalBtc = 0;
        $trades = Trade::where('trades.exchange_id', $exchange)
            ->where('trades.user_id', $this->id)
            ->where('trades.status', Trade::STATUS_SOLD)
            ->where('trades.is_test', $isTest)
            ->select(
                "trades.*",
                DB::raw("GREATEST(0, (trades.price_per_unit - bought.price_per_unit) * 0.9975 / 1.0025 * trades.quantity) AS profit")
            )
            ->leftJoin(DB::raw('trades AS bought'), function ($join) {
                $join->on('trades.original_trade_id', '=', 'bought.id');
            })
            ->leftJoin(DB::raw('suggestions AS s'), function ($join) {
                $join->on('s.coin', '=', 'trades.target_coin_id');
                $join->on('s.base', '=', 'trades.base_coin_id');
                $join->on('s.exchange', '=', 'trades.exchange_id');
            })
            ->get();
        if ($trades->count() > 0) {
            foreach ($trades as $trade) {
                $totalBtc += $trade->profit;
            }

            $rate = CurrencyRate::baseAndTarget('USD', $this->currency)->first();
            $coin = Coin::find('bitcoin');
            $totalFlat = $coin->price_usd * $rate->rate * $totalBtc;
        }

        return [
            'realized_profit' => $totalBtc,
            'realized_profit_currency' => 'BTC',
            'realized_flat' => $this->formatNumber($totalFlat),
            'realized_flat_currency' => $this->currency,
        ];
    }

    public function getUserRoleAttribute()
    {
        if ($this->role_id == self::StandardRole)
            return 'standard';
        elseif ($this->role_id == self::ManagerRole)
            return 'manager';
        elseif ($this->role_id == self::AdminRole)
            return 'admin';
    }

    public function getExitStrategySetAttribute()
    {
        return !is_null($this->exit_target);
    }

    public function sendVerificationCode()
    {
        $this->confirmed_2fa = false;
        $code = GeneralHelper::QuickOnlyNumbers(6);
        $this->code_2fa = $code;

        $billingService = new BillingService();
        if ($this->enabled_2fa == User::TwoFAEmail) {
            if ($billingService->hasEmailsRemaining($this->id)) {
                EmailHelper::sendVerificationCode($this, $code);
                $billingService->emailSent($this->id);
            } else {
                return 'none';
            }
        }
        if ($this->enabled_2fa == User::TwoFASMS) {
            if ($billingService->hasSmsRemaining($this->id)) {
                $twilioHelper = new TwilioHelper();
                $twilioHelper->sendText($this->phone, 'Your verification code is: ' . $code);
                $billingService->smsSent($this->id);
            } else {
                return 'none';
            }
        }
        $this->save();

        return 'sent';
    }
}
