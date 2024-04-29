<?php

namespace App\Events;

use App\Models\Ad;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TgParseAdCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Ad $ad;
    /**
     * Create a new event instance.
     */
    public function __construct(Ad $ad){
        $this->ad = $ad;
    }
}