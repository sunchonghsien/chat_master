<?php

namespace App\Http\Controllers;

use App\Events\ReceiveMessage;
use App\Events\SendMessage;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Pusher\Pusher;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // $users = User::where('id', '!=', Auth::id())->get();
        $users = DB::select("select users.id, users.name, users.avatar, users.email, count(is_read) as unread 
        from users LEFT  JOIN  messages ON users.id = messages.from and is_read = 0 and messages.to = " . Auth::id() . "
        where users.id != " . Auth::id() . " 
        group by users.id, users.name, users.avatar, users.email");

        return view('home', ['users' => $users]);
    }

    public function getMessage($user_id)
    {
        $my_id = Auth::id();
        Message::where(['from' => $user_id, 'to' => $my_id])->update(['is_read' => 1]);
        $messages = Message::where(function ($query) use ($user_id, $my_id) {
            $query->where(['from' => $my_id, 'to' => $user_id]);
        })->orWhere(function ($query) use ($user_id, $my_id) {
            $query->where(['from' => $user_id, 'to' => $my_id]);
        })->get();

        return view('messages.index', ['messages' => $messages]);
    }

    public function room_send()
    {
        $to      = request('to');
        $is_room = request('is_room');
        $my_id   = Auth::id();
        Redis::set("friend_room:$my_id", json_encode(['is_room' => $is_room, 'to' => $to]));
    }

    public function sendMessage()
    {
        $from    = Auth::id();
        $to      = request('receiver_id');
        $message = request('message');
        $time    = request('time');

        $data          = new Message();
        $data->from    = $from;
        $data->to      = $to;
        $data->message = $message;
        $data->is_read = 0;
        if ($time) {
            $data->created_at = $time;
        }
        $data->save();
        $data->refresh();
        event(new SendMessage(['msg' => $data->message, 'time' => $data->created_at], $from, $to));
    }
}
