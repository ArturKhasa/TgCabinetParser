<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdStatus extends Model
{
    const STATUS_DELETED = 6;
    const STATUS_ACTIVE = 1;
    const STATUS_HOLD = 2;

    public const WORK_STATUSES = [1, 2, 3, 4, 5];
    public const ALL_STATUSES_WITHOUT_DELETED = [1, 2, 3, 4, 5];

    public function isActive(): bool
    {
        return $this->id == 1;
    }

    public function isHold(): bool
    {
        return $this->id == 2;
    }

    public function isDelete(): bool
    {
        return $this->id == 6;
    }
}