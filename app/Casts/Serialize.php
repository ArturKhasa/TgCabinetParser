<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Serialize implements CastsAttributes
{
    public function get($model, string $key, mixed $value, array $attributes): array
    {
        if($value)
            return unserialize($value);
        else
            return [];
    }

    public function set($model, string $key, mixed $value, array $attributes): string
    {
        return serialize($value);
    }
}