<?php

namespace App\Listeners;

use App\Events\TgParseAdCreated;
use App\Jobs\TgParseAdInfo;

class TgParseAdCreatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TgParseAdCreated $event): void
    {
        TgParseAdInfo::dispatch($event->ad);
    }
}