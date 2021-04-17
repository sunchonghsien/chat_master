<?php

namespace App\Listeners;

use App\Events\MessageRead;
use App\Events\ReceiveMessage;
use App\Events\SendMessage;
use App\Helper\RedisKeyName;
use App\Message;
use App\User;
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
        $name = RedisKeyName::friendRoom($event->to);
        $item = [];
        if (Redis::exists($name)) {
            $item = json_decode(Redis::get($name) ?? '{}', true);
        }

        $msg_name = RedisKeyName::messageRead($event->to,$event->from);
        $count    = !Redis::hExists($msg_name, 'count') ? 1 : Redis::hGet($msg_name, 'count');
        if ((isset($item['is_room']) && $item['is_room'] != 'out') && (isset($item['to']) && $item['to'] == Auth::id())) {
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
        Redis::hSet($msg_name,'count',$count);

        $send_key = RedisKeyName::historicalRecord($event->to,$event->from);

        if (Redis::lLen($send_key) >= 100) {
            Redis::rPop($send_key);
        }

        $data = [
            'message'    => $event->data['msg'],
            'created_at' => $event->data['time'],
            'from'       => $event->from,
            'to'         => $event->to
        ];

        Redis::rPush($send_key, json_encode($data));
        Message::query()->insert($data);

        event(new MessageRead($event->from,$event->to, $count));
        event(new ReceiveMessage($event->data['msg'], $event->from, $event->to));
    }
}
