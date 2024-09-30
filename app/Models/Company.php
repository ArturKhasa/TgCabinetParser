<?php

namespace App\Models;

use App\Casts\Serialize;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        "name",
        "system",
        "cabinet_id",
        "active_schedule",
        "schedule",
        "timezone_id",
        "timezone_custom"
    ];

    protected $casts = [
        "schedule"      =>  Serialize::class
    ];

    public function cabinet(){
        return $this->belongsTo(Cabinet::class);
    }

    public function statusIs($statusId): bool
    {
        return $this->status_id == $statusId;
    }

//    public function status(){
//        return $this->belongsTo(CompanyStatus::class);
//    }

    public function ads(){
        return $this->hasMany(Ad::class);
    }

    public function adStats(){
        return $this->hasMany(AdStatFull::class);
    }

    public function adStops(){
        return $this->belongsToMany(Ad::class, 'company_ad_stops', 'company_id', 'ad_id')->using(CompanyAdStop::class);
    }

//    public function rules(){
//        return $this->morphMany(Rule::class, 'ruleable');
//    }

    public function syncSpaceRules($ids): bool{
        $this->rules()->delete();
        foreach($ids AS $id){
            $this->rules()->create(["rule_id" => $id]);
        }
        return true;
    }
}