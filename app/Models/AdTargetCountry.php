<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetCountry query()
 * @mixin \Eloquent
 */
class AdTargetCountry extends Pivot
{
    use HasFactory;
}
