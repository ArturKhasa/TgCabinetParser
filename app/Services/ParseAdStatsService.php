<?php

namespace App\Services;

use App\Events\TgParseAdCreated;
use App\Events\TgParseAdUpdated;
use App\Jobs\TgParseAdStatsByCabinet;
use App\Models\AdStatus;
use App\Models\Cabinet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PHPHtmlParser\Dom;

class ParseAdStatsService
{
    private $limit;

    public function __construct(
        private Cabinet $cabinet,
    )
    {
        $this->limit = $this->cabinet->space->getAdLimitBalance();
    }

    public static function parse(array $cabinetIds)
    {
        $cabinets = Cabinet::query()->whereIn('id', $cabinetIds)->get();

        foreach ($cabinets as $cabinet) {
            if ($cabinet->space->hasActivePlan() || $cabinet->is_clickise) {
                TgParseAdStatsByCabinet::dispatch($cabinet, $cabinet->is_new);
            }
        }
    }

    public function parseAds(array $cabinetIds)
    {

    }

    private function parseTgAdStatByMinutes($ad)
    {
        $cpm = $ad->cpm;
        $tgAdStat = $this->cabinet->tgStatsByMinutes($ad->external_ad_id);
        preg_match('#chart_count_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $joins);
        preg_match('#chart_budget_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $spent);

        $data = [];

        if (isset($joins[1])) {
            $stat = json_decode($joins[1], true);
            $statSpent = json_decode($spent[1], true);

            $i = 0;
            foreach ($stat["columns"][0] as $timestamp) {
                if (is_int($timestamp)) {
                    /*
                     * Совместить данные в каждый час, вместо запроса на каждые 5 минут
                     * Сделать тоже самое при парсинге по дням
                     */
                    $dateTime = $this->roundTime(Carbon::createFromTimestamp($timestamp / 1000), 60);
                    if (!isset($data[$dateTime])) {
                        $data[$dateTime] = [
                            "views"  => 0,
                            "clicks" => 0,
                            "joins"  => 0,
                            "spent"  => 0,
                            "cpm"    => $cpm
                        ];
                    }
                    $data[$dateTime]["views"] += $stat["columns"][1][$i] ?? 0;
                    $data[$dateTime]["clicks"] += $stat["columns"][2][$i] ?? 0;
                    $data[$dateTime]["joins"] += $stat["columns"][3][$i] ?? 0;

                    $s_spent = $statSpent["columns"][1][$i];
                    $data[$dateTime]["spent"] += $s_spent > 0 ? (float)$s_spent / 1000000 : 0;
                }
                $i++;
            }
        }

        $data = array_slice($data, count($data) - 3, count($data) - 1, true);
        foreach ($data as $datetime => $stat) {

            $ad->statMinutes()->updateOrCreate(
                ["datetime" => $datetime],
                $stat
            );

        }
    }

    private function roundTime($carbonTime, $minuteInterval)
    {
        return $carbonTime->setTime(
            $carbonTime->format('H'),
            floor($carbonTime->format('i') / $minuteInterval) * $minuteInterval,
            0
        )->timestamp;
    }

    private function partTgAdStat($ad)
    {
        $tgAdStatAll = $this->cabinet->tgStatsAll($ad->external_ad_id);
        $tgAdStat = $tgAdStatAll["j"];

        $cleanString = function ($string) {
            $search = array('&#8234;', '&lrm;', '&#8236;');
            $replace = array('', '', '');

            return str_replace($search, $replace, $string);
        };

        $dom = new Dom;
        $dom->loadStr($tgAdStatAll["h"]);
        $adTextHtml = $dom->find('.ad-msg-text')[0];
        $text = $cleanString(strip_tags($adTextHtml->innerHtml));

        $adLink = $dom->find(".ad-msg-btn")[0];
        $link = $adLink->getAttribute("href");
        $link = str_replace("&amp;", "&", $link);

        $adWebsiteNameHtml = $dom->find('.ad-msg-from')[0];
        $websiteName = $cleanString(strip_tags($adWebsiteNameHtml->innerHtml));

        $ad->update([
//            "text"          =>  $text,
            "promote_url"  => $link,
            "website_name" => $websiteName
        ]);

        preg_match('#chart_count_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $joins);
        preg_match('#chart_budget_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $spent);

        if (isset($joins[1])) {
            $stat = json_decode($joins[1], true);
            $statSpent = json_decode($spent[1], true);

            $i = 0;
            $lastI = count($stat["columns"][0]) - 1;
            $timestamp = $stat["columns"][0][$lastI];

            if ($timestamp && is_int($timestamp)) {
                $s_spent = $statSpent["columns"][1][$lastI];
                $date = Carbon::parse($timestamp / 1000)->format("Y-m-d");
                $dataToSave = [
                    "views"  => $stat["columns"][1][$lastI] ?? 0,
                    "clicks" => $stat["columns"][2][$lastI] ?? 0,
                    "joins"  => $stat["columns"][3][$lastI] ?? 0,
                    "spent"  => $s_spent > 0 ? (float)$s_spent / 1000000 : 0
                ];

                $ad->stats()->updateOrCreate(
                    ["date" => $date],
                    $dataToSave
                );
            }
        }
        event(new TgParseAdUpdated($ad));
    }

    private function fullTgAdStat($ad)
    {
        $tgAdStat = $this->cabinet->tgStats($ad->external_ad_id);
        preg_match('#chart_count_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $joins);
        preg_match('#chart_budget_stats_wrap\'\, (.*)\, true\)\;#isU', $tgAdStat, $spent);

        if (isset($joins[1])) {
            $stat = json_decode($joins[1], true);
            $statSpent = json_decode($spent[1], true);
            $i = 0;
            foreach ($stat["columns"][0] as $timestamp) {
                if (is_int($timestamp)) {
                    $s_spent = $statSpent["columns"][1][$i];
                    $date = Carbon::parse($timestamp / 1000)->format("Y-m-d");
                    $adStat = $ad->stats()->updateOrCreate(
                        ["date" => $date],
                        [
                            "views"  => $stat["columns"][1][$i] ?? 0,
                            "clicks" => $stat["columns"][2][$i] ?? 0,
                            "joins"  => $stat["columns"][3][$i] ?? 0,
                            "spent"  => $s_spent > 0 ? (float)$s_spent / 1000000 : 0
                        ]
                    );
                }
                $i++;
            }
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('handle');

        $adStatuses = AdStatus::get();
        $tgAds = $this->cabinet->tgAllAds();
        $strToFloat = function ($str) {
            return mb_substr(strip_tags($str), 1);
        };
        $tgAdIds = array_column($tgAds, "ad_id");
        if (count($tgAdIds))
            $this->cabinet->ads()->whereNotIn("external_ad_id", $tgAdIds)->update([
                "status_id" => AdStatus::STATUS_DELETED
            ]);

        $cabinetIsNew = $this->cabinet->is_new;

        foreach ($tgAds as $tgAd) {
            $ad = $this->cabinet->ads()->where("external_ad_id", $tgAd["ad_id"])->first();
            $promoteUrl = null;
            if (isset($tgAd["promote_url"]))
                $promoteUrl = str_replace("&amp;", "&", $tgAd["promote_url"]);
            if (isset($tgAd["tme_path"]) && strlen($tgAd["tme_path"]) > 0)
                $promoteUrl = "t.me/" . $tgAd["tme_path"];
            if (!$ad && $this->limit > 0) {
                $ad = $this->cabinet->ads()->create([
                    "external_ad_id" => $tgAd["ad_id"],
                    "title"          => $tgAd["title"],
                    "cpm"            => $tgAd["cpm"],
                    "budget"         => $tgAd["budget"],
                    "promote_url"    => $promoteUrl,
                    "status_id"      => $adStatuses->where("code", $tgAd["status"])->first()->id,
                    "company_id"     => $this->cabinet->defaultCompany?->id
                ]);
                try {
                    $this->fullTgAdStat($ad);
                    $this->parseTgAdStatByMinutes($ad);
                    $ad->update(["views" => $tgAd["views"]]);
                } catch (\Exception $e) {

                }
                $this->limit -= 1;
                TgParseAdCreated::dispatch($ad);
            } elseif ($ad) {
                $views = $tgAd["views"];
                $ad->update([
                    "title"       => $tgAd["title"],
                    "promote_url" => $promoteUrl,
                    "cpm"         => $tgAd["cpm"],
                    "budget"      => $tgAd["budget"],
                    "status_id"   => $adStatuses->where("code", $tgAd["status"])->first()->id,
                ]);
                if ($ad->views != $views) {
                    try {
                        $ad->update(["views" => $tgAd["views"]]);

                        if ($cabinetIsNew)
                            $this->fullTgAdStat($ad);
                        else
                            $this->partTgAdStat($ad);
                        $this->parseTgAdStatByMinutes($ad);
                    } catch (\Exception $e) {

                    }
                }
//                TgParseAdInfo::dispatch($ad);
                TgParseAdUpdated::dispatch($ad);
            }
        }

        if ($cabinetIsNew)
            $this->cabinet->update(["is_new" => false]);
    }
}