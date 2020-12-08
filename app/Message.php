<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['from','to','message','is_read'];

    protected $attributes = [
        'is_read' => 0,
    ];

}
