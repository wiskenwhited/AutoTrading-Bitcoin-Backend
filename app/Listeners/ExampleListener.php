<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent $event
     * @return void
     */
    public function handle(ExampleEvent $event)
    {
//        $user = new User();
//        $user->name = 'name';
//        $user->email = 'email';
//        $user->password = 'a';
//        $user->country = 'country';
//        $user->city = 'city';
//        $user->phone = 'phone';
//        $user->currency = 'currency';
//        $user->verification_code = md5(time() . rand(1, 99999));
//        $user->save();
    }
}
