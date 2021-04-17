<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2021/1/4
 * Time: 下午 05:16
 */

namespace App\Helper;


class RedisKeyName
{
    public static function messageRead($myId, $id)
    {
        return "messageRead:$myId:$id";
    }
    public static function friendRoom($myId){
        return "friend_room:$myId";
    }
    public static function historicalRecord($receiver_id,$my_id){
        return 'historical_record:' . ($receiver_id > $my_id ? "$my_id:$receiver_id" : "$receiver_id:$my_id");
    }
}