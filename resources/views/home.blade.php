@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="user-wrapper">
                    <ul class="users" id="test">
                        @foreach($users as $user)
                            <li class="user" id="{{ $user->id }}">
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
                    </ul>
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
