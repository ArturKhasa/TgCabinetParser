<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $ad_id
 * @property int $topic_id
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic whereAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdExcludeTopic whereTopicId($value)
 * @mixin \Eloquent
 */
class AdExcludeTopic extends Pivot
{
    //
}
