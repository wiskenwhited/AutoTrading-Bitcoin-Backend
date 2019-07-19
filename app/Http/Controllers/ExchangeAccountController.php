<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\ExchangeAccount;
use App\Views\ExchangeAccountView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExchangeAccountController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        $query = ExchangeAccount::byUser($this->auth->user());
        $total = $query->count();
        $accounts = $this->applyPaginationData($request, $query)->get();

        $view = new ExchangeAccountView();

        return response()->json([
            'data' => $view->render($accounts),
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }

        $view = new ExchangeAccountView();

        return response()->json($view->render($account));
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->input(), [
            'name' => 'required',
            'exchange_id' => 'required|string',
            'key' => 'required',
            'secret' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $data = array_merge($request->input(), ['user_id' => $this->auth->user()->id]);
        $data['key'] = trim($data['key']);
        $data['secret'] = trim($data['secret']);
        $account = ExchangeAccount::create($data);

        $view = new ExchangeAccountView();

        return response()->json($view->render($account), 201);
    }

    public function update(Request $request, $id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }
        $validator = Validator::make($request->input(), [
            // Account details
            'exchange_id' => 'string',
            'name' => 'required_with:exchange_id',
            'key' => 'required_with:exchange_id',
            'secret' => 'required_with:exchange_id',
            // Auto trading settings
            'auto_global_is_auto_trading' => 'boolean',
            'auto_global_round_duration' => 'required_if:auto_global_is_auto_trading,true|integer',
            'auto_global_round_granularity' => 'required_if:auto_global_is_auto_trading,true|in:hours,days',
            'auto_global_cycles' => 'required_if:auto_global_is_auto_trading,true|integer',
            'auto_global_age' => 'required_if:auto_global_strategy,advanced|in:2,3',
            'auto_global_strategy' => 'required_if:auto_global_is_auto_trading,true|in:simple,advanced',
            'auto_entry_minimum_fr' => 'required_if:auto_global_strategy,advanced|in:25,50,75,100',
            'auto_entry_price_movement' => 'required_if:auto_global_strategy,advanced|in:progressive,regressive',
            'auto_entry_price_sign' => 'required_if:auto_global_strategy,advanced|in:any,positive,negative',
            'auto_entry_volume_movement' => 'required_if:auto_global_strategy,advanced|in:progressive,regressive',
            'auto_entry_volume_sign' => 'required_if:auto_global_strategy,advanced|in:any,positive,negative',
            'auto_entry_maximum_ati' => 'required_if:auto_global_is_auto_trading,true|numeric',
            'auto_entry_ati_movement' => 'required_if:auto_global_is_auto_trading,true|in:progressive,regressive',
            'auto_entry_ati_sign' => 'required_if:auto_global_is_auto_trading,true|in:any,positive,negative',
            'auto_entry_minimum_liquidity_variance' => 'required_if:auto_global_strategy,advanced|numeric',
            'auto_entry_minimum_prr' => 'required_if:auto_global_strategy,advanced|numeric',
            'auto_entry_hold_time_granularity' => 'required_if:auto_global_strategy,advanced|in:minutes,hours',
            'auto_entry_hold_time' => 'required_if:auto_global_strategy,advanced|integer',
            'auto_entry_price' => 'required_if:auto_global_strategy,advanced|in:low,current',
            'auto_entry_position_btc' => 'required_if:auto_global_is_auto_trading,true|numeric',
            'auto_entry_open_time' => 'required_if:auto_global_is_auto_trading,true|integer',
            'auto_exit_action' => 'in:move,sell',
            'auto_exit_intervals' => 'integer',
            'auto_exit_drops' => 'integer'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $data = $request->input();
        if (array_has($data, 'key') && array_has($data, 'value')) {
            $data['key'] = trim($data['key']);
            $data['secret'] = trim($data['secret']);
        }
        $account->update($data);

        $view = new ExchangeAccountView();

        return response()->json($view->render($account));
    }

    public function delete($id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }
        $account->delete();

        return response()->json([], 200);
    }

    protected function userHasAccess($id)
    {
        $user = $this->auth->user();
        $account = ExchangeAccount::findOrFail($id);

        return $account->user_id == $user->id;
    }
}