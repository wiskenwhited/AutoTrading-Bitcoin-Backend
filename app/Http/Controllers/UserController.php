<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\User;
use App\Views\UserView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PragmaRX\Google2FA\Google2FA;

class UserController extends ApiController
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
        return response()->json($this->auth->user());
    }

    public function update(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->input(), [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'min:6|confirmed',
            'country' => 'required|max:255',
            'city' => 'required|max:255',
            'phone' => 'required|numeric',
            'currency' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->fill($request->only([
            'name',
            'email',
            'country',
            'city',
            'phone',
            'currency',
        ]));
        if ($password = $request->get('password')) {
            $user->password = Hash::make($password);
        }
        $user->save();
        $view = new UserView();

        return response()->json($view->render($user));
    }

    public function qr()
    {
        $user = $this->auth->user();
        $google2fa = new Google2FA();
        $google2fa_secret = $google2fa->generateSecretKey();

        $google2fa_url = $google2fa->getQRCodeGoogleUrl(
            'XChangeRate',
            $user->email,
            $google2fa_secret
        );

        return response()->json([
            'secret' => $google2fa_secret,
            'url' => $google2fa_url
        ]);
    }

    public function save2FA(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->input(), [
            'enabled_2fa' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if ($request->input('enabled_2fa') == User::TwoFAGA)
            return response()->json('Please enable Google Authenticator', 422);

        $user->enabled_2fa = $request->input('enabled_2fa');
        $user->save();

        return response()->json($user);
    }

    public function saveQr(Request $request)
    {
        $user = $this->auth->user();

        $validator = Validator::make($request->input(), [
            'secret' => 'required',
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($request->secret, $request->code);
        if (!$valid)
            return response()->json('Invalid code', 401);

        $user->enabled_2fa = User::TwoFAGA;
        $user->google2fa_secret = $request->secret;
        $user->save();

        return response()->json($user);
    }

    public function saveWallet(Request $request)
    {
        $user = $this->auth->user();

        $validator = Validator::make($request->input(), [
            'wallet_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->wallet_id = $request->wallet_id;
        $user->save();

        return response()->json($user);
    }
}