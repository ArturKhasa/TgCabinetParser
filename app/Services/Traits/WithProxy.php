<?php

namespace App\Services\Traits;

use App\Models\Proxy;
use Illuminate\Support\Facades\Redis;

trait WithProxy
{
    public function spaceProxy(){
        $key = "cabinet:space:proxy:" . $this->id;
        $timeout = 3600;
        $exists = Redis::exists($key);
        if($exists) {
            return json_decode(Redis::get($key));
        }
        else
        {
            $proxyRow = $this->space->proxy;
            if(!$proxyRow)
            {
                $proxyRow = Proxy::where("active", true)->first();
                if($proxyRow) {
                    $proxyRow->activate();
                    $this->space->updateProxy($proxyRow->id);
                    Redis::set($key, $proxyRow, 'EX', $timeout);
                }
                else
                    return false;
            }
            else
                Redis::set($key, $proxyRow, 'EX', $timeout);
            return $proxyRow;
        }
    }
}