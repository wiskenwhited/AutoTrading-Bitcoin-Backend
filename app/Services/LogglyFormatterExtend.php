<?php

namespace App\Services;

use App\Auth\Auth;
use Exception;
use Monolog\Formatter\LogglyFormatter;

class LogglyFormatterExtend extends LogglyFormatter
{
    public function format(array $record)
    {
        if ($record['message'] instanceof Exception) {
            $record['message'] = $record['message']->getMessage();
        }
        if (isset($record["datetime"]) && ($record["datetime"] instanceof \DateTime)) {
            $record["timestamp"] = $record["datetime"]->format("Y-m-d\TH:i:s.uO");
            // TODO 2.0 unset the 'datetime' parameter, retained for BC
        }
        // Log current user by default if available
        $auth = app(Auth::class);
        if ($user = $auth->user()) {
            $record['auth'] = ['user_id' => $user->id];
        }

        return parent::format($record);
    }
}
