<?php

namespace App\Services\Traits;

use App\Models\Ad;
use App\Models\AdStatus;
use App\Models\Channel;
//use App\Models\Device;
//use App\Models\Language;
//use App\Models\Ord\OrdCreative;
//use App\Models\TargetCountry;
//use App\Models\TargetLocation;
use App\Models\Timezone;
//use App\Models\Topic;
//use App\Services\HasAudiences;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;

trait TelegramProvider
{
    use HasAudiences;

    private $url = "https://ads.telegram.org/";
    private $headers = [
        'Accept'            => 'application/json, text/javascript, */*; q=0.01',
        'X-Requested-With'  => 'XMLHttpRequest',
        'Host'              =>  'ads.telegram.org',
        'Accept-Encoding'   =>  'keep-alive'
    ];

    private $htmlHeaders = [
        'Accept'            =>  'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        'Host'              =>  'ads.telegram.org',
        'Accept-Encoding'   =>  'gzip, deflate, br'
    ];
    private $withoutProxy = false;

    public function setWithoutProxy(){
        $this->withoutProxy = true;
        return $this;
    }

    public function tgRequest($httpMethod, $method, $data = [], $form = false, $headers = null){
        if(!$headers)
            $headers = $this->headers;
        $cookies = [];
        $cookies[] = "stel_adowner=" . $this->owner_id;
        $cookies[] = "stel_ssid=" . $this->ssid;
        $cookies[] = "stel_dt=" . $this->dt;
        $cookies[] = "stel_token=" . $this->token;
        $headers["Cookie"] = implode(";", $cookies);
        $http = Http::withHeaders($headers);
//
//        if(!$this->withoutProxy && $proxy = $this->spaceProxy())
//        {
//            $http->withOptions([
//                'proxy'     =>  "http://" . $proxy->host . ":" . $proxy->port
//            ]);
//        }

        if($form)
            $http->asForm();
        return $http->$httpMethod($this->url . $method, $data);
    }

    private function tgAdsPagination($offset_id = null){
        return $this->tgRequest("post", "api?hash=" . $this->hash, [
            "owner_id"      =>  $this->owner_id,
            "offset_id"     =>  $offset_id,
            "method"        =>  "getAdsList"
        ], true)->json();
    }

    public function checkAdPost(Ad $ad){
        return $this->tgRequest("post", "api?hash=" . $this->hash, [
            "owner_id"      =>  $this->owner_id,
            "promote_url"   =>  $ad->promote_url,
            "website_name"  =>  $ad->website_name,
            "text"          =>  $ad->text,
            "method"        =>  "checkAdPost"
        ], true)->json();
    }

    public function tgAllAds(){
        $ads = [];
        $next_offset_id = null;
        do
        {
            $result = $this->tgAdsPagination($next_offset_id);
            $next_offset_id = @$result["next_offset_id"];
            if(isset($result["items"]))
                $ads = array_merge($ads, $result["items"]);
        }while($next_offset_id);
        return $ads;
    }

    public function tgAds(){
        return $this->tgRequest("get", "account")->json()["s"]["initialAdsList"]["items"];
    }

    public function tgAccountDataBody(){
        return $this->tgRequest("get", "account", headers: $this->htmlHeaders)->body();
    }

    public function tgAccountSpentMonth($month){
        return $this->tgRequest("get", "account/stats", [
            "month" =>  $month
        ])->json()["h"];
    }

    /**
     * @throws \Exception
     */
    public function tgAccountData(){
        $page = $this->tgRequest("get", "account", headers: $this->htmlHeaders)->body();
        $dom = new Dom;
        $dom->loadStr($page);
        $budgetHtml = $dom->find('.js-owner_budget')[0];

        if(!$budgetHtml)
            throw new \Exception("Кабинет не авторизован");

        $nameHtml = $dom->find('.pr-dropdown-label .mobile-hide')[0];
        $photoHtml = $dom->find('.pr-auth-photo img');
        preg_match('/Budget: (.*)/', $budgetHtml->text, $budget);

        $budgetStripped = strip_tags($budgetHtml);
        $currency = "euro";
        if(str_contains($budgetStripped, "💎")) {
            preg_match('#💎(.*)#', strip_tags($budgetHtml), $budget);
            $currency = 'ton';
        }else
            preg_match('#€(.*)#', strip_tags($budgetHtml), $budget);
        $budget = str_replace(",", "", $budget[1]);

        /*hash*/
        preg_match('#\"apiUrl\"\:\"\\\/api\?hash\=(.*)\"#isU', $page, $hash);
        return [
            "budget"        =>  $budget,
            "name"          =>  $nameHtml->text,
            "photo"         =>  $photoHtml->getAttribute("src"),
            "hash"          =>  $hash[1],
            "currency"      =>  $currency
        ];
    }

    public function tgGetLastAd(){
        $ads = $this->tgAds();
        return $ads[0] ?? null;
    }

    public function tgAd($id){
        return $this->tgRequest("get", "account/ad/{$id}")->json()["h"];
    }

    public function hasTgAd($id): bool
    {
        return !isset($this->tgRequest("get", "account/ad/{$id}")->json()["l"]);
    }

    public function tgStatsAll($id){
        return $this->tgRequest("get", "account/ad/{$id}/stats", [
            "period"        =>  "day"
        ])->json();
    }

    public function tgStats($id){
        return $this->tgRequest("get", "account/ad/{$id}/stats", [
            "period"        =>  "day"
        ])->json()["j"];
    }

    public function tgStatsByMinutes($id){
        return $this->tgRequest("get", "account/ad/{$id}/stats", [
            "period"        =>  "5min"
        ])->json()["j"];
    }

    public function tgSearchChannel($url, $field = "channels"){
        $result = $this->tgRequest("post", "api?hash=" . $this->hash, [
            "query"     =>	$url,
            "field"     =>  $field,
            "method"    =>  "searchChannel"
        ], true)->json();

        if(isset($result["error"]))
        {
            return [
                "ok"        =>  false,
                "error"     =>  $result["error"]
            ];
        }
        elseif(isset($result["ok"]))
        {
            preg_match('#src\=\"(.*)\"#isU', $result["channel"]["photo"], $photo);
            try {
                $channel = Channel::updateOrCreate(
                    ["tg_id" => $result["channel"]["id"]],
                    [
                        "username" => $result["channel"]["username"],
                        "photo" => $photo[1],
                        "title" => strip_tags($result["channel"]["title"]),
                    ]
                );
            }catch(\Exception $e){
                return [
                    "ok"        =>  false,
                    "error"     =>  "Ошибка добавления канала в Базу"
                ];
            }
            return [
                "ok"        =>  true,
                "channel"   =>  $channel
            ];
        }
    }

    public function tgDeleteAd(Ad $ad, $confirmHash = null){
        $postData = [
            "owner_id"       =>  $this->owner_id,
            "ad_id"         =>  $ad->external_ad_id,
            "method"        =>  "deleteAd",
        ];
        if($confirmHash)
            $postData["confirm_hash"] = $confirmHash;

        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
            ->json();
        if(isset($result["confirm_hash"]))
            return $this->tgDeleteAd($ad, $result["confirm_hash"]);
        return $result;
    }

    public function tgEditAdStatus(Ad $ad, AdStatus $status){
        $postData = [
            "owner_id"       =>  $this->owner_id,
            "ad_id"         =>  $ad->external_ad_id,
            "method"        =>  "editAdStatus",
        ];
        if($status->isActive())
            $postData["active"] = 1;
        elseif($status->isHold())
            $postData["active"] = 0;
        if(isset($postData["active"])) {
            $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
                ->json();
            return $result;
        }
        return false;
    }

    public function tgEditAdCPM(Ad $ad, $cpm){
        $postData = [
            "owner_id"       =>  $this->owner_id,
            "ad_id"         =>  $ad->external_ad_id,
            "cpm"           =>  $cpm,
            "method"        =>  "editAdCPM",
        ];
        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
            ->json();
        return $result;
    }

    public function tgIncrementAdBudget(Ad $ad, $budget){
        $postData = [
            "owner_id"          =>  $this->owner_id,
            "ad_id"             =>  $ad->external_ad_id,
            "amount"            =>  $budget,
            "popup"             =>  1,
            "method"            =>  "incrAdBudget",
        ];
        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
            ->json();
        return $result;
    }

    public function tgEditAdFromArray(Ad $ad, $data = []){
        $postData = [
            "owner_id"       =>  $this->owner_id,
            "ad_id"         =>  $ad->external_ad_id,
            "method"        =>  "editAd",
            "title"         =>  $data["title"],
            "text"          =>  $data["text"],
            "promote_url"   =>  $data["promote_url"],
            "website_name"  =>  $data["website_name"],
            "cpm"           =>  $data["cpm"],
            "active"        =>  $ad->afterStatusCode(),
        ];
        $postData["intersect_topics"] = $ad->intersect_topics;
        $postData["exclude_politic"] = $ad->exclude_politic;
        $postData["picture"] = $ad->picture;

        /*Schedule*/
        if($ad->use_schedule)
        {
            $scheduleService = new ScheduleService();
            $postData["schedule"] = $scheduleService->getSumStringFromArray($ad->schedule);
            $postData["schedule_tz_custom"] = $ad->timezone_custom;
            $postData["schedule_tz"] = Timezone::find($ad->timezone_id)?->value;
        }

        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
            ->json();
        return $result;
    }

    public function tgEditAd(Ad $ad){
        $postData = [
            "owner_id"       =>  $this->owner_id,
            "ad_id"         =>  $ad->external_ad_id,
            "title"         =>  $ad->title,
            "text"          =>  $ad->text,
            "promote_url"   =>  str_replace("https://", "", $ad->promote_url),
            "website_name"  =>  $ad->website_name,
            "cpm"           =>  $ad->cpm,
            "budget"        =>  $ad->budget,
            "method"        =>  "editAd",
            "active"        =>  $ad->afterStatusCode(),
        ];
        $postData["intersect_topics"] = $ad->intersect_topics;
        $postData["exclude_politic"] = $ad->exclude_politic;
        $postData["picture"] = $ad->picture;

        /*Schedule*/
        if($ad->use_schedule)
        {
            $scheduleService = new ScheduleService();
            $postData["schedule"] = $scheduleService->getSumStringFromArray($ad->schedule);
            $postData["schedule_tz_custom"] = $ad->timezone_custom;
            $postData["schedule_tz"] = Timezone::find($ad->timezone_id)?->value;
        }

        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
            ->json();
        return $result;
    }

//    public function tgEditOrdToken(Ad $ad, OrdCreative $ordCreative): bool
//    {
//        $postData = [
//            "owner_id"       =>  $this->owner_id,
//            "ad_id"         =>  $ad->external_ad_id,
//            "title"         =>  $ad->title,
//            "text"          =>  $ad->text,
//            "promote_url"   =>  str_replace("https://", "", $ad->promote_url),
//            "website_name"  =>  $ad->website_name,
//            "cpm"           =>  $ad->cpm,
//            "budget"        =>  $ad->budget,
//            "ad_info"       =>  "Erid: " . $ordCreative->erir_token,
//            "method"        =>  "editAd",
//            "active"        =>  $ad->afterStatusCode(),
//        ];
//
//        if($ad->intersect_topics)
//            $postData["intersect_topics"] = "1";
//        if($ad->exclude_politic)
//            $postData["exclude_politic"] = "1";
//        if($ad->picture)
//            $postData["picture"] = "1";
//
//        /*Schedule*/
//        if($ad->use_schedule)
//        {
//            $scheduleService = new ScheduleService();
//            $postData["schedule"] = $scheduleService->getSumStringFromArray($ad->schedule);
//            $postData["schedule_tz_custom"] = $ad->timezone_custom;
//            $postData["schedule_tz"] = Timezone::find($ad->timezone_id)?->value;
//        }
//
//        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
//            ->json();
//
//        return isset($result["ok"]) && $result["ok"];
//    }

//    public function tgEditOrdTokenTest(Ad $ad, OrdCreative $ordCreative)
//    {
//        $postData = [
//            "owner_id"       =>  $this->owner_id,
//            "ad_id"         =>  $ad->external_ad_id,
//            "title"         =>  $ad->title,
//            "text"          =>  $ad->text,
//            "promote_url"   =>  $ad->promote_url,
//            "website_name"  =>  $ad->website_name,
//            "cpm"           =>  $ad->cpm,
//            "budget"        =>  $ad->budget,
//            "ad_info"       =>  "Erid: " . $ordCreative->erir_token,
//            "method"        =>  "editAd",
//            "active"        =>  $ad->afterStatusCode(),
//        ];
//        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
//            ->json();
//        return $result;
//    }

//    public function tgCreateAd(Ad $ad, $targetings = [], $trg_type = 'channel')
//    {
//        $postData = [
//            "owner_id"       =>  $this->owner_id,
//            "title"         =>  $ad->title,
//            "text"          =>  $ad->text,
//            "promote_url"   =>  str_replace("https://", "", $ad->promote_url),
//            "website_name"  =>  $ad->website_name,
//            "ad_info"       =>  $ad->ad_info,
//            "cpm"           =>  $ad->cpm,
//            "budget"        =>  $ad->budget,
//            "method"        =>  "createAd",
//            "active"        =>  $ad->afterStatusCode(),
//            /*channel target*/
//            "langs"                 =>  "",
//            "topics"                =>  "",
//            "channels"              =>  "",
//            "exclude_topics"        =>  "",
//            "exclude_channels"      =>  "",
//            /*user target*/
//            "countries"             =>  "",
//            "locations"             =>  "",
//            "user_langs"            =>  "",
//            "user_topics"           =>  "",
//            "exclude_user_topics"   =>  "",
//            "views_per_user"        =>  1,
//
//            "device"                =>  "",
//            "user_channels"         =>  "",
//            "exclude_user_channels" =>  "",
//        ];
//        if($ad->intersect_topics)
//            $postData["intersect_topics"] = "1";
//        if($ad->exclude_politic)
//            $postData["exclude_politic"] = "1";
//        if($ad->picture)
//            $postData["picture"] = "1";
//
//        /*Schedule*/
//        if($ad->use_schedule)
//        {
//            $scheduleService = new ScheduleService();
//            $postData["schedule"] = $scheduleService->getSumStringFromArray($ad->schedule);
//            $postData["schedule_tz_custom"] = $ad->timezone_custom;
//            $postData["schedule_tz"] = Timezone::find($ad->timezone_id)?->value;
////            Log::channel("tests")->info(print_r($postData, true));
//        }
//
//        if($trg_type == 'channel')
//        {
//            $postData["target_type"] = "channels";
//
//            if(isset($targetings["langs"]) && count($targetings["langs"])) {
//                $langs = Language::whereIn("id", $targetings["langs"])->get()->pluck("code")->toArray();
//                $postData["langs"] = implode(";", $langs);
//            }
//
//            if(isset($targetings["topics"]) && count($targetings["topics"])) {
//                $topics = Topic::whereIn("id", $targetings["topics"])->get()->pluck("code")->toArray();
//                $postData["topics"] = implode(";", $topics);
//            }
//
//            if(isset($targetings["channels"]) && count($targetings["channels"]))
//                $postData["channels"] = implode(";", $targetings["channels"]);
//
//            if(isset($targetings["exclude_topics"]) && count($targetings["exclude_topics"])) {
//                $topics = Topic::whereIn("id", $targetings["exclude_topics"])->get()->pluck("code")->toArray();
//                $postData["exclude_topics"] = implode(";", $topics);
//            }
//
//            if(isset($targetings["exclude_channels"]) && count($targetings["exclude_channels"]))
//                $postData["exclude_channels"] = implode(";", $targetings["exclude_channels"]);
//        }
//        elseif($trg_type == 'user')
//        {
//            $postData["target_type"] = "users";
//
//            if(isset($targetings["countries"]) && count($targetings["countries"])) {
//                $countries = TargetCountry::whereIn("id", $targetings["countries"])->get()->pluck("code")->toArray();
//                $postData["countries"] = implode(";", $countries);
//            }
//
//            if(isset($targetings["locations"]) && count($targetings["locations"])) {
//                $locations = TargetLocation::whereIn("id", $targetings["locations"])->get()->pluck("code")->toArray();
//                $postData["locations"] = implode(";", $locations);
//            }
//
//            if(isset($targetings["userLanguages"]) && count($targetings["userLanguages"])) {
//                $langs = Language::whereIn("id", $targetings["userLanguages"])->get()->pluck("code")->toArray();
//                $postData["user_langs"] = implode(";", $langs);
//            }
//
//            if(isset($targetings["userTopics"]) && count($targetings["userTopics"])) {
//                $topics = Topic::whereIn("id", $targetings["userTopics"])->get()->pluck("code")->toArray();
//                $postData["user_topics"] = implode(";", $topics);
//            }
//
//            if(isset($targetings["excludeUserTopics"]) && count($targetings["excludeUserTopics"])) {
//                $topics = Topic::whereIn("id", $targetings["excludeUserTopics"])->get()->pluck("code")->toArray();
//                $postData["exclude_user_topics"] = implode(";", $topics);
//            }
//
//            if(isset($targetings["userDevice"]) && $targetings["userDevice"]) {
//                $device = Device::where("id", $targetings["userDevice"])->first();
//                if($device)
//                    $postData["device"] = $device->identity;
//            }
//
//            if(isset($targetings["userChannels"]) && count($targetings["userChannels"]))
//                $postData["user_channels"] = implode(";", $targetings["userChannels"]);
//
//            if(isset($targetings["excludeUserChannels"]) && count($targetings["excludeUserChannels"]))
//                $postData["exclude_user_channels"] = implode(";", $targetings["excludeUserChannels"]);
//
//        }
//
//        $result = $this->tgRequest("post", "api?hash=" . $this->hash, $postData, true)
//            ->json();
////        Log::channel("ads")->info(print_r($postData, true));
//        return $result;
//    }
}