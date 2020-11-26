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

class ReceiveMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $msg;
    public $to;
    public $from;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($msg,$from,$to)
    {
        $this->msg=$msg;
        $this->from =$from;
        $this->to=$to;
    }

    public function broadcastAs()
    {
        //命名推播的事件
        return 'receiveMessageEvent';

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
        return ['message' => $this->msg,'from'=>$this->from];
    }
}
