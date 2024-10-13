<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\TelegramPhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChannelPostViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:channel-post-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $headers = [
        'Accept'            => 'application/json, text/javascript, */*; q=0.01',
        'X-Requested-With'  => 'XMLHttpRequest',
        'Accept-Encoding'   =>  'keep-alive',
        'content-type'      =>  'application/x-www-form-urlencoded; charset=UTF-8',
    ];

    private function roundTime($carbonTime, $minuteInterval){
        return $carbonTime->setTime(
            $carbonTime->format('H'),
            floor($carbonTime->format('i') / $minuteInterval) * $minuteInterval,
            0
        );
    }

    private function log($string){
        Log::channel("jobChannelPostViews")->info($string);
    }

    private function getPhone(Channel $channel): TelegramPhone{
        if(!$channel->telegramPhone) {
            $phone = TelegramPhone::where("open", true)->inRandomOrder()->first();
            $channel->setTelegramPhone($phone);
            return $phone;
        }
        return $channel->telegramPhone;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $startTime = time();
        $this->log("Start ChannelPostViews Job. Start time - " . $startTime);

        $dateTime = $this->roundTime(Carbon::now(), 5);
        $http = Http::withHeaders($this->headers);

        $channels = Channel::whereNotNull("username")->get();
        foreach($channels AS $channel){

            $result = [];
            try {
                $telegramPhone = $this->getPhone($channel);
                $startTimeRequest = time();
                $result = $http->get("https://mtprotonew.tgbooster.ru/", [
                    "hash"      =>  "4ijsd8wnd28jasdids8j3sd",
                    "username"  =>  $channel->username,
                    "phone"     =>  $telegramPhone->phone
                ])->json();
                $finishTimeRequest = time();
                $this->info("channel " . ($channel->username) . ", phone " . $telegramPhone->phone);
                $this->info("request " . ($finishTimeRequest - $startTimeRequest) . " seconds");
                $this->info('result -', $result);
            }catch(\Exception $e) {
                $this->info("Exception 1");
                $this->info(print_r($result, true));
                $this->info($e->getMessage());
            }
            try{
                if (isset($result["success"]) && $result["success"]) {
                    $result["messages"] = array_reverse($result["messages"]);
                    $lastPost = null;
                    $originalViews = 0;
                    $changeViews = 0;
                    foreach ($result["messages"] as $index => $message) {
                        $lastPost = $channel->posts()->firstWhere("external_id", $message["id"]);
                        if ($lastPost)
                            $originalViews = $lastPost->views;

                        $lastPost = $channel->posts()->updateOrCreate(
                            ["external_id" => $message["id"]],
                            [
                                "message" => $message["message_text"],
                                "views" => $message["views"],
                                "date" => $message["date"]
                            ]
                        );
                        if ($lastPost->wasRecentlyCreated)
                            $originalViews = $message["views"];

                        if ($lastPost->wasRecentlyCreated && $index == (count($result["messages"]) - 1))
                            $changeViews = $message["views"];
                        else
                            $changeViews = ($message["views"] - $originalViews);
                    }
                    if ($lastPost) {
                        $stat = $lastPost->stats()->firstWhere("date", $dateTime->timestamp);
                        if ($stat) {
                            if ($changeViews > 0)
                                $stat->increment("views", $changeViews);
                        } else
                            $lastPost->stats()->create([
                                "date" => $dateTime->timestamp,
                                "views" => $changeViews
                            ]);
                    }
                }
                else
                    $this->info(print_r($result, true));
            }catch(\Exception $e){
                $this->info("Exception 2");
                $this->info($e->getMessage());
//                $this->log("Exception");
//                $this->log(print_r($e->getMessage(), true));
            }

        }
        $finishTime = time();
        $this->log("Finish ChannelPostViews Job. Finish time - " . $finishTime);
        $this->log("Process seconds " . ($finishTime - $startTime));
    }
}
