<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserChannelAudience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserChannelAudience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserChannelAudience query()
 * @mixin \Eloquent
 */
class AdUserChannelAudience extends Pivot
{
    use HasFactory;
}
