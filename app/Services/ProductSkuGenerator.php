<?php

namespace App\Services;

use App\Data\ProductSkuPatternData;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Settings\ProductSkuSettings;
use Illuminate\Support\Str;

class ProductSkuGenerator
{
    public function __construct(
        protected ProductSkuSettings $settings
    ) {}

    public function generate(?string $categoryId = null): string
    {
        do {
            $sku = $this->buildSku($categoryId, false);
        } while ($this->skuExists($sku));

        return $sku;
    }

    public function generateForVariant(?string $categoryId = null): string
    {
        do {
            $sku = $this->buildSku($categoryId, true);
        } while ($this->skuExists($sku) || $this->variantSkuExists($sku));

        return $sku;
    }

    public function generateForVariantFromName(?string $categoryId, string $variantName): string
    {
        $baseSku = $this->buildSkuFromVariantName($categoryId, $variantName);
        $sku = $baseSku;
        $counter = 1;

        while ($this->skuExists($sku) || $this->variantSkuExists($sku)) {
            $sku = $baseSku.'-'.str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $sku;
    }

    protected function buildSku(?string $categoryId, bool $isVariant = false): string
    {
        $pattern = $this->findPattern($categoryId);
        $uuid = strtoupper(Str::uuid()->toString());
        // Remove hyphens and take first 8 characters
        $shortUuid = substr(str_replace('-', '', $uuid), 0, 8);

        if ($pattern === null) {
            $prefix = $isVariant ? $this->defaultVariantPrefix($categoryId) : $this->defaultPrefix($categoryId);
            $separator = '-';
        } else {
            $patternPrefix = strtoupper(trim($pattern['prefix']));
            // Prepend SKU- to custom pattern prefix if it doesn't already start with it
            $prefix = str_starts_with($patternPrefix, 'SKU-') ? $patternPrefix : 'SKU-'.$patternPrefix;
            $separator = $pattern['separator'] ?? '-';
        }

        $prefix = strtoupper(trim($prefix));
        $separator = $separator === '' ? '-' : $separator;

        return $prefix === ''
            ? $shortUuid
            : sprintf('%s%s%s', $prefix, $separator, $shortUuid);
    }

    protected function findPattern(?string $categoryId): ?array
    {
        if ($categoryId === null) {
            return null;
        }

        return collect($this->settings->category_patterns)
            ->map(fn ($pattern) => $this->normalizePattern($pattern))
            ->firstWhere('category_id', $categoryId);
    }

    protected function defaultPrefix(?string $categoryId): string
    {
        $category = $categoryId ? Category::query()->find($categoryId) : null;

        if ($category === null) {
            return 'SKU';
        }

        $categoryName = $category->name ?? 'Unknown';
        // Sanitize category name: remove special characters but keep spaces and basic alphanumeric
        $sanitizedName = preg_replace('/[^A-Z0-9\s-]/', '', strtoupper($categoryName));
        $sanitizedName = trim($sanitizedName);

        return sprintf('SKU(%s)', $sanitizedName);
    }

    protected function defaultVariantPrefix(?string $categoryId): string
    {
        $category = $categoryId ? Category::query()->find($categoryId) : null;

        if ($category === null) {
            return 'SKU-VAR';
        }

        $categoryName = $category->name ?? 'Unknown';
        // Sanitize category name: remove special characters but keep spaces and basic alphanumeric
        $sanitizedName = preg_replace('/[^A-Z0-9\s-]/', '', strtoupper($categoryName));
        $sanitizedName = trim($sanitizedName);

        return sprintf('SKU-VAR(%s)', $sanitizedName);
    }

    protected function skuExists(string $sku): bool
    {
        return Product::query()->where('sku', $sku)->exists();
    }

    protected function buildSkuFromVariantName(?string $categoryId, string $variantName): string
    {
        // Get category abbreviation
        $category = $categoryId ? Category::query()->find($categoryId) : null;
        $catAbbr = 'CAT';
        if ($category) {
            $categoryName = $category->name ?? 'Unknown';
            $sanitized = preg_replace('/[^A-Z0-9]/', '', strtoupper($categoryName));
            $catAbbr = substr($sanitized, 0, 3) ?: 'CAT';
        }

        // Generate 8-character UUID
        $uuid = strtoupper(Str::uuid()->toString());
        $shortUuid = substr(str_replace('-', '', $uuid), 0, 8);

        return sprintf('SKU-%s-VAR-%s', $catAbbr, $shortUuid);
    }

    protected function variantSkuExists(string $sku): bool
    {
        return ProductVariant::query()->where('sku', $sku)->exists();
    }

    protected function normalizePattern(mixed $pattern): array
    {
        if ($pattern instanceof ProductSkuPatternData) {
            return $pattern->toArray();
        }

        return is_array($pattern) ? $pattern : [];
    }
}
