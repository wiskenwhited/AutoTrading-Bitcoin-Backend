<?php

namespace App\Http\Controllers\Traits;

trait HandlesTradingBotResponsesTrait
{
    protected function processTradingBotResponse(array $response)
    {
        $status = 200;
        if ($error = array_get($response, 'error')) {
            $status = array_get($response, 'status', 500);
            $response = $error;
        }

        return [$response, $status];
    }
}