<?php


namespace App\Services;


use App\Auth\Auth;
use App\Models\BillingHistory;
use App\Models\BillingPackage;
use App\Models\ReferralBonus;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\UserPackageExchange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingService
{
    private $packageRules;
    private $dbPackage;


    public function __construct()
    {
        $billingPackages = new BillingPackage();
        $this->packageRules = $billingPackages->package_rules();
    }

    public function createPackage($user_id, $package_id, $quantity = 1, $price = null, $payment_id = null)
    {

        $package = BillingPackage::find($package_id);
//        if (!$package || ($price && $package->price != $price))
//            return false;
        $this->dbPackage = $package;

        $billingHistory = false;
        if ($payment_id) {
            $billingHistory = BillingHistory::where('payment_id', $payment_id)->first();
        }

        if (!$billingHistory)
            $billingHistory = new BillingHistory();

        $billingHistory->user_id = $user_id;
        $billingHistory->package_id = $package_id;
        $billingHistory->payment_id = $payment_id;
        $billingHistory->description = $package->description;
        $billingHistory->price_per_item = $price ?: 0;
        $billingHistory->quantity = $quantity;
        $billingHistory->total_price = $price ? $price * $quantity : 0;
        $billingHistory->completed = 0;
        $billingHistory->scratch_card_used = is_null($price) && is_null($payment_id);
        $billingHistory->save();
    }

    public function failedPackage($user_id, $package_id, $quantity = 1, $price = null, $payment_id = null)
    {

        $package = BillingPackage::find($package_id);
//        if (!$package || ($price && $package->price != $price))
//            return false;
        $this->dbPackage = $package;

        $billingHistory = false;
        if ($payment_id)
            $billingHistory = BillingHistory::where('payment_id', $payment_id)->first();

        if ($payment_id && $billingHistory) {
            $billingHistory->completed = 1;
            $billingHistory->failed = 1;
            $billingHistory->save();
        } else {
            $billingHistory = new BillingHistory();
            $billingHistory->user_id = $user_id;
            $billingHistory->package_id = $package_id;
            $billingHistory->payment_id = $payment_id;
            $billingHistory->description = $package->description;
            $billingHistory->price_per_item = $price ?: 0;
            $billingHistory->quantity = $quantity;
            $billingHistory->total_price = $price ? $price * $quantity : 0;
            $billingHistory->completed = 1;
            $billingHistory->failed = 1;
            $billingHistory->scratch_card_used = is_null($price) && is_null($payment_id);
            $billingHistory->save();
        }
    }

    public function activatePackage($user_id, $package_id, $quantity = 1, $price = null, $payment_id = null, $exchange = '')
    {
        $package = BillingPackage::find($package_id);

        if (!$package)// || ($price && $package->price != $price))
            return false;
        $this->dbPackage = $package;

        $approved = $this->extendPackage($user_id, $package_id, $quantity, $exchange);

        if ($approved) {
            $billingHistory = false;

            if ($payment_id)
                $billingHistory = BillingHistory::where('payment_id', $payment_id)->first();

            if ($payment_id && $billingHistory) {
                $billingHistory->completed = 1;
                $billingHistory->save();
            } else {

                $billingHistory = new BillingHistory();
                $billingHistory->user_id = $user_id;
                $billingHistory->package_id = $package_id;
                $billingHistory->payment_id = $payment_id;
                $billingHistory->description = $package->description;
                $billingHistory->price_per_item = $price ?: 0;
                $billingHistory->quantity = $quantity;
                $billingHistory->total_price = $price ? ($price * $quantity) : 0;
                $billingHistory->completed = 1;
                $billingHistory->scratch_card_used = is_null($price) && is_null($payment_id);
                $billingHistory->save();

            }

            $referral = new ReferralBonus();
            $referral->assignBonus($user_id, $billingHistory->id);
        }

        return $approved;
    }

    private function extendPackage($user_id, $package_id, $quantity, $exchange)
    {
        try {
            $userPackage = UserPackage::where(['user_id' => $user_id])->first();
            if (!$userPackage) {
                $userPackage = new UserPackage();
                $userPackage->user_id = $user_id;
            }

            $package = $this->packageRules[$package_id];

            $userPackage->sms_max = $userPackage->sms_max + $this->dbPackage->sms;
            $userPackage->email_max = $userPackage->email_max + $this->dbPackage->emails;

            $test_duration = $package['test_duration'];

            if ($test_duration > 0) {
                $total_test_duration = intval($test_duration) * intval($quantity);
                if ($test_duration == 1) {
                    $userPackage->test_days_eligible = $userPackage->test_days_eligible + $total_test_duration;
                } else {
                    if (!$userPackage->test_started) {
                        $userPackage->test_started = Carbon::now();
                    }
                    $userPackage->test_enabled = true;
                    if (!$userPackage->test_valid_until) {
                        $userPackage->test_valid_until = Carbon::now()->addDays($total_test_duration);
                    } else {
                        $userPackage->test_valid_until = $userPackage->test_valid_until->addDays($total_test_duration);
                    }
                }
            }

            $live_duration = $package['live_duration'];

            if ($live_duration > 0) {
                $total_duration = intval($live_duration) * intval($quantity);
                if ($package['exchanges'] == -1) {
                    $userPackage->enabled = 1;
                    $userPackage->all_live_enabled = 1;

                    if (!$userPackage->all_live_started) {
                        $userPackage->all_live_started = Carbon::now();
                    }
                    if (!$userPackage->all_live_valid_until) {
                        $userPackage->all_live_valid_until = Carbon::now()->addDays($total_duration);
                    } else {
                        $userPackage->all_live_valid_until = $userPackage->all_live_valid_until->addDays($total_duration);
                    }
                }


                if ($package['exchanges'] == 1) {

                    if ($userPackage->id)
                        $userPackageExchange = UserPackageExchange::where('exchange', $exchange)->where('user_package_id', $userPackage->id)->first();
                    if (!$userPackage->id || !$userPackageExchange) {
                        $userPackageExchange = new UserPackageExchange();
                        $userPackageExchange->user_id = $user_id;
                        $userPackageExchange->exchange = $exchange;
                    }

                    if ($package_id != BillingPackage::Package5) {
                        $userPackageExchange->live_enabled = 1;
                        if (!$userPackageExchange->live_started) {
                            $userPackageExchange->live_started = Carbon::now();
                        }

                        if (!$userPackageExchange->live_valid_until) {
                            $userPackageExchange->live_valid_until = Carbon::now()->addDays($total_duration);
                        } else {
                            $userPackageExchange->live_valid_until = $userPackageExchange->live_valid_until->addDays($total_duration);
                        }
                    } else {
                        $userPackageExchange->active_days_eligible = $userPackageExchange->active_days_eligible + $total_duration;
                    }

                    if (!$userPackageExchange->user_package_id) {
                        $userPackage->save();
                        $userPackageExchange->user_package_id = $userPackage->id;
                    }
                    $userPackageExchange->save();
                }


            }

            $userPackage->save();

            return $userPackage->load('exchanges');
        } catch (\Exception $ex) {
            Log::error($ex);
            return false;
        }
    }

    public function validate(Request $request)
    {

        $cp_merchant_id = config('services.coinpayments.merchant_id');
        $cp_ipn_secret = config('services.coinpayments.ipn_secret');

        if (!$request->input('ipn_mode') || $request->input('ipn_mode') != 'hmac') {
            $error = $this->errorAndDie($request, 'IPN Mode is not HMAC');
            if (!$error)
                return false;
        }

        if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
            $error = $this->errorAndDie($request, 'No HMAC signature sent.');
            if (!$error)
                return false;
        }

        if ($request === FALSE || empty($request)) {
            $error = $this->errorAndDie($request, 'Error reading POST data');
            if (!$error)
                return false;
        }

        if (!$request->input('merchant') || $request->input('merchant') != trim($cp_merchant_id)) {
            $error = $this->errorAndDie($request, 'No or incorrect Merchant ID passed');
            if (!$error)
                return false;
        }

        $requestString = file_get_contents('php://input');
        if ($requestString === FALSE || empty($requestString)) {
            $error = $this->errorAndDie($request, 'Error reading POST data');
            if (!$error)
                return false;
        }

        $hmac = hash_hmac("sha512", $requestString, trim($cp_ipn_secret));
        if (!isset($_SERVER['HTTP_HMAC']) && $hmac != $_SERVER['HTTP_HMAC']) {
            $error = $this->errorAndDie($request, 'HMAC signature does not match. HC: ' . $hmac);
            if (!$error)
                return false;
        }

        // HMAC Signature verified at this point, load some variables.

        $currency1 = $request->input('currency1');
        $status = intval($request->input('status'));

        $quantity = intval($request->input('quantity'));


//        $txn_id = $request->input('txn_id');
//        $item_name = $request->input('item_name');
//        $item_number = $request->input('item_number');
//        $amount1 = floatval($request->input('amount1'));
//        $amount2 = floatval($request->input('amount2'));
//        $currency2 = $request->input('currency2');
//        $status_text = $request->input('status_text');

        //depending on the API of your system, you may want to check and see if the transaction ID $txn_id has already been handled before at this point

        // Check the original currency to make sure the buyer didn't change it.
        if ($currency1 != 'BTC') {
            $error = $this->errorAndDie($request, 'Original currency mismatch!');
            if (!$error)
                return false;
        }

        // Check amount against order total
//        if ($amount1 < $price) {
//            $error = $this->errorAndDie($request, 'Amount is less than order total!');
//            if (!$error)
//                return false;
//        }

        if ($status >= 100 || $status == 2) {
            // payment is complete or queued for nightly payout, success
            return 'complete';
        } else if ($status < 0) {
            //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
            return 'failed';
        } else {
            //payment is pending, you can optionally add a note to the order page
            return 'pending';
        }
    }

    private function errorAndDie($request, $error_msg)
    {
        $report = 'Error: ' . $error_msg . "\n\n";
        $report .= "POST Data\n\n";
        foreach ($request->input() as $k => $v) {
            $report .= "|$k| = |$v|\n";
        }
        Log::error($report);
        die($report);
        return false;
    }

    public function hasEmailsRemaining($user_id)
    {
        $userPackage = UserPackage::with('user')->where('user_id', $user_id)->first();
        if ($userPackage && $userPackage->user && $userPackage->user->is_dev)
            return true;
        if (!$userPackage) {
            $user = User::find($user_id);
            if ($user->is_dev)
                return true;
        }
        return ($userPackage && $userPackage->email_max > $userPackage->email_used);
    }

    public function hasSmsRemaining($user_id)
    {
        $userPackage = UserPackage::with('user')->where('user_id', $user_id)->first();
        if ($userPackage && $userPackage->user && $userPackage->user->is_dev)
            return true;

        if (!$userPackage) {
            $user = User::find($user_id);
            if ($user->is_dev)
                return true;
        }
        return ($userPackage && $userPackage->sms_max > $userPackage->sms_used);
    }

    public function smsSent($user_id)
    {
        $userPackage = UserPackage::with('user')->where('user_id', $user_id)->first();
        if ($userPackage) {
            if ($userPackage && $userPackage->user && $userPackage->user->is_dev)
                return;

            $userPackage->sms_used = $userPackage->sms_used + 1;
            $userPackage->save();
        }
    }

    public function emailSent($user_id)
    {
        $userPackage = UserPackage::with('user')->where('user_id', $user_id)->first();
        if ($userPackage) {
            if ($userPackage && $userPackage->user && $userPackage->user->is_dev)
                return;

            $userPackage->email_used = $userPackage->email_used + 1;
            $userPackage->save();
        }
    }


    public function hasActivePackage(Auth $auth, $exchange, $test = false)
    {
        if ($auth->user()->is_dev == true)
            return true;

        $userPackage = UserPackage::where('user_id', $auth->user()->id)->with('exchanges')->first();
        if (!$userPackage)
            return false;

        if ($test) {
            return !$userPackage->test_expired;
        }

        $active = !$userPackage->all_expired;
        if ($active)
            return true;

        foreach ($userPackage->exchanges as $value) {
            if ($value->exchange == $exchange && !$value->live_expired)
                return true;
        }

        return false;
    }
}