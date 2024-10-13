<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelPostStat extends Model
{

    public $timestamps = false;

    protected $fillable = [
        "post_id", "views", "date"
    ];
}
