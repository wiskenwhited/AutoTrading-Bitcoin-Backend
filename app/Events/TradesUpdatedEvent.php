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

class TradesUpdatedEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $trades;
    public $user_id;

    public function __construct($trades, $user_id)
    {
        $this->trades = $trades;
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        return ['trades'];

//        return new PresenceChannel('trades');
//        return new PrivateChannel('trades.'.$this->user_id);
    }


}
