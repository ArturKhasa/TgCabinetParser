<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Command;

class TelegramParsePosts extends Command
{
    protected $signature = 'app:telegram-parse-posts';

    public function handle(): void
    {
        $channelIds = Channel::query()->whereNotNull("username")->limit(300)->get()->pluck("id")->toArray();

        $this->info(count($channelIds));
        $lastCount = 0;
        while (count($channelIds) > 0) {
            $ids = array_slice($channelIds, 0, 100);
            $lastCount += count($ids);
            dispatch(new \App\Jobs\TelegramParsePosts($ids));
            $channelIds = array_slice($channelIds, count($ids), count($channelIds) - 1);
        }
        $this->info($lastCount);

    }
}