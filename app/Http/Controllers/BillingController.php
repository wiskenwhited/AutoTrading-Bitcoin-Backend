<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Helpers\Coinpayments;
use App\Http\Controllers\ApiController;
use App\Models\BillingHistory;
use App\Models\BillingPackage;
use App\Models\ScratchCode;
use App\Models\UserPackage;
use App\Models\UserPackageExchange;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;


class BillingController extends ApiController
{
    public function pricing()
    {
        $packages = BillingPackage::get();

        return response()->json($packages);
    }

    public function packageData(Auth $auth)
    {
        $packages = BillingPackage::get();
        $billingHistory = BillingHistory::whereUserId($auth->user()->id)->orderBy('created_at', 'DESC')->get();
        foreach ($packages as $package) {
            $history = $billingHistory->where('package_id', $package->id)->first();
            $purchased = $history ? 1 : 0;
            $package->setAttribute('purchased', $history ? 1 : 0);
            if (!$purchased) {
                $package->setAttribute('completed', 0);
                $package->setAttribute('failed', 0);
            } else {
                $package->setAttribute('completed', $history ? $history->completed : 1);
                $package->setAttribute('failed', $history ? $history->failed : 0);
            }
        }
        $merchant_id = config('services.coinpayments.merchant_id');

        return response()->json([
            'user_id' => $auth->user()->id,
            'packages' => $packages,
            'merchant_id' => $merchant_id,
        ]);

    }

    public function scratch(Auth $auth, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scratchCode = ScratchCode::where('code', $request->code)->whereNull('user_id')->where('assigned', false)->first();
        if (!$scratchCode)
            return response()->json('Invalid scratchcard', 422);


        $billingService = new BillingService();
        $success = $billingService->activatePackage($auth->user()->id, BillingPackage::Package5);

        if ($success) {
            $scratchCode->assigned = true;
            $scratchCode->user_id = $auth->user()->id;
            $scratchCode->save();
        }


        return response()->json($success);
    }


    public function userPackage(Auth $auth, Request $request)
    {
        $userPackage = UserPackage::with('exchanges')->whereUserId($auth->user()->id)->first();

        return response()->json($userPackage);
    }

    public function checkTestPackage(Auth $auth, Request $request)
    {
        $userPackage = UserPackage::whereUserId($auth->user()->id)->first();

        $active = false;
        $time = $userPackage && $userPackage->test_valid_until ? $userPackage->test_valid_until->toDateTimeString() : null;
        if ($userPackage && $userPackage->test_enabled && $userPackage->test_started && $userPackage->test_valid_until >= Carbon::now()) {
            $active = true;
        }

        return response()->json([
            'active' => $active,
            'until' => $time
        ]);
    }

    public function checkLivePackage(Auth $auth, Request $request)
    {
        $userPackage = UserPackage::with('exchanges')->whereUserId($auth->user()->id)->first();

        $active_for_all = false;
        $time_for_all = $userPackage && $userPackage->all_live_valid_until ? $userPackage->all_live_valid_until->toDateTimeString() : null;

        if ($userPackage && $userPackage->all_live_enabled && $userPackage->all_live_started && $userPackage->all_live_valid_until >= Carbon::now()) {
            $active_for_all = true;
        }

        $exchanges = [];
        if ($userPackage && $userPackage->exchanges) {
            foreach ($userPackage->exchanges as $exchange) {
                $exchanges[] = [
                    'exchange' => $exchange->exchange,
                    'until' => $exchange->live_valid_until ? $exchange->live_valid_until->toDateTimeString() : null,
                    'active' => $exchange->live_valid_until ? ($exchange->live_valid_until >= Carbon::now()) : false
                ];
            }
        }

        return response()->json([
            'active_for_all' => $active_for_all,
            'all_until' => $time_for_all,
            'exchanges' => $exchanges
        ]);
    }

    public function activateLiveMode(Auth $auth, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exchange' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $userPackage = UserPackage::whereUserId($auth->user()->id)->first();
        if ($userPackage) {
            $userPackageExchange = UserPackageExchange::where('exchange', $request->input('exchange'))->where('user_package_id', $userPackage->id)->first();
            if (!$userPackageExchange) {
                $userPackageExchange = UserPackageExchange::where('exchange', '')->where('user_package_id', $userPackage->id)->first();

                if (!$userPackageExchange)
                    return response()->json('No activate package', 422);

                $userPackageExchange->exchange = $request->input('exchange');
            }
        }

        if (!$userPackage || $userPackageExchange->active_days_eligible <= 0) {
            return response()->json(['User does not have days remaining'], 422);
        }

        $userPackageExchange->live_enabled = true;
        if (!$userPackageExchange->live_started)
            $userPackageExchange->live_started = Carbon::now();
        if (!$userPackageExchange->live_valid_until) {
            $userPackageExchange->live_valid_until = Carbon::now()->addDay(1);
        } else {
            $userPackageExchange->live_valid_until = $userPackageExchange->live_valid_until->addDay();
        }
        $userPackageExchange->active_days_eligible = $userPackageExchange->active_days_eligible - 1;
        $userPackageExchange->save();

        $userPackage = $userPackage->load('exchanges');
        return response()->json($userPackage);

    }

    public function activateTestMode(Auth $auth, Request $request)
    {
        $userPackage = UserPackage::whereUserId($auth->user()->id)->first();

        if ($userPackage && $userPackage->test_days_eligible > 0) {
            $userPackage->test_enabled = true;
            if (!$userPackage->test_started)
                $userPackage->test_started = Carbon::now();
            if (!$userPackage->test_valid_until) {
                $userPackage->test_valid_until = Carbon::now()->addDay(1);
            } else {
                $userPackage->test_valid_until = $userPackage->test_valid_until->addDay();
            }
            $userPackage->test_days_eligible = $userPackage->test_days_eligible - 1;
            $userPackage->save();

            $userPackage = $userPackage->load('exchanges');
            return response()->json($userPackage);
        }

        return response()->json(['User does not have testing days remaining'], 422);
    }

    public function testPurchase(Auth $auth, Request $request)
    {
        if (App::environment('production'))
            abort(404);

        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $billingService = new BillingService();
        $success = $billingService->activatePackage($auth->user()->id, $request->input('package_id'), $request->input('quantity'), null, null, $request->input('exchange') ?: '');

        return response()->json($success);
    }

    public function history(Auth $auth)
    {
        $history = BillingHistory::where('user_id', $auth->user()->id)->orderBy('created_at', 'DESC')->get();
        return response()->json($history);
    }
}