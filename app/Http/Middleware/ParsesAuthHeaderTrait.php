<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

trait ParsesAuthHeaderTrait
{
    protected function parseHeader(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (! $authHeader) {
            return null;
        }
        $jwt = explode(' ', $authHeader);
        if (count($jwt) == 2) {
            if ($jwt[0] == 'Bearer') {
                return $jwt[1];
            }
        }

        return null;
    }
}