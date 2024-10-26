<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserChannelAudience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserChannelAudience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserChannelAudience query()
 * @mixin \Eloquent
 */
class AdExcludeUserChannelAudience extends Pivot
{
    use HasFactory;
}
