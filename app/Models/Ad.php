<?php

namespace App\Models;

use App\Casts\HtmlEntity;
use App\Casts\Serialize;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        "status_id",
        "after_status_id",
        "user_id",
        "external_ad_id",
        "title",
        "text",
        "promote_url",
        "website_name",
        "views",
        "cpm",
        "budget",
        "cabinet_id",
        "company_id",
        "trg_type",
        "ad_info",
        "picture",
        "use_schedule",
        "schedule",
        "timezone_id",
        "is_timezone_custom"
    ];

    protected $hidden = [
        "langs",
        "channels",
        "exclude_topics",
        "exclude_channels",
    ];

    public $casts = [
        "text"     => HtmlEntity::class,
        "schedule" => Serialize::class
    ];

    public function getNextId(): int
    {
        return Ad::orderBy('id', 'desc')->first()->id + 1;
    }

//    public function link(){
//        return $this->hasOne(Link::class, 'ad_id', 'id');
//    }

//    public function botLinkSubscribers(){
//        return $this->hasManyThrough(
//            BotLinkSubscriber::class,
//            BotLink::class,
//            'ad_id',
//            'bot_link_id',
//            'id',
//            'id'
//        );
//    }

    public function afterStatusCode(): string
    {
        return $this->after_status_id == 1 ? "1" : "0";
    }

//    public function conversations(){
//        return $this->hasMany(Conversation::class, 'ad_id', 'id');
//    }

    public function getTargetType()
    {
        return $this->trg_type;
    }

//    public function active(){
//        return $this->where("status_id", AdStatus::STATUS_ACTIVE);
//    }

//    public function status(){
//        return $this->belongsTo(AdStatus::class);
//    }

    public function stats()
    {
        return $this->hasMany(AdStat::class);
    }

    public function statMinutes()
    {
        return $this->hasMany(AdStatMinute::class);
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'ad_channels')->using(AdChannel::class);
    }
//
//    /*
//     * User Targets Start
//     */
//
    public function countries()
    {
        return $this->belongsToMany(TargetCountry::class, 'ad_target_countries', 'ad_id', 'target_country_id')->using(AdTargetCountry::class);
    }

    public function locations()
    {
        return $this->belongsToMany(TargetLocation::class, 'ad_target_locations', 'ad_id', 'target_location_id')->using(AdTargetLocation::class);
    }
//
    public function userLanguage()
    {
        return $this->belongsToMany(Language::class, 'ad_user_languages', 'ad_id', 'lang_id')->using(AdUserLanguage::class);
    }
    public function userTopics()
    {
        return $this->belongsToMany(Topic::class, 'ad_user_topics', 'ad_id', 'topic_id')->using(AdUserTopic::class);
    }

    public function userExcludeTopics()
    {
        return $this->belongsToMany(Topic::class, 'ad_exclude_user_topics', 'ad_id', 'topic_id')->using(AdExcludeUserTopic::class);
    }
//
//    public function userAudiences(){
//        return $this->belongsToMany(CabinetAudience::class, 'ad_user_audiences', 'ad_id', 'audience_id')->using(AdUserAudience::class);
//    }
//
//    public function userExcludeAudiences(){
//        return $this->belongsToMany(CabinetAudience::class, 'ad_exclude_user_audiences', 'ad_id', 'audience_id')->using(AdExcludeUserAudience::class);
//    }

    public function userChannels()
    {
        return $this->belongsToMany(Channel::class, 'ad_user_channel_audiences')->using(AdUserChannelAudience::class);
    }
//
    public function excludeUserChannels()
    {
        return $this->belongsToMany(Channel::class, 'ad_exclude_user_channel_audiences')->using(AdExcludeUserChannelAudience::class);
    }
//
    public function userDevices()
    {
        return $this->belongsToMany(Device::class, "ad_user_devices")->using(AdUserDevice::class);
    }

    /*
     * User Targets Finish
     */

    public function channelList()
    {
        return $this->belongsToMany(Channel::class, 'ad_channels', 'ad_id', 'channel_id');
    }

    public function excludeChannelList()
    {
        return $this->belongsToMany(Channel::class, 'ad_exclude_channels', 'ad_id', 'channel_id');
    }

//    public function languageList(){
//        return $this->belongsToMany(Language::class, 'ad_language', 'ad_id', 'lang_id');
//    }
//
//    public function topicList(){
//        return $this->belongsToMany(Topic::class, 'ad_topic', 'ad_id', 'topic_id');
//    }
//
//    public function excludeTopicList(){
//        return $this->belongsToMany(Topic::class, 'ad_exclude_topic', 'ad_id', 'topic_id');
//    }

    public function excludeChannels()
    {
        return $this->belongsToMany(Channel::class, 'ad_exclude_channels')->using(AdExcludeChannel::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'ad_language', 'ad_id', 'lang_id')->using(AdLanguage::class);
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'ad_topic', 'ad_id', 'topic_id')->using(AdTopic::class);
    }

    public function excludeTopics(){
        return $this->belongsToMany(Topic::class, 'ad_exclude_topic', 'ad_id', 'topic_id')->using(AdExcludeTopic::class);
    }
//
//    public function company(){
//        return $this->belongsTo(Company::class);
//    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

//    public function rules(){
//        return $this->morphMany(Rule::class, 'ruleable');
//    }

    public function getFullPromoteUrl(): string
    {
        $promote_url = str_replace("http://", "https://", $this->promote_url);
        if (!str_starts_with($promote_url, "https://"))
            return "https://" . $promote_url;
        return $promote_url;
    }

//    public function ordCreative(){
//        return $this->hasOne(OrdCreative::class, "ad_id", "id");
//    }

    public function hasAdInfo(): bool
    {
        return $this->ad_info !== null;
    }
}