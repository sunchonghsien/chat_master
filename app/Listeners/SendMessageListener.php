<?php

namespace App\Listeners;

use App\Events\MessageRead;
use App\Events\ReceiveMessage;
use App\Events\SendMessage;

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
     * @param  SendMessage  $event
     * @return void
     */
    public function handle(SendMessage $event)
    {
        event(new ReceiveMessage($event->data['msg'],$event->from,$event->to));
        event(new MessageRead($event->to));
    }
}
