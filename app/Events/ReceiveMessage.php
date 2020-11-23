<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiveMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $msg;
    private $to;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($msg,$to)
    {
        $this->msg=$msg;
        $this->to=$to;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('receive-messages-'.$this->to);
    }

    public function broadcastWith()
    {
        return ['message' => $this->msg];
    }
}
