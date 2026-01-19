<?php

namespace App\Settings\Casts;

use App\Data\ProductSkuPatternData;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class ProductSkuPatternArrayCast implements SettingsCast
{
    public function get($payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        return array_map(
            fn ($pattern) => ProductSkuPatternData::from($pattern),
            $payload
        );
    }

    public function set($payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        return array_map(function ($pattern) {
            if ($pattern instanceof ProductSkuPatternData) {
                return $pattern->toArray();
            }

            if (is_array($pattern)) {
                return ProductSkuPatternData::from($pattern)->toArray();
            }

            return [];
        }, $payload);
    }
}
