<?php

namespace App\Listeners;

use App\Events\MessageRead;
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
        Redis::hSet("messageRead:$event->to:$event->from",'count',$event->count);
    }
}
