<?php

namespace App\Services\Traits;

use App\Models\Ad;
use App\Models\AdStatus;

trait TelegramService
{
    public function adChangeStatus(Ad $ad, AdStatus $status): bool
    {
        try {
            if ($status->isDelete())
                $result = $this->tgDeleteAd($ad);
            else
                $result = $this->tgEditAdStatus($ad, $status);

            if (isset($result["error"]) && $result["error"] == 'Ad not found')
                $ad->status_id = AdStatus::STATUS_DELETED;
            elseif (isset($result["ok"]) && $result["ok"])
                $ad->status_id = $status->id;
            $ad->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function adEditAd(Ad $ad, $data)
    {
        try {

            $postData = [];
            $adDataKeys = ["title", "text", "promote_url", "cpm"];
            foreach ($adDataKeys as $key) {
                if (isset($data[$key]) && $data[$key]) {
                    $postData[$key] = $data[$key];
                } else
                    $postData[$key] = $ad->$key;

                if ($key == 'promote_url')
                    $postData[$key] = str_replace("https://", "", $postData[$key]);
            }
            if ($ad->website_name)
                $postData["website_name"] = $ad->website_name;

            $result = $this->tgEditAdFromArray($ad, $postData);
            if (isset($result["ok"]) && $result["ok"]) {
                foreach ($postData as $key => $value) {
                    $ad->$key = $value;
                }
                $ad->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function adEditCPM(Ad $ad, $cpmValue)
    {
        try {
            $result = $this->tgEditAdCPM($ad, $cpmValue);
            if (isset($result["ok"]) && $result["ok"]) {
                $ad->cpm = $cpmValue;
                $ad->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function adIncrementBudget(Ad $ad, $budget)
    {
        try {
            $result = $this->tgIncrementAdBudget($ad, $budget);
            if (isset($result["ok"]) && $result["ok"]) {
                $ad->increment("budget", $budget);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}