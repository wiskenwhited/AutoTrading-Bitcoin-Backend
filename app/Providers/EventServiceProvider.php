<?php

namespace App\Providers;

use App\Events\TradesUpdatedEvent;
use App\Listeners\TradesUpdatedListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
        'App\Events\TradesUpdatedEvent' => [
            'App\Listeners\TradesUpdatedListener',
        ],
    ];
}
