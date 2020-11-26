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

        event(new MessageRead($event->from, $event->to, $count));
        event(new ReceiveMessage($event->data['msg'], $event->from, $event->to));
    }
}
