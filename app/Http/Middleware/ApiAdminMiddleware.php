<?php

namespace App\Http\Middleware;

use App\Auth\Auth;
use App\Models\User;
use Closure;

class ApiAdminMiddleware
{
    use ParsesAuthHeaderTrait;

    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $jwt = $this->parseHeader($request);

        if (!$jwt || !$this->auth->setJwt($jwt, $request, User::AdminRole)) {
            return response()->json('Unauthorized', 401);
        }

        return $next($request);
    }
}
