<?php

namespace App\Models\Clickise\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    use HasFactory;

    public function isLegal(){
        return in_array($this->id, [2, 3]);
    }

    public function getIdentityForOrd(){
        if($this->id == 1)
            return "fl";
        elseif($this->id == 2)
            return "ip";
        elseif($this->id == 3)
            return "ul";
        return null;
    }
}
