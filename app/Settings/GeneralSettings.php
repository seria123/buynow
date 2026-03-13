<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $site_email;
    public ?string $site_logo;
    public ?string $site_favicon;
    public string $currency;
    public string $currency_symbol;
    public string $timezone;
    public string $locale;
    public string $date_format;
    public string $time_format;
    public bool $maintenance_mode;
    public ?string $maintenance_message;

    public static function group(): string
    {
        return 'general';
    }

    public static function locks(): array
    {
        return [];
    }

    public static function fields(): array
    {
        return [
            'site_name' => [
                'type' => 'string',
                'label' => 'Site Name',
                'description' => 'The name of your application',
            ],
            'site_email' => [
                'type' => 'string',
                'label' => 'Site Email',
                'description' => 'Primary contact email address',
            ],
            'site_logo' => [
                'type' => 'string',
                'label' => 'Site Logo',
                'description' => 'URL to the site logo',
            ],
            'site_favicon' => [
                'type' => 'string',
                'label' => 'Site Favicon',
                'description' => 'URL to the site favicon',
            ],
            'currency' => [
                'type' => 'string',
                'label' => 'Currency Code',
                'description' => 'ISO 4217 currency code (e.g., USD, EUR)',
            ],
            'currency_symbol' => [
                'type' => 'string',
                'label' => 'Currency Symbol',
                'description' => 'Currency symbol (e.g., $, €, £)',
            ],
            'timezone' => [
                'type' => 'string',
                'label' => 'Timezone',
                'description' => 'PHP timezone identifier',
            ],
            'locale' => [
                'type' => 'string',
                'label' => 'Locale',
                'description' => 'Application locale code',
            ],
            'date_format' => [
                'type' => 'string',
                'label' => 'Date Format',
                'description' => 'PHP date format',
            ],
            'time_format' => [
                'type' => 'string',
                'label' => 'Time Format',
                'description' => 'PHP time format',
            ],
            'maintenance_mode' => [
                'type' => 'boolean',
                'label' => 'Maintenance Mode',
                'description' => 'Enable to show maintenance page to visitors',
            ],
            'maintenance_message' => [
                'type' => 'string',
                'label' => 'Maintenance Message',
                'description' => 'Message to show when in maintenance mode',
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'site_name' => 'BuyNow',
            'site_email' => 'support@buynow.com',
            'site_logo' => null,
            'site_favicon' => null,
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'Africa/Nairobi',
            'locale' => 'en',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'maintenance_mode' => false,
            'maintenance_message' => 'We are currently under maintenance. Please check back soon.',
        ];
    }
}
