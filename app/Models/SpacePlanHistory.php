<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpacePlanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "plan_id",
        "sum",
        "user_id",
        "active_to"
    ];

    public function leftDays(){
        $now = Carbon::now();
        $active_to = Carbon::parse($this->active_to);
        return $now->diffInDays($active_to);
//        return $now->diff($active_to)->format("%dд, %H:%I");
    }

    public function tariffPlan(){
        return $this->belongsTo(TariffPlan::class, 'plan_id', 'id');
    }
}
