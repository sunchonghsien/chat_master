<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $to;
    public $count;
    public $msg;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($to)
    {
        $this->to  = $to;
    }


    public function broadcastWhen()
    {
        return $this->count>0;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('receive-messages-' . Auth::id());
    }

    public function broadcastAs()
    {
        //命名推播的事件
        return 'messageReadEvent';

    }

    public function broadcastWith()
    {
        return ['count' => $this->count];
    }
}
