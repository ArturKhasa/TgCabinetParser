<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeUserTopic query()
 * @mixin \Eloquent
 */
class AdExcludeUserTopic extends Pivot
{
    use HasFactory;
}
