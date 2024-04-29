<?php

namespace App\Models\Ord;

use App\Models\Cabinet;
use App\Models\Clickise\Finance\Contract;
use App\Services\OrdClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdContract extends Model
{
    use HasFactory;

    protected $fillable = [
        "ord_organization_id",
        "number",
        "amount",
        "date",
        "erir_id"
    ];

    public function cabinets(){
        return $this->hasMany(Cabinet::class, 'ord_contract_id', 'id');
    }

    public function ordInvoices(){
        return $this->hasMany(OrdInvoice::class, 'ord_contract_id', 'id');
    }

    public function ordOrganization(){
        return $this->belongsTo(OrdOrganization::class, "ord_organization_id" , "id");
    }

    public static function ordCreate(
        OrdOrganization $ordOrganization,
                        $amount,
                        $date,
                        $contractNumber,
    ): ?OrdContract
    {
        $ordClient = new OrdClient();
        $erir = $ordClient->contract(
            $ordOrganization,
            $amount,
            $date,
            $contractNumber
        );
        if(!$erir)
            return null;
        return OrdContract::firstOrCreate([
            "ord_organization_id"       =>  $ordOrganization->id,
            "number"                    =>  $contractNumber,
            "erir_id"                   =>  $erir
        ],[
            "amount"                    =>  $amount,
            "date"                      =>  $date,
        ]);
    }
}
