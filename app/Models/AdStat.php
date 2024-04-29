<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdStat extends Model
{
    protected $fillable = [
        "date", "views", "joins", "spent", "clicks"
    ];

    public function ad(){
        return $this->belongsTo(Ad::class, 'ad_id', 'id');
    }
}