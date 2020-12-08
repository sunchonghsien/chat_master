<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\SendMessage;
use App\Message;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

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
        $users = User::query()->where('id', '!=', Auth::id())->get()->transform(function ($user) {

            $name = "messageRead:" . Auth::id() . ":$user->id";
            $item = ['count' => 0, 'msg' => ''];
            if (Redis::hKeys($name)) {
                $item = Redis::hGetAll($name);
            }
            $msg            = Arr::get($item, 'msg', '');
            $user->unread   = Arr::get($item, 'count', 0);
            $user->last_msg = !empty($msg) ? $msg : $user->email;
            return $user;
        });
//        $users = DB::select("select users.id, users.name, users.avatar, users.email, count(is_read) as unread
//        from users LEFT  JOIN  messages ON users.id = messages.from and is_read = 0 and messages.to = " . Auth::id() . "
//        where users.id != " . Auth::id() . "
//        group by users.id, users.name, users.avatar, users.email");
        Redis::del('friend_room:' . Auth::id());
        return view('home', ['users' => $users]);
    }

    public function historical()
    {
        $page        = request('page');
        $size        = 10;
        $page_index  = ($page - 1) * $size;
        $page_size   = $page * $size;
        $receiver_id = request('receiver_id');
        $my_id       = Auth::id();
        $send_key    = 'historical_record:' . ($receiver_id > $my_id ? "$my_id:$receiver_id" : "$receiver_id:$my_id");
        $messages    = Redis::lRange($send_key, $page_index, $page_size);
        if (!empty($messages)) {
            foreach ($messages as &$item) {
                $item = json_decode($item, true);
            }
        } else {
            $messages = Message::query()->where(function ($query) use ($my_id, $my_id) {
                $query->where(['from' => $my_id, 'to' => $my_id]);
            })->orWhere(function ($query) use ($my_id, $my_id) {
                $query->where(['from' => $my_id, 'to' => $my_id]);
            })->limit("$page_index,$page_size")->get();
        }
        print_r($messages);
        exit;
    }

    public function getMessage($user_id)
    {
        $my_id    = Auth::id();
        $send_key = 'historical_record:' . ($user_id > $my_id ? "$my_id:$user_id" : "$user_id:$my_id");
        $messages = Redis::lRange($send_key, 0, 10);
        foreach ($messages as &$item) {
            $item = json_decode($item, true);
        }
//        $messages = Message::query()->where(function ($query) use ($user_id, $my_id) {
//            $query->where(['from' => $my_id, 'to' => $user_id]);
//        })->orWhere(function ($query) use ($user_id, $my_id) {
//            $query->where(['from' => $user_id, 'to' => $my_id]);
//        })->limit('5')->get();

        event(new MessageRead($user_id, $my_id));
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
        event(new SendMessage(['msg' => $message, 'time' => $time], $from, $to));
    }
}
