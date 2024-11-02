<?php

namespace App\Jobs;

use App\Events\TgParseAdUpdated;
use App\Models\Ad;
use App\Models\Cabinet;
use App\Models\Channel;
use App\Models\Device;
use App\Models\Language;
use App\Models\TargetCountry;
use App\Models\TargetLocation;
use App\Models\Timezone;
use App\Models\Topic;
use App\Services\ScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPHtmlParser\Dom;

class TgParseAdInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Ad $ad;
    private Cabinet $cabinet;
    private $dom;

    /**
     * Create a new job instance.
     */
    public function __construct(Ad $ad)
    {
        $this->onConnection('redis');
        $this->onQueue("ads");

        $this->ad = $ad;
        $this->cabinet = $ad->cabinet;
        $this->dom = new Dom;
    }

    private function getTargetType(): ?string
    {
        $htmlUser = $this->dom->find('[data-name="countries"] .selected-items');
        $htmlChannel = $this->dom->find('[data-name="langs"] .selected-items');

        if(trim($htmlUser))
            return 'user';
        if(trim($htmlChannel))
            return 'channel';
        return null;
    }

    private function checkboxIsChecked($name): bool
    {
        $elem = $this->dom->find('[name="'.$name.'"]');
        if($elem && isset($elem[0]))
            return $this->dom->find('[name="'.$name.'"]')[0]->hasAttribute("checked");
        return false;
    }

    private function loadTarget($category){
        $html = $this->dom->find('[data-name="'.$category.'"] .selected-items');
        $ids = [];
        foreach($html[0] AS $item){
            $code = $item->getAttribute("data-val");
            if($code)
                $ids[] = $code;
        }
        return $ids;
    }

    private function parseDomChannels($category = 'channels'){
        $channels = $this->dom->find('[data-name="'.$category.'"] .selected-items')[0];
        $channelIds = [];
        foreach($channels AS $channel){
            $channelInfo = [];
            $tg_id = $channel->getAttribute("data-val");
            if(!$tg_id)
                continue;
            $img = $channel->find(".selected-item-photo img");//->getAttribute("src");
            preg_match('#src="(.*)"#', $img, $img);
            $channelInfo["photo"] = $img[1];
            $title = $channel->find(".label");
            $channelInfo["title"] = strip_tags($title);

            $channelDb = Channel::firstOrCreate(
                ["tg_id" => $tg_id],
                $channelInfo
            );
            if($channelDb)
                $channelIds[] = $channelDb->id;
        }
        return $channelIds;
    }

    private function getDeviceIdentity()
    {
        try {
            $deviceName = $this->dom->find(".pr-form-column", 1)
                ->find(".form-group", 5)
                ->find(".input")->innerHtml;
            $devices = Device::where("name", $deviceName)->get()->pluck("id");
            return $devices ?? null;
        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adHtml = $this->cabinet->tgAd($this->ad->external_ad_id);
        $this->dom->loadStr($adHtml);

        $trg_type = $this->getTargetType();

        $ad_text = $this->dom->find('#ad_text')[0]->text;
        $ad_website_name = $this->dom->find('#ad_website_name')[0]?->value;

        $this->ad->picture = $this->checkboxIsChecked("picture");

        $activeChecked = $this->dom->find('[name="active"]', 0)->hasAttribute("checked");
        $this->ad->after_status_id = $activeChecked ? 1 : 2;
        $this->ad->use_schedule = $this->checkboxIsChecked("use_schedule");

        $scheduleService = new ScheduleService();
        $this->ad->schedule = $scheduleService->getSerializedScheduleFrom($this->dom->find('[name="schedule"]', 0)->value);
        $timeZoneValue = $this->dom->find('[name="schedule_tz"]', 0)->value;
        $this->ad->timezone_id = Timezone::where("value", $timeZoneValue)->first()?->id;
        $this->ad->is_timezone_custom = $this->dom->find('[name="schedule_tz_custom"]', 0)->value;

        if($trg_type == 'user')
        {
            $targetCountryCodes = $this->loadTarget("countries");
            $targetLocationCodes = $this->loadTarget("locations");
            $targetUserLangCodes = $this->loadTarget("user_langs");
            $targetUserTopicCodes = $this->loadTarget("user_topics");
            $targetExcludeUserTopicCodes = $this->loadTarget("exclude_user_topics");

            $targetUserChannelAudienceIds = $this->parseDomChannels("user_channels");
            $targetExcludeUserChannelAudienceIds = $this->parseDomChannels("exclude_user_channels");

            $device = $this->getDeviceIdentity();

//            $this->ad->intersect_topics = $this->checkboxIsChecked("intersect_topics");
//            $this->ad->exclude_politic = $this->checkboxIsChecked("exclude_politic");

            if(count($targetCountryCodes))
            {
                $idTargetCountryCodes = TargetCountry::whereIn("code", $targetCountryCodes)->get()->pluck("id");
                $this->ad->countries()->sync($idTargetCountryCodes);
            }

            if(count($targetLocationCodes))
            {
                $idTargetLocationCodes = TargetLocation::whereIn("code", $targetLocationCodes)->get()->pluck("id");
                $this->ad->locations()->sync($idTargetLocationCodes);
            }

            if(count($targetUserLangCodes))
            {
                $idTargetUserLangCodes = Language::whereIn("code", $targetUserLangCodes)->get()->pluck("id");
                $this->ad->userLanguage()->sync($idTargetUserLangCodes);
            }

            if(count($targetUserTopicCodes))
            {
                $idTargetTopicCodes = Topic::whereIn("code", $targetUserTopicCodes)->get()->pluck("id");
                $this->ad->userTopics()->sync($idTargetTopicCodes);
            }

            if(count($targetExcludeUserTopicCodes))
            {
                $idTargetExcludeTopicCodes = Topic::whereIn("code", $targetExcludeUserTopicCodes)->get()->pluck("id");
                $this->ad->userExcludeTopics()->sync($idTargetExcludeTopicCodes);
            }

            if(count($targetUserChannelAudienceIds))
                $this->ad->userChannels()->sync($targetUserChannelAudienceIds);

            if(count($targetExcludeUserChannelAudienceIds))
                $this->ad->excludeUserChannels()->sync($targetExcludeUserChannelAudienceIds);

            if($device)
                $this->ad->userDevices()->sync($device);

        }
        elseif($trg_type == 'channel')
        {
            $langCodes = $this->loadTarget("langs");
            $topicCodes = $this->loadTarget("topics");
            $channelIds = $this->parseDomChannels();
            $eTopicCodes = $this->loadTarget("exclude_topics");
            $eChannelIds = $this->parseDomChannels("exclude_channels");

            if(count($langCodes)) {
                $idLangs = Language::whereIn("code", $langCodes)->get()->pluck("id");
                $this->ad->languages()->sync($idLangs);
            }

            if(count($topicCodes)){
                $idTopics = Topic::whereIn("code", $topicCodes)->get()->pluck("id");
                $this->ad->topics()->sync($idTopics);
            }

            if(count($eTopicCodes)){
                $idTopics = Topic::whereIn("code", $eTopicCodes)->get()->pluck("id");
                $this->ad->excludeTopics()->sync($idTopics);
            }

            if(count($channelIds))
                $this->ad->channels()->sync($channelIds);

            if(count($eChannelIds))
                $this->ad->excludeChannels()->sync($eChannelIds);
        }

        $this->ad->trg_type = $trg_type;
        $this->ad->text = $ad_text;
        $this->ad->website_name = $ad_website_name;
        $this->ad->save();

        $mediaIfExist = $this->dom->find('[name="media"]')[0]?->value;

        if ($mediaIfExist) {
            $dataPreview = $adHtml["s"]["previewData"]["media"];

            preg_match("#background\-image\:url\(\'(.*)\'\)#", $dataPreview, $tgMediaUrl);

            $this->ad->files()->firstOrCreate([
                "filepath"     => $tgMediaUrl[1],
                "media" => $mediaIfExist
            ]);
        }

        event(new TgParseAdUpdated($this->ad));
    }

}