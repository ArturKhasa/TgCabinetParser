<?php

namespace App\Models\Clickise\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        "legal_name",
        "ord_organization_id",
        "inn",
        "space_id",
        "email",
        "phone"
    ];

    public function getFullNameAttribute()
    {
        $type = $this->type;
        if($type->id == 1)
            return $this->contact_name;
        else
            return $this->legal_name;
    }

    public function type(){
        return $this->belongsTo(ContractType::class, 'contract_type_id', 'id');
    }
}
