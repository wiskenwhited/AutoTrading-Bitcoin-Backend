<?php

namespace App\Http\Controllers\Auth;

use App\Auth\Auth;
use App\Helpers\EmailHelper;
use App\Http\Controllers\ApiController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends ApiController
{
    /**
     * @var Auth
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function postRegistration(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'country' => 'required|max:255',
            'city' => 'required|max:255',
            'phone' => 'required|numeric',
            'currency' => 'required',
            'referral' => 'exists:users,referral',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->country = $request->country;
            $user->city = $request->city;
            $user->phone = $request->phone;
            $user->currency = $request->currency;
            $user->verification_code = md5(time() . rand(1, 99999));
            if ($request->input('referral')) {
                $referral = User::where('referral', $request->input('referral'))->first();
                if($referral)
                    $user->mentor_id = $referral->id;
            }
            $user->save();
            $user->setVisible(['name', 'email', 'phone']);

            EmailHelper::SendWelcomeEmail($user->email, $user->verification_code);

            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            Log::error("Registration error occurred", [
                'message' => $e->getMessage()
            ]);

            return response()->json($e->getMessage(), 500);
        }
    }

    public function verify(Request $request, $verificationCode)
    {
        $user = User::whereVerified(false)->whereVerificationCode($verificationCode)->first();

        if ($user) {
            $user->verified = true;
            $user->save();

            return response()->json();
        }

        return response()->json('Verification failed', 500);
    }

    public function fa2(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'email' => 'required',
            'password' => 'required',
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        /** @var User $user */
        $user = User::whereEmail($request->email)->first();


        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->enabled_2fa == User::TwoFANone || $user->confirmed_2fa) {
                return response()->json('Please go back to the login screen', 401);
            }
            if ($user->enabled_2fa == User::TwoFAGA) {
                $google2fa = new Google2FA();
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                if (!$valid)
                    return response()->json('Invalid code', 401);
            } else {
                if ($user->code_2fa != $request->code)
                    return response()->json('Invalid code', 401);
            }
            $user->confirmed_2fa = true;
            $user->last_login_ip = $request->ip();
            $user->last_login_date = Carbon::now();
            $user->save();
            $user->append(['user_role', 'exit_strategy_set']);
            $user->setVisible([
                'name',
                'email',
                'phone',
                'currency',
                'country',
                'city',
                'user_role',
                'exit_strategy_set',
                'is_dev',
            ]);

            $jwt = $this->auth->generateJwt($user, (bool)($request->input('remember_me')));

            return response()->json([
                'user' => $user,
                'api_key' => $jwt,
            ], 201);
        }

        return response()->json('Invalid credentials', 401);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $message = null;

        /** @var User $user */
        $user = User::whereEmail($request->email)->first();
        if ($user && !$user->verified) {
            return response()->json('Account not verified', 401);
        }

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->enabled_2fa != 0) {
                $sent = $user->sendVerificationCode();

                if ($sent == 'sent') {
                    if ($user->enabled_2fa == User::TwoFAGA)
                        $message = "Please use your Google Authenticator to verify account";
                    if ($user->enabled_2fa == User::TwoFAEmail)
                        $message = 'Verification code has been sent to your email';
                    if ($user->enabled_2fa == User::TwoFASMS)
                        $message = 'Verification code has been sent to your phone';
                    return response()->json([
                        'enabled_2fa' => true,
                        'message' => $message
                    ]);
                } elseif ($sent == 'none') {
                    $type = $user->enabled_2fa == User::TwoFAEmail ? "emails" : "sms";
                    $message = 'Not enough ' . $type . ' left in your package for your 2FA authentication';
                }
            }

            $user->last_login_ip = $request->ip();
            $user->last_login_date = Carbon::now();
            $user->save();
            $user->append(['user_role', 'exit_strategy_set']);
            $user->setVisible([
                'name',
                'email',
                'phone',
                'currency',
                'country',
                'city',
                'user_role',
                'exit_strategy_set',
                'is_dev'
            ]);

            $jwt = $this->auth->generateJwt($user, (bool)($request->input('remember_me')));

            return response()->json([
                'user' => $user,
                'api_key' => $jwt,
                'message' => $message
            ], 201);
        }

        return response()->json('Invalid credentials', 401);
    }

    public function logout()
    {
        $this->auth->invalidateJwt();

        return response()->json();
    }
}