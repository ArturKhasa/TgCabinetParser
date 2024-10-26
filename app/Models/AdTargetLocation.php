<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTargetLocation query()
 * @mixin \Eloquent
 */
class AdTargetLocation extends Pivot
{
    use HasFactory;
}
