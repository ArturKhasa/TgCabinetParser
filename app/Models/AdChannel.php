<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $ad_id
 * @property int $channel_id
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel whereAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdChannel whereId($value)
 * @mixin \Eloquent
 */
class AdChannel extends Pivot
{
    use HasFactory;

    protected $table = 'ad_channels';
}
