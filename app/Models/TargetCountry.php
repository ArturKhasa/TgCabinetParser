<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetCountry extends Model
{
    public function locations(){
        return $this->hasMany(TargetLocation::class, 'id', 'target_country_id');
    }
}