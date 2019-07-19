<?php

namespace App\Auth;

use App\Models\ResetPasswordToken;
use App\Models\User;
use App\Models\WhitelistedToken;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Auth
{
    protected $jwt;
    protected $user;
    protected $key;
    protected $allowed_algs;

    public function __construct()
    {
        $this->key = env('JWT_SECRET', '4Pe6nPWXE5kNiyUyAaUirDn0MdQCNfBx');
        $this->allowed_algs = ['HS256'];
    }

    public function check()
    {
        return $this->user ? true : false;
    }

    public function checkRole($role)
    {
        return $this->user && $this->user->role_id == $role;
    }

    public function user()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getJwt()
    {
        return $this->jwt;
    }

    /**
     * @param mixed $jwt
     * @param Request $request
     * @param mixed $checkRole
     * @return bool
     */
    public function setJwt($jwt, Request $request, $checkRole = false)
    {
        $this->jwt = $jwt;
        $decoded = $this->validateAndDecodeJwt($jwt, $request);
        if (!$decoded) {
            return false;
        }
        $this->user = User::find($decoded->sub);

        if (!$checkRole)
            return $this->check();

        return $this->checkRole($checkRole);
    }

    protected function validateAndDecodeJwt($jwt, Request $request)
    {
        $whitelistedJwt = WhitelistedToken::byToken($jwt)->first();
        if (!$whitelistedJwt) {
            return false;
        }

        // If token was last used more than "auth.inactivity_timeout_minutes" ago, we invalidate it
        $lastUsed = $whitelistedJwt->updated_at ?: $whitelistedJwt->created_at;
        $lastUsedMax = Carbon::now()->subMinutes(config('auth.inactivity_timeout_minutes'));
        if ($lastUsedMax->greaterThan($lastUsed)) {
            $whitelistedJwt->delete();

            return false;
        }
        // We only update the whitelisted token if a request is not a polling request
        if (is_null($request->query('polling'))) {
            $whitelistedJwt->touch();
        }

        $decoded = JWT::decode($jwt, $this->key, $this->allowed_algs);
        if ($decoded->exp && $decoded->exp < time()) {
            return false;
        }
        if ($decoded->sub != $whitelistedJwt->user_id) {
            return false;
        }

        return $decoded;
    }

    public function generateJwt(User $user, $remember = false)
    {
        $expiryDate = !$remember ? strtotime('+1 month') : null;

        $token = [
            'sub' => $user->id,
            'iss' => null,
            'exp' => $expiryDate,
            'aud' => null,
            'iat' => time(),
            'nbf' => time(),
            'jti' => null
        ];
        $jwt = JWT::encode($token, $this->key);

        WhitelistedToken::create([
            'user_id' => $user->id,
            'token' => $jwt
        ]);

        return $jwt;
    }

    public function invalidateJwt($jwt = null)
    {
        if (!$jwt) {
            $jwt = $this->jwt;
        }
        WhitelistedToken::byToken($jwt)->delete();
    }

    public function createResetPasswordToken(User $user)
    {
        do {
            $token = hash_hmac('sha256', Str::random(40), config('app.key'));
        } while (ResetPasswordToken::where('token', $token)->exists());

        $token = ResetPasswordToken::create([
            'user_id' => $user->id,
            'token' => $token
        ]);

        return $token;
    }

    public function resetPasswordWithToken(ResetPasswordToken $token, $password)
    {
        $token->user->password = Hash::make($password);
        $token->user->save();

        $token->delete();
    }
}