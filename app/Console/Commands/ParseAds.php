<?php

namespace App\Console\Commands;

use App\Jobs\AdsParser;
use App\Models\Cabinet;
use Illuminate\Console\Command;

class ParseAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $cabinets = Cabinet::query()->get();
        foreach ($cabinets as $cabinet) {
            dispatch(new AdsParser(cabinet: $cabinet));
        }
    }
}
