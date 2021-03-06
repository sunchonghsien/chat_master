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
            background: #F8FAFC;
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
            height: 620px;
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

        #more-msg {
            cursor: pointer;
            width: 75px;
            height: 20px;
        }

        p[id=more-msg]:hover {
            font-weight: bold;
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
    var user_list = [];

    $(document).ready(function () {
       var doc = new DOMParser().parseFromString('{{$users??'{}'}}','text/html');
        user_list = JSON.parse(doc.documentElement.textContent);
        var _ajax = $.ajax;
        $.ajax = function (opt) {
            var fn = {
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                }
            }

            if (opt.error) {
                fn.error = opt.error;
            }

            var _opt = $.extend(opt, {
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (XMLHttpRequest.status == 401) {
                        window.location.href = 'login';
                    }
                    fn.error(XMLHttpRequest, textStatus, errorThrown);
                }
            });
            return _ajax(_opt);
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#messages').hide();
        Pusher.logToConsole = true;


        Echo.private('receive-messages-' + my_id).listen('.receiveMessageEvent', function (data) {
            console.info(data);
            $('.record>li[id='+data.from+']').find('.last_msg').html(data.message)
        }).listen('.messageReadEvent', function (data) {
            var pending = $('.record>li[id='+data.from+']');
            if (pending.find('.pending').html() == undefined) {
                pending.append('<span class="pending">' + data.count + '</span>')
            } else {
                pending.find('.pending').html(data.count)
            }
        });


        $('.user-info').on('click', function () {
            $('.user-info').removeClass('active')
            getMsg.call(this);
        })

        //获取消息
        $('.user-msg').on('click', function () {
            $('.user-msg').removeClass('active')
            getMsg.call(this);
        })

        //历史纪录
        $(document).on('click', 'p[id=more-msg]', function (e) {
            $.ajax({
                type: 'get',
                url: 'historical',
                data: {
                    page: ++page,
                    receiver_id: receiver_id,
                },
                cache: false,
                success: function (data) {
                    var msg_list = new Array();
                    data.forEach(item => {
                        var msg = '<li class="message clearfix">\n' +
                            '<div class="' + (item.from == my_id ? 'sent' : 'received') + '">\n' +
                            '<p>' + item.message + '</p>\n' +
                            '<p class="date">' + new Date(item.created_at).format('d M y, h:i a') + '</p>\n' +
                            '</div>\n' +
                            '\n' +
                            '</li>'
                        msg_list.unshift(msg)
                    });

                    var height = $('.message-wrapper').get(0).scrollHeight;
                    $('.messages').prepend(msg_list.join(''));
                    $('.message-wrapper').get(0).scrollTop = $('.message-wrapper').get(0).scrollHeight - height;

                },
                error: function (jqXHR, status, error) {

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

                        $('.record>li[id='+receiver_id+']').find('.last_msg').html(message)
                    },
                    error: function (jqXHR, status, error) {

                    },
                    complete: function () {
                        scrollToBottomFunc();
                    }
                })
            }
        })

        $('#search_friend_name').keyup(function (e) {
           var arrBirds = user_list.filter(item=>item.name.toLowerCase().includes(e.target.value.toLowerCase()))

           $('.users >li').hide();

            arrBirds.forEach(item=>{
                $('.users >li[id='+item.id+']').show()
            });

           if(!e.target.value){
               $('.users >li').show();
           }

            // if(delay){
            //     clearTimeout(delay);
            // }
            // delay = setTimeout(function () {
            //
            // });
        })
    });


    // make a function to scroll down auto
    function scrollToBottomFunc() {
        $('.message-wrapper').animate({
            scrollTop: $('.message-wrapper').get(0).scrollHeight
        }, 50);
    }

    function getMsg() {
        page = 1;
        var is_room = 'in'
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
                    $('.message-wrapper').html(data);
                    $('#messages').show();
                    scrollToBottomFunc();
                }
            })

        } else {
            $('.message-wrapper').html('')
            $('#messages').hide();
            $(this).removeClass('active');
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

    }

</script>
</body>
</html>
