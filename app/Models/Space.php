<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Space extends Model
{
    protected $fillable = ['name', 'tariff_plan_id', 'proxy_id'];

    public function hasActivePlan()
    {
        return $this->currentTariffPlan?->tariffPlan;
    }

    public function getAdLimitBalance()
    {
        $tariffPlan = $this->currentTariffPlan?->tariffPlan;
        if (!$tariffPlan)
            return 0;
        $ads_limit = $tariffPlan->ads_limit;
        $ads_count = $this->ads()->count();
        if ($ads_count >= $ads_limit)
            return 0;
        return ($ads_limit - $ads_count);
    }

    public function currentTariffPlan(): BelongsTo
    {
        return $this->belongsTo(SpacePlanHistory::class, 'tariff_plan_id', 'id');
    }

    public function ads(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ad::class, Cabinet::class,
            'space_id', 'cabinet_id', 'id',
        );
    }
}