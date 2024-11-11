<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeChannel query()
 * @mixin \Eloquent
 */
class AdExcludeChannel extends Pivot
{
    use HasFactory;
}
