<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class HtmlEntity implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get($model, string $key, mixed $value, array $attributes): ?string
    {
        return html_entity_decode($value, ENT_QUOTES, "UTF-8");
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set( $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }
}