<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BroadcastEvent  implements ShouldBroadcastNow
{
    use SerializesModels;

    public $data;

    public function __construct()
    {
        $this->data = array(
            'power'=> '10'
        );
    }

    public function broadcastOn()
    {
        return ['test-channel'];
    }
}
