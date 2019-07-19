<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Helpers\Coinpayments;
use App\Helpers\TestCoinpayments;
use App\Http\Controllers\ApiController;
use App\Models\BillingPackage;
use App\Models\ScratchCode;
use App\Models\UserPackage;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CoinPaymentController extends ApiController
{
    public function retrieve(Request $request)
    {
        $cps = new BillingService();
        Log::info('CoinPaymentController entered.', $request->input());

        $status = $cps->validate($request);
        Log::info('CoinPaymentController validated.', $request->input());

        if ($status) {
            Log::info('CoinPaymentController passed.', $request->input());

            $txn_id = $request->input('txn_id');
            $custom = $request->input('custom');
            $quantity = intval($request->input('quantity'));
            $customArray = explode("&", $custom);
            $exchange = $user_id = $product_id = null;

            if (isset($customArray[0])) {
                $user = explode('=', $customArray[0]);
                if (isset($user[0]) && $user[0] == 'user_id')
                    $user_id = $user[1];
            }

            if (isset($customArray[1])) {
                $product = explode('=', $customArray[1]);
                if (isset($product[0]) && $product[0] == 'package_id')
                    $product_id = $product[1];
            }

            if (isset($customArray[2])) {
                $exchange_data = explode('=', $customArray[2]);
                if (isset($exchange_data[0]) && $exchange_data[0] == 'exchange')
                    $exchange = $exchange_data[1];
            }

            $amount = floatval($request->input('amount1'));

            Log::info('CoinPaymentController status: '.$status, $request->input());

            if ($status == 'pending') {
                $cps->createPackage($user_id, $product_id, $quantity, $amount, $txn_id);
            }

            if ($status == 'complete') {
                $cps->activatePackage($user_id, $product_id, $quantity, $amount, $txn_id, $exchange);
            }


            if ($status == 'failed') {
                $cps->failedPackage($user_id, $product_id, $quantity, $amount, $txn_id);
            }
            return $status;
        }

    }
}