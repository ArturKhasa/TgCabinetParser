<?php

namespace App\Listeners;

use App\Events\TgParseAdUpdated;
use App\Models\Ord\OrdCreative;
use Illuminate\Support\Facades\Log;

class AdOrdSignup
{
    public function __construct()
    {
        Log::info('ord construct');
    }

    /**
     * Handle the event.
     */
    public function handle(TgParseAdUpdated $event): void
    {
        Log::info('ord handle');
        $ad = $event->ad;
        $cabinet = $ad->cabinet;
        if($cabinet->ord_contract_id && !$ad->ordCreative)
        {
            $ordContract = $cabinet->ordContract;
            $ordCreative = OrdCreative::ordCreate($ad, $ordContract);
            if($ordCreative)
                $cabinet->tgEditOrdToken($ad, $ordCreative);
        }
    }
}