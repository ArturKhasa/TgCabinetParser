<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param $model
     * @param string $key
     * @param $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */

    public function get($model, string $key, mixed $value, array $attributes): array
    {
        if($value)
            return json_decode($value, true);
        else
            return [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set($model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }
}