<?php

namespace App\Models\Ord;

use App\Models\Ad;
use App\Services\OrdClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrdCreative extends Model
{
    use HasFactory;

    protected $fillable = [
        "ad_id",
        "erir_id",
        "erir_token"
    ];

    public function ad(){
        return $this->belongsTo(Ad::class, "ad_id", "id");
    }

    public static function ordCreateWithoutSave(
        Ad $ad,
        OrdContract $ordContract
    ): ?array
    {
        $ordClient = new OrdClient();
        $erirData = $ordClient->creative(
            $ad,
            $ordContract
        );
        return $erirData;
    }

    public static function ordCreate(
        Ad $ad,
        OrdContract $ordContract
    ): ?OrdCreative
    {
        $ordClient = new OrdClient();
        $erirData = $ordClient->creative(
            $ad,
            $ordContract
        );

        if(!$erirData)
            return null;

        return OrdCreative::firstOrCreate([
            "ad_id"         =>  $ad->id,
        ],[
            "erir_id"                   =>  $erirData["erir_id"],
            "erir_token"                =>  $erirData["erir_token"],
        ]);
    }
}
