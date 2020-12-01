<?php

namespace App\Listeners;

use App\Events\MessageRead;
use App\Events\ReceiveMessage;
use App\Events\SendMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class SendMessageListener
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
     * @param  SendMessage $event
     * @return void
     */
    public function handle(SendMessage $event)
    {
        $name = 'friend_room:' . $event->to;
        $item = [];
        if (Redis::exists($name)) {
            $item     = json_decode(Redis::get($name) ?? '{}', true);
        }

        $msg_name = "messageRead:$event->to:$event->from";
        $count    = !Redis::hExists($msg_name, 'count') ? 1 : Redis::hGet($msg_name, 'count');
        if ((isset($item['is_room'])&&$item['is_room'] != 'out') && (isset($item['to'])&&$item['to'] == Auth::id())) {
            $count = 0;
        } else {
            if ($count != '99+') {
                $count++;
            } else {
                if ($count > 99) {
                    $count = '99+';
                }
            }
        }

        $send_key = 'historical_record:'.($event->from>$event->to?"{$event->to}:{$event->from}":"{$event->from}:{$event->to}");
        if(Redis::hLen($send_key)>10){
           $list = Redis::hGetAll("$send_key:*");
            print_r($list);
        }else{
           $mic = microtime(true)*10000;
            Redis::hSet("$send_key:$mic",'message',$event->data['msg']);
            Redis::hSet("$send_key:$mic",'is_read',0);
            Redis::hSet("$send_key:$mic",'created_at',$event->data['time']);
        }
        event(new MessageRead($event->from, $event->to, $count));
        event(new ReceiveMessage($event->data['msg'], $event->from, $event->to));
    }
}
