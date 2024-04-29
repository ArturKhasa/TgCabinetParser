<?php

namespace App\Events;

use App\Models\Ad;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TgParseAdUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ad $ad)
    {
    }

}