<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelPost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "message", "views", "channel_id", "external_id", "date"
    ];

    public function stats(): HasMany
    {
        return $this->hasMany(ChannelPostStat::class, 'post_id', 'id');
    }
}
