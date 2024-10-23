<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\Proxy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;
use Carbon\Carbon;

class TelegramParsePosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $channels;
    public $proxy;
    /**
     * Create a new job instance.
     */
    public function __construct($ids = [])
    {
        $this->onConnection('redis');
        $this->onQueue("channels");

        $this->channels = Channel::whereIn("id", $ids)->get();
        $this->proxy = Proxy::inRandomOrder()->first();
    }

    private function roundTime($carbonTime, $minuteInterval){
        return $carbonTime->setTime(
            $carbonTime->format('H'),
            floor($carbonTime->format('i') / $minuteInterval) * $minuteInterval,
            0
        );
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dateTime = $this->roundTime(Carbon::now(), 60);

        $http = Http::withOptions([
//            'proxy'     =>  "http://" . $this->proxy->host . ":" . $this->proxy->port
        ]);

        foreach($this->channels AS $channel) {
            $startTime = time();

            try{
                $dom = new Dom;
                $body = $http->get("https://t.me/s/" . $channel->username)->body();
                $dom->loadStr($body);

                $posts = $dom->getElementsByClass('tgme_widget_message_wrap');
                $postCount = count($posts);
                $posts = array_slice($posts->toArray(), $postCount - 3, $postCount - 1);
                $postCount = count($posts);

                $lastPost = null;
                $originalViews = 0;
                $changeViews = 0;

                $i = 0;
                foreach ($posts as $post) {

                    $i++;
                    $postUrl = $post->firstChild()->getAttribute("data-post");
                    $postId = explode("/", $postUrl)[1];

                    $postContent = $post->find(".tgme_widget_message_text");
    //                    $this->info($i);
                    $postText = "";
                    if(isset($postContent->innerHtml))
                        $postText = strip_tags($postContent->innerHtml, "<br>");

                    $postStatContent = $post->find(".tgme_widget_message_info");
                    $postStatViews = strip_tags($postStatContent->find(".tgme_widget_message_views"));
                    $postStatDate = $postStatContent->find("time")[0]->getAttribute("datetime");

                    if (preg_match("#(.*)K#", $postStatViews, $views)) {
                        $postStatViews = (float)$views[1] * 1000;
                    }
                    if (preg_match("#(.*)M#", $postStatViews, $views)) {
                        $postStatViews = (float)$views[1] * 1000000;
                    }

                    if(!$postStatViews)
                        continue;

                    $postStatDate = Carbon::parse($postStatDate)->timestamp;

                    $lastPost = $channel->posts()->firstWhere("external_id", $postId);
                    if ($lastPost)
                        $originalViews = $lastPost->views;

                    $lastPost = $channel->posts()->updateOrCreate(
                        ["external_id" => $postId],
                        [
                            "message" => $postText,
                            "views" => $postStatViews,
                            "date" => $postStatDate
                        ]
                    );
                    if ($lastPost->wasRecentlyCreated)
                        $originalViews = $postStatViews;

                    if ($lastPost->wasRecentlyCreated && $i == $postCount)
                        $changeViews = $postStatViews;
                    else
                        $changeViews = ($postStatViews - $originalViews);

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
            }catch(\Exception $e){

            }

            $finishTime = time();
//            $this->info("finish channel - " . $channel->username . "; time - " . ($finishTime - $startTime) . " seconds");
        }
    }
}
