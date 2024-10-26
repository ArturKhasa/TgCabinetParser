<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserLanguage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserLanguage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdUserLanguage query()
 * @mixin \Eloquent
 */
class AdUserLanguage extends Pivot
{
    use HasFactory;
}
