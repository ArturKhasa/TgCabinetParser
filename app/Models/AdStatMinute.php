<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdStatMinute extends Model
{
    protected $fillable = [
        "datetime", "views", "joins", "spent", "cpm", "clicks"
    ];
}