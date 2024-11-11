<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserTopic query()
 * @mixin \Eloquent
 */
class AdUserTopic extends Pivot
{
    use HasFactory;
}
