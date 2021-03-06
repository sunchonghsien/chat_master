<?php

namespace App\Listeners;

use App\Events\ReceiveMessage;
use App\Helper\RedisKeyName;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class ReceiveMessageListener
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
     * @param  ReceiveMessage  $event
     * @return void
     */
    public function handle(ReceiveMessage $event)
    {
        Redis::hSet(RedisKeyName::messageRead($event->to,$event->from),'msg',$event->msg);
        Redis::hSet(RedisKeyName::messageRead($event->from,$event->to),'msg',$event->msg);
    }
}
