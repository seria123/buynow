<?php

namespace App\Settings;

use App\Data\ProductSkuPatternData;
use App\Settings\Casts\ProductSkuPatternArrayCast;
use Spatie\LaravelSettings\Settings;

class ProductSkuSettings extends Settings
{
    /** @var ProductSkuPatternData[] */
    public array $category_patterns = [];

    public static function group(): string
    {
        return 'catalogue';
    }

    public static function casts(): array
    {
        return [
            'category_patterns' => ProductSkuPatternArrayCast::class,
        ];
    }
}
