<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserDevice query()
 * @mixin \Eloquent
 */
class AdUserDevice extends Pivot
{
    use HasFactory;
}
