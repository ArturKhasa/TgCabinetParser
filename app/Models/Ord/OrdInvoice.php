<?php

namespace App\Models\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        "contract_id",
        "client_role",
        "contractor_role",
        "date",
        "start_date",
        "end_date",
        "amount",
        "is_vat",
        "erir_id"
    ];

    public function ordContract(){
        return $this->belongsTo(OrdContract::class, 'ord_contract_id', 'id');
    }
}
