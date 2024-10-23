<?php

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\Channel;
use App\Models\Proxy;
use App\Services\ParseAdStatsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;

class WebhookHandleController extends Controller
{
    public function parseAdStats(Request $request): JsonResponse
    {
//        ParseAdStatsService::parse($request->get('cabinetIds'));
//        $cabinets = Cabinet::query()->where('id', 37)->get();
//        foreach ($cabinets as $cabinet) {
//            $service = new ParseAdStatsService($cabinet);
//            $service->handle();
//        }
//
//        return response()->json();
    }

    public function channelPosts()
    {
        $dateTime = $this->roundTime(Carbon::now(), 60);

        $channels = Channel::query()->orderByDesc('id')->limit(100)->get();
        $proxy = Proxy::inRandomOrder()->first();

        $http = Http::withOptions([
//            'proxy' => "http://" . $proxy->host . ":" . $proxy->port
        ]);

        foreach ($channels as $channel) {
            $startTime = time();

            try {
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
                    if (isset($postContent->innerHtml))
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

                    if (!$postStatViews)
                        continue;

                    $postStatDate = Carbon::parse($postStatDate)->timestamp;

                    $lastPost = $channel->posts()->firstWhere("external_id", $postId);
                    if ($lastPost)
                        $originalViews = $lastPost->views;

                    $lastPost = $channel->posts()->updateOrCreate(
                        ["external_id" => $postId],
                        [
                            "message" => $postText,
                            "views"   => $postStatViews,
                            "date"    => $postStatDate
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
                            "date"  => $dateTime->timestamp,
                            "views" => $changeViews
                        ]);
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    private function roundTime($carbonTime, $minuteInterval)
    {
        return $carbonTime->setTime(
            $carbonTime->format('H'),
            floor($carbonTime->format('i') / $minuteInterval) * $minuteInterval,
            0
        );
    }
}