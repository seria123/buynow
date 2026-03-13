<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TaxSettings extends Settings
{
    public bool $tax_enabled;
    public string $tax_type; // 'inclusive' or 'exclusive'
    public float $default_tax_rate;
    public ?string $tax_number;
    public bool $price_includes_tax;
    
    // Per-country tax rates (stored as JSON array)
    public array $country_tax_rates;

    public static function group(): string
    {
        return 'tax';
    }

    public static function locks(): array
    {
        return [];
    }

    public static function fields(): array
    {
        return [
            'tax_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Tax',
                'description' => 'Enable tax calculations',
            ],
            'tax_type' => [
                'type' => 'string',
                'label' => 'Tax Type',
                'description' => 'How tax is applied (inclusive or exclusive)',
            ],
            'default_tax_rate' => [
                'type' => 'number',
                'label' => 'Default Tax Rate (%)',
                'description' => 'Default tax rate percentage',
            ],
            'tax_number' => [
                'type' => 'string',
                'label' => 'Tax Number/VAT Number',
                'description' => 'Your tax registration number',
            ],
            'price_includes_tax' => [
                'type' => 'boolean',
                'label' => 'Prices Include Tax',
                'description' => 'Whether product prices include tax',
            ],
            'country_tax_rates' => [
                'type' => 'array',
                'label' => 'Country-specific Tax Rates',
                'description' => 'Tax rates per country (JSON format)',
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'tax_enabled' => true,
            'tax_type' => 'exclusive',
            'default_tax_rate' => 16.0,
            'tax_number' => null,
            'price_includes_tax' => false,
            'country_tax_rates' => [
                ['country' => 'KE', 'name' => 'VAT', 'rate' => 16.0],
                ['country' => 'UG', 'name' => 'VAT', 'rate' => 18.0],
                ['country' => 'TZ', 'name' => 'VAT', 'rate' => 18.0],
            ],
        ];
    }
}
