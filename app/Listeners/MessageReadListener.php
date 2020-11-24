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
        $item = json_decode(Redis::get('friend_room:'.$event->to)??'{}',true);
        if(isset($item['is_room'])){
            $event->count = Redis::hGet("messageRead:$event->to:{$item['to']}",'count');
            if($item['is_room']!='out'&&$item['to']==Auth::id()){
                $event->count = 0;
            }else{
                $event->count++;
            }
            Redis::hSet("messageRead:$event->to:{$item['to']}",'count',$event->count);
        }

    }
}
