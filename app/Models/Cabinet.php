<?php

namespace App\Models;

//use App\Models\Ord\OrdContract;
//use App\Models\Ord\OrdOrganization;
use App\Services\Traits\TelegramProvider;
use App\Services\Traits\TelegramService;
use App\Services\Traits\WithProxy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property bool $is_new
 */
class Cabinet extends Model
{
    use TelegramService, TelegramProvider, WithProxy;

    protected $hidden = [
        "owner_id",
        "dt",
        "ssid",
        "token",
        "hash"
    ];

    protected $fillable = [
        "owner_id",
        "dt",
        "ssid",
        "token",
        "photo",
        "budget",
        "name",
        "hash",
        "is_new",
        "has_connect",
        "ord_contract_id"
    ];

//    public function audiences(){
//        return $this->hasMany(CabinetAudience::class, 'cabinet_id', 'id');
//    }
//
//    public function companies(){
//        return $this->hasMany(Company::class);
//    }
//
//    public function defaultCompany(){
//        return $this->hasOne(Company::class)->where("system", true);
//    }

    public function ads(){
        return $this->hasMany(Ad::class);
    }

    public function adStats(){
        return $this->hasManyThrough(AdStat::class, Ad::class);
    }

    public function space(){
        return $this->belongsTo(Space::class);
    }

    public function ordOrganization(){
        return $this->hasOne(OrdOrganization::class, 'cabinet_id', 'id');
    }

    public function ordContract(){
        return $this->belongsTo(OrdContract::class, 'ord_contract_id', 'id');
    }
}