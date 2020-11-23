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

class SendMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $data;
    private $to;
    private $from;


    /**
     * Create a new event instance.
     *
     * @param $data
     * @param $from
     * @param $to
     */
    public function __construct($data,$from, $to)
    {
        $this->data = $data;
        $this->to   = $to;
        $this->from = $from;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("send-messages-$this->to-$this->from");
    }

    public function broadcastAs()
    {
        //命名推播的事件
        return 'sendMessageEvent';

    }

    public function broadcastWith()
    {
        return ['item' => $this->data,'from'=>$this->from];
    }
}
