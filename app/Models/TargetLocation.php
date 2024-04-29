<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetLocation extends Model
{
    protected $fillable = [
        "target_country_id",
        "name",
        "code",
        "region"
    ];
}