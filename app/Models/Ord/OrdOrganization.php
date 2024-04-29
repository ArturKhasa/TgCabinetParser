<?php

namespace App\Models\Ord;

use App\Models\Cabinet;
use App\Models\Clickise\Finance\Contract;
use App\Services\OrdClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdOrganization extends Model
{
    use HasFactory;

    protected $fillable = [
        "cabinet_id",
        "contract_id",
        "erir_id"
    ];

    public static function ordCreate(
        Contract $contract,
        Cabinet $cabinet
    ): ?OrdOrganization
    {
        $ordClient = new OrdClient();
        $erir = $ordClient->organization($contract, $cabinet);
        if(!$erir)
            return null;
        $ordOrganization = OrdOrganization::firstOrCreate([
            "contract_id"       =>  $contract->id,
            "cabinet_id"        =>  $cabinet->id,
            "erir_id"           =>  $erir
        ]);
        return $ordOrganization;
    }

    public function ordContract(){
        return $this->hasOne(OrdContract::class, 'ord_organization_id', 'id');
    }

    public function contract(){
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function cabinet(){
        return $this->belongsTo(Cabinet::class, 'cabinet_id', "id");
    }

    public function clientId(){
        return $this->contract->id . "." . $this->cabinet->id;
    }

    public function contractorId(){
        return $this->contract->id . "." . $this->cabinet->id;
    }
}
