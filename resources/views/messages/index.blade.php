<div class="message-wrapper">
    @if(count($messages)>=10)
        <p style="text-align: center;cursor:pointer" class="more-msg">显示更多...</p>
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
</div>

<div class="input-text">
    <input type="text" name="message" class="submit">
</div>

