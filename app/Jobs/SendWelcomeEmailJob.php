<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJob extends Job
{
    private $hash;
    private $to;

    public function __construct($to, $hash)
    {
        $this->hash = $hash;
        $this->to = $to;
    }

    public function handle()
    {
        Mail::send('emails.welcome', ['hash' => $this->hash], function ($msg) {
            $msg->subject("Welcome to XChangeRate");
            $msg->to([$this->to]);
        });
    }
}