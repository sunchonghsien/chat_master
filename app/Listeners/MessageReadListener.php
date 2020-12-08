<?php

namespace App\Listeners;

use App\Events\MessageRead;
use App\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class MessageReadListener
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
     * @param  MessageRead  $event
     * @return void
     */
    public function handle(MessageRead $event)
    {
        if(!$event->count){
            Message::query()->where(['from' => $event->from, 'to' => $event->to])->update(['is_read' => 1]);
        }
        Redis::hSet("messageRead:$event->to:$event->from",'count',$event->count);
    }
}
