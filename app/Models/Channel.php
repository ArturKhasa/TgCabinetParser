<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{

    protected $fillable=[
        'tg_id',
        'photo',
        'title',
        'title',
        'username',
        'phone_id'
    ];
}
