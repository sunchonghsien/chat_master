<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* width */
        ::-webkit-scrollbar {
            width: 7px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #a7a7a7;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #929292;
        }

        ul {
            margin: 0;
            padding: 0;
        }

        li {
            list-style: none;
        }

        .user-wrapper, .message-wrapper {
            border: 1px solid #dddddd;
            overflow-y: auto;
        }

        .user-wrapper {
            height: 600px;
        }

        .user {
            cursor: pointer;
            padding: 5px 0;
            position: relative;
        }

        .user:hover {
            background: #eeeeee;
        }

        .user:last-child {
            margin-bottom: 0;
        }

        .pending {
            position: absolute;
            left: 13px;
            top: 9px;
            background: #b600ff;
            margin: 0;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            line-height: 18px;
            color: #ffffff;
            font-size: 12px;
            text-align: center;
        }

        .media-left {
            margin: 0 10px;
        }

        .media-left img {
            width: 64px;
            border-radius: 64px;
        }

        .media-body p {
            margin: 6px 0;
        }

        .message-wrapper {
            padding: 10px;
            height: 536px;
            background: #eeeeee;
        }

        .messages .message {
            margin-bottom: 15px;
        }

        .messages .message:last-child {
            margin-bottom: 0;
        }

        .received, .sent {
            width: 45%;
            padding: 3px 10px;
            border-radius: 10px;
        }

        .received {
            background: #ffffff;
        }

        .sent {
            background: #3bebff;
            float: right;
            text-align: right;
        }

        .message p {
            margin: 5px 0;
        }

        .date {
            color: #777777;
            font-size: 12px;
        }

        .active {
            background: #eeeeee;
        }

        input[type=text] {
            width: 100%;
            padding: 12px 20px;
            margin: 15px 0 0 0;
            display: inline-block;
            border-radius: 4px;
            box-sizing: border-box;
            outline: none;
            border: 1px solid #cccccc;
        }

        input[type=text]:focus {
            border: 1px solid #aaaaaa;
        }
    </style>
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>
</div>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="js/date.format.js"></script>
<script>
    var receiver_id = '';
    var my_id = "{{ Auth::id() }}";
    var page = 1;

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        Pusher.logToConsole = true;


        Echo.private('receive-messages-' + my_id).listen('.receiveMessageEvent', function (data) {
            $('#'+data.from).find('.last_msg').html(data.message)
        }).listen('.messageReadEvent', function (data) {
            var pending = $('#'+data.from);
            if(pending.find('.pending').html()==undefined){
                pending.append('<span class="pending">'+data.count+'</span>')
            }else{
                pending.find('.pending').html(data.count)
            }
        });


        //获取消息
        $('.user').click(function () {
            page = 1;
            var is_room = 'in'
            $('.user').removeClass('active')
            $(this).addClass('active')
            $(this).find('.pending').remove();
            Echo.leave('send-messages-' + my_id + '-' + receiver_id);
            if ($(this).attr('id') != receiver_id) {
                Echo.private('send-messages-' + my_id + '-' + $(this).attr('id')).listen('.sendMessageEvent', function (data) {
                    $('.messages').append('<li class="message clearfix">\n' +
                        '<div class="received">\n' +
                        '<p>' + data.item.msg + '</p>\n' +
                        '<p class="date">' + new Date(data.item.time).format('d M y, h:i a') + '</p>\n' +
                        '</div>\n' +
                        '\n' +
                        '</li>');
                    scrollToBottomFunc();

                });
                receiver_id = $(this).attr('id')

                $.ajax({
                    type: 'get',
                    url: 'message/' + receiver_id,
                    data: '',
                    cache: false,
                    success: function (data) {
                        $('#messages').html(data)
                        scrollToBottomFunc();
                    }
                })

            } else {
                $('#messages').html('')
                is_room = 'out';
                receiver_id = '';
            }


            $.ajax({
                type: 'post',
                url: 'room_send',
                data: {
                    is_room: is_room,
                    to: is_room == 'out' ? '' : receiver_id
                },
                cache: false,
            })
        })
        $(document).on('click', 'p[class=more-msg]', function (e) {
            $.ajax({
                type: 'get',
                url: 'historical',
                data: {
                    page:++page,
                    receiver_id: receiver_id,
                },
                cache: false,
                success: function (data) {
                    // $('.messages').before('<li class="message clearfix">\n' +
                    //     '<div class="sent">\n' +
                    //     '<p>' + message + '</p>\n' +
                    //     '<p class="date">' + new Date(date).format('d M y, h:i a') + '</p>\n' +
                    //     '</div>\n' +
                    //     '\n' +
                    //     '</li>');
                    // scrollToBottomFunc();
                    //
                    // $('#'+receiver_id).find('.last_msg').html(message)
                },
                error: function (jqXHR, status, error) {

                },
                complete: function () {
                    scrollToBottomFunc();
                }
            })
        })

        // 发送消息
        $(document).on('keyup', '.input-text input', function (e) {
            var message = $(this).val()
            if (e.keyCode == 13 && (message != '' && message.trim() != '') && receiver_id != '') {
                $(this).val('')

                var date = "{{date('Y-m-d H:i:s')}}";
                $.ajax({
                    type: 'post',
                    url: 'message',
                    data: {
                        receiver_id: receiver_id,
                        message: message,
                        time: date
                    },
                    cache: false,
                    success: function (data) {
                        $('.messages').append('<li class="message clearfix">\n' +
                            '<div class="sent">\n' +
                            '<p>' + message + '</p>\n' +
                            '<p class="date">' + new Date(date).format('d M y, h:i a') + '</p>\n' +
                            '</div>\n' +
                            '\n' +
                            '</li>');
                        scrollToBottomFunc();

                        $('#'+receiver_id).find('.last_msg').html(message)
                    },
                    error: function (jqXHR, status, error) {

                    },
                    complete: function () {
                        scrollToBottomFunc();
                    }
                })
            }
        })


    });

    // make a function to scroll down auto
    function scrollToBottomFunc() {
        $('.message-wrapper').animate({
            scrollTop: $('.message-wrapper').get(0).scrollHeight
        }, 50);
    }

</script>
</body>
</html>
