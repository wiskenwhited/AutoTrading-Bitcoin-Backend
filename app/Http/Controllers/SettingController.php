<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Views\SettingView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function show()
    {
        $view = new SettingView();

        return response()->json($view->render($this->auth->user()));
    }

    public function update(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->input(), [
            'global_round' => 'integer|in:1,30',
            'global_cycle_granularity' => 'in:hours,days',
            'global_cycle' => 'integer',
            'entry_minimum_fr' => 'integer|in:25,50,75,100',
            'entry_price_movement' => 'in:progressive,regressive',
            'entry_price_from' => 'integer|in:2,3',
            'entry_volume_movement' => 'in:progressive,regressive',
            'entry_volume_from' => 'integer|in:2,3',
            'entry_ati' => 'numeric',
            'entry_ati_movement' => 'in:progressive,regressive',
            'entry_ati_from' => 'integer|in:2,3',
            'entry_liquidity_variance' => 'numeric',
            'entry_minimum_prr' => 'numeric',
            'entry_hold_time_granularity' => 'in:hours,minutes',
            'entry_hold_time' => 'integer',
            'entry_price' => 'in:low,current',
            'entry_historic_target_from' => 'integer|in:1,2,3',
            'entry_historic_target' => 'numeric',
            'entry_position_btc' => 'numeric',
            'entry_open_time' => 'integer',
//            'entry_notified_by_email' => 'required',
//            'entry_notified_by_sms' => 'required',
//            'entry_is_auto_trading' => 'required',
//            'exit_target' => 'required',
//            'exit_shrink_differential' => 'required',
//            'exit_option' => 'required',
//            'exit_notified_by_email' => 'required',
//            'exit_notified_by_sms' => 'required',
//            'exit_is_auto_trading' => 'required',
//            'smart_sell_enabled' => 'required',
            'smart_sell_interval' => 'required_if:smart_sell_enabled,1',
            'smart_sell_drops' => 'required_if:smart_sell_enabled,1',
//            'withdrawal_capital_balance_currency' => 'required',
//            'withdrawal_value' => 'required',
//            'withdrawal_value_coin' => 'required',
//            'withdrawal_address' => 'required',
//            'withdrawal_notified_by_email' => 'required',
//            'withdrawal_notified_by_sms' => 'required',
//            'withdrawal_is_auto_trading' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // TODO Check if there's an active round, if yes do not allow change of some entry settings

        $only = [
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

        $data = [];
        foreach ($request->json() as $field => $value) {
            if (in_array($field, $only)) {
                $data[$field] = $value;
            }
        }

        $user->update($data);

        $view = new SettingView();

        return response()->json($view->render($user));
    }
}