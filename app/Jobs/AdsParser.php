<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Models\Cabinet;
use App\Services\ParseAdStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdsParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Cabinet $cabinet;
    public function __construct(Cabinet $cabinet)
    {
        $this->onConnection('redis');
        $this->onQueue("stats");

        $this->cabinet  = $cabinet;
    }

    public function handle(): void
    {
        $service = new ParseAdStatsService($this->cabinet);
        $service->handle();
    }
}