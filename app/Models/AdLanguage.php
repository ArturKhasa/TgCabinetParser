<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $ad_id
 * @property int $lang_id
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage whereAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdLanguage whereLangId($value)
 * @mixin \Eloquent
 */
class AdLanguage extends Pivot
{
    //
}
