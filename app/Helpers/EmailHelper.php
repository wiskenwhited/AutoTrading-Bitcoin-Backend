<?php

namespace App\Helpers;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class EmailHelper
{
    public static function SendWelcomeEmail($to, $hash)
    {
        //Queue::push(new SendWelcomeEmailJob($to, $hash));
        (new SendWelcomeEmailJob($to, $hash))->handle();
    }

    public static function SendBuyWatchlistEmail($rulesMet, $watchlist, $user, $matchDate)
    {
        try {
            $data = [
                'name' => $user->name,
                'watchlist' => $watchlist,
                'rulesMet' => $rulesMet,
                'matchDate' => $matchDate
            ];
            $coin = $watchlist->coin;

            Mail::send('emails.watchlist_buy', $data, function ($msg) use ($user, $coin) {
                $msg->subject("Your added watchlist " . $coin . " matched your CMB settings.");
                $msg->to($user->email);
            });

            return true;
        } catch (\Exception $ex) {
            Log::error($ex);
            return false;
        }
    }

    public static function SendSellWatchlistEmail($watchlist, $user, $matchDate, $trade)
    {
        try {
            $data = [
                'name' => $user->name,
                'watchlist' => $watchlist,
                'trade' => $trade,
                'matchDate' => $matchDate
            ];
            $coin = $watchlist->coin;
            Mail::send('emails.watchlist_sell', $data, function ($msg) use ($user, $coin) {
                $msg->subject("CMB just made a sale of " . $coin);
                $msg->to($user->email);
            });

            return true;
        } catch (\Exception $ex) {
            Log::error($ex);
            return false;
        }
    }

    public static function sendResetPasswordEmail(User $user, $token)
    {
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $token->token
        ];

        Mail::send('emails.reset_password', $data, function ($msg) use ($user) {
            $msg->subject("Reset password requested");
            $msg->to($user->email);
        });

        return true;
    }

    public static function contactUs($data)
    {
        try {
            Mail::send('emails.contact_us_received', $data, function ($msg) use ($data) {
                $msg->subject("We have received your message");
                $msg->to($data['email'], $data['name']);
            });

            Mail::send('emails.contact_us_admin', $data, function ($msg) use ($data) {
                $msg->subject($data['subject']);
                $msg->to('cs@xchangerate.io');
            });
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }


    public static function sendVerificationCode(User $user, $code)
    {
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'code' => $code
        ];

        Mail::send('emails.verification_code', $data, function ($msg) use ($user) {
            $msg->subject("Verification code");
            $msg->to($user->email);
        });

        return true;
    }
}