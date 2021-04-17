@if(!empty($messages))
    @if(count($messages)>=10)
        <div class="d-flex justify-content-center"><p class="" id="more-msg">显示更多...</p></div>
    @endif
    <ul class="messages">
        @foreach($messages as $message)
            <li class="message clearfix">
                <div class="{{($message['from']==Auth::id())?'sent':'received'}}">
                    <p>{{$message['message'] }}</p>
                    <p class="date">{{date('d M y, h:i a',strtotime($message['created_at']))}}</p>
                </div>

            </li>
        @endforeach
    </ul>
@endif
