<?php

namespace App\Helpers;

use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioHelper
{
    public function sendText($to, $message)
    {

        $sid = config('twilio.twilio.sid');
        $token = config('twilio.twilio.token');
        $from = config('twilio.twilio.from');

        $client = new Client($sid, $token);
        $to = "+" . (ltrim($to, '+'));

        try {

            $client->messages->create($to,
                array(
                    'from' => $from,
                    'body' => $message
                )
            );

            return true;
        } catch (TwilioException $e) {
            Log::warning("Twilio exception occurred", [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

}