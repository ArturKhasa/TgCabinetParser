<?php

namespace App\Services\Traits;

trait HasAudiences
{
    public function tgAudiences()
    {
        try {
            return $this->tgRequest("get", "account/audiences")
                ->json()["s"]["audiencesList"];
        } catch (\Exception $e) {
            return false;
        }
    }

    public function tgAudiencesLoad(): bool
    {
        $result = $this->tgAudiences();
        if (!$result || !is_array($result))
            return false;
        foreach ($result as $audience) {
            $this->audiences()->updateOrCreate([
                "audience_id" => $audience["audience_id"]
            ], [
                "title" => $audience["title"],
                "size"  => $audience["size"],
                "users" => $audience["users"],
                "ads"   => $audience["ads"]
            ]);
        }
        return true;
    }
}