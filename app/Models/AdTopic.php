<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $ad_id
 * @property int $topic_id
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic whereAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdTopic whereTopicId($value)
 * @mixin \Eloquent
 */
class AdTopic extends Pivot
{
    //
}
