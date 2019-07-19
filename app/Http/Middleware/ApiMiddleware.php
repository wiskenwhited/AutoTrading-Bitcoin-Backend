<?php

namespace App\Http\Middleware;

use App\Auth\Auth;
use Closure;
use Illuminate\Support\Facades\Log;

class ApiMiddleware
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
        //Log::info($request->fullUrl());

        if (! $request->isJson() && ! $request->ajax()) {
            return response()->json('Unsupported Media Type', 415);
        }

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            // Parse JWT even if accessing non-auth routes so we can work with signed-in user
            $jwt = $this->parseHeader($request);
            if ($jwt) {
                $this->auth->setJwt($jwt, $request);
            }
            // Pass the request to the next middleware
            $response = $next($request);
        }
        $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE');
        $response->header('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));
        $response->header('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
