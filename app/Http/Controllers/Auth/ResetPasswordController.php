<?php

namespace App\Http\Controllers\Auth;

use App\Auth\Auth;
use App\Helpers\EmailHelper;
use App\Http\Controllers\ApiController;
use App\Models\ResetPasswordToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ResetPasswordController extends ApiController
{
    /**
     * @var Auth
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function postReset(Request $request, Auth $auth)
    {
        $validator = Validator::make($request->input(), [
            'email' => 'required|email|exists:users,email'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('email', $request->get('email'))->first();
        $token = $auth->createResetPasswordToken($user);

        EmailHelper::sendResetPasswordEmail($user, $token);

        return response()->json([], 201);
    }

    public function postConfirmation(Request $request, Auth $auth)
    {
        $validator = Validator::make($request->input(), [
            'token' => 'required|exists:reset_password_tokens,token',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $token = ResetPasswordToken::findByToken($request->get('token'));
        $auth->resetPasswordWithToken($token, $request->get('password'));

        return response()->json([
            'email' => $token->user->email
        ], 200);
    }
}