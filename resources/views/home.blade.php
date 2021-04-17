@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- List group -->
                <div class="list-group list-group-horizontal" id="controldemo">
                    <a class="list-group-item list-group-item-action col-2" data-toggle="list" href="#Buddy">好友</a>
                    <a class="list-group-item list-group-item-action col-2 active" data-toggle="list" href="#chat-with">聊天</a>
                </div>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane " id="Buddy">
                        <input class="form-control form-control-flush" id="search_friend_name" placeholder="搜索好友">
                        <div class="user-wrapper">
                            <ul class="users" id="test">
                                @if(!empty($users))
                                    @foreach($users as $user)
                                        <li class="user user-info" id="{{ $user->id }}">
                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="{{ $user->avatar }}" alt="" class="media-object">
                                                </div>

                                                <div class="media-body">
                                                    <p class="name">{{ $user->name }}</p>
                                                    <p class="email">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane active" id="chat-with">
                        <input class="form-control" id="search_friend_name" placeholder="搜索聊天记录">
                        <div class="user-wrapper">
                            <ul class="record" id="test">
                                @if(!empty($chat_record))
                                    @foreach($chat_record as $user)
                                        <li class="user user-msg" id="{{ $user->id }}">
                                            @if($user->unread)
                                                <span class="pending">{{ $user->unread }}</span>
                                            @endif

                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="{{ $user->avatar }}" alt="" class="media-object">
                                                </div>

                                                <div class="media-body">
                                                    <p class="name">{{ $user->name }}</p>
                                                    <p class="last_msg">{{ $user->last_msg }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 message-scroll" id="messages">
                <div class="message-wrapper">
                </div>

                <div class="input-text">
                    <input type="text" name="message" class="submit">
                </div>
            </div>
        </div>
    </div>
@endsection
