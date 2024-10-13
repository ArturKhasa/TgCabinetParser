<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function posts(): HasMany
    {
        return $this->hasMany(ChannelPost::class, 'channel_id', 'id');
    }

    public function telegramPhone(): BelongsTo
    {
        return $this->belongsTo(TelegramPhone::class, 'phone_id', 'id');
    }

    public function setTelegramPhone(TelegramPhone $phone): void
    {
        $this->update(["phone_id" => $phone->id]);
        $phone->increment("count", 1);
        if($phone->count >= 100)
            $phone->update(["open" => false]);
    }

    public function postStats(): HasManyThrough
    {
        return $this->hasManyThrough(
            ChannelPostStat::class, ChannelPost::class,
            'channel_id', 'post_id', 'id', 'id'
        );
    }
}

