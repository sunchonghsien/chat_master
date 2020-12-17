<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\SendMessage;
use App\Helper\Constant;
use App\Message;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

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
        $receiver_id = request('receiver_id');
        $my_id       = Auth::id();
        $send_key    = 'historical_record:' . ($receiver_id > $my_id ? "$my_id:$receiver_id" : "$receiver_id:$my_id");
        $page        = request('page');
        $size        = Constant::PAGE;
        $parent_page = ($page - 1) * $size;
        $len         = Redis::lLen($send_key);
        $page        *= $size;
        $index       = bcsub($len, $page);
        $page_size   = bcsub($len, $parent_page)-1;
        $messages = collect();
        if ($page_size > 0) {
            $messages->wrap(Redis::lRange($send_key, $index, $page_size))
                ->sortKeysDesc()->values();

            if ($messages->isNotEmpty()) {
                $messages->transform(function ($value){
                    return json_decode($value, true);
                });
            }
        }

        if ($messages->isEmpty()) {
            $messages = Message::query()->where(function ($query) use ($my_id, $receiver_id) {
                $query->where(['from' => $my_id, 'to' => $receiver_id])->orWhere([
                    'from' => $receiver_id,
                    'to'   => $my_id
                ]);
            })->offset($parent_page)->limit($size)->orderByDesc('id')
                ->get(['message', 'created_at', 'from', 'to'])
            ;
        }

        return $messages;
    }

    public function getMessage($user_id)
    {
        $my_id    = Auth::id();
        $send_key = 'historical_record:' . ($user_id > $my_id ? "$my_id:$user_id" : "$user_id:$my_id");
        $len      = Redis::lLen($send_key);
        $index    = bcsub($len, Constant::PAGE);
        $messages = Redis::lRange($send_key, $index < 0 ? 0 : $index, $len);
        foreach ($messages as &$item) {
            $item = json_decode($item, true);
        }

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
