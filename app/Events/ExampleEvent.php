<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExampleEvent extends Event implements ShouldBroadcast
{
    public $event;
//
    public function __construct($event)
    {
        $this->event = $event;
    }
    public function broadcastOn()
    {

        return new PrivateChannel('order.1');

    }

    public function broadcastAs()
    {
        return 'trade.updated';
    }
}
