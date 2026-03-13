<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // General Settings
        $generalSettings = [
            ['site_name', 'BuyNow'],
            ['site_email', 'support@buynow.com'],
            ['site_logo', null],
            ['site_favicon', null],
            ['currency', 'USD'],
            ['currency_symbol', '$'],
            ['timezone', 'Africa/Nairobi'],
            ['locale', 'en'],
            ['date_format', 'Y-m-d'],
            ['time_format', 'H:i:s'],
            ['maintenance_mode', false],
            ['maintenance_message', 'We are currently under maintenance. Please check back soon.'],
        ];

        foreach ($generalSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'general', 'name' => $setting[0]],
                [
                    'locked' => false,
                    'payload' => json_encode($setting[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Email Settings
        $emailSettings = [
            ['mailer', 'smtp'],
            ['host', 'smtp.mailgun.org'],
            ['port', 587],
            ['username', null],
            ['password', null],
            ['encryption', 'tls'],
            ['from_address', 'noreply@buynow.com'],
            ['from_name', 'BuyNow'],
            ['mailgun_domain', null],
            ['mailgun_secret', null],
            ['ses_key', null],
            ['ses_secret', null],
            ['ses_region', 'us-east-1'],
        ];

        foreach ($emailSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'email', 'name' => $setting[0]],
                [
                    'locked' => false,
                    'payload' => json_encode($setting[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Payment Settings
        $paymentSettings = [
            ['mpesa_enabled', true],
            ['mpesa_environment', 'sandbox'],
            ['mpesa_consumer_key', null],
            ['mpesa_consumer_secret', null],
            ['mpesa_shortcode', null],
            ['mpesa_passkey', null],
            ['mpesa_callback_url', null],
            ['stripe_enabled', false],
            ['stripe_key', null],
            ['stripe_secret', null],
            ['stripe_webhook_secret', null],
            ['paypal_enabled', false],
            ['paypal_client_id', null],
            ['paypal_client_secret', null],
            ['paypal_mode', 'sandbox'],
            ['cod_enabled', true],
            ['cod_instructions', 'Payment will be collected upon delivery.'],
        ];

        foreach ($paymentSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'payments', 'name' => $setting[0]],
                [
                    'locked' => false,
                    'payload' => json_encode($setting[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Social Auth Settings
        $socialSettings = [
            ['google_enabled', false],
            ['google_client_id', null],
            ['google_client_secret', null],
            ['google_redirect', null],
            ['facebook_enabled', false],
            ['facebook_client_id', null],
            ['facebook_client_secret', null],
            ['facebook_redirect', null],
            ['twitter_enabled', false],
            ['twitter_client_id', null],
            ['twitter_client_secret', null],
            ['twitter_redirect', null],
        ];

        foreach ($socialSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'social_auth', 'name' => $setting[0]],
                [
                    'locked' => false,
                    'payload' => json_encode($setting[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Tax Settings
        $taxSettings = [
            ['tax_enabled', true],
            ['tax_type', 'exclusive'],
            ['default_tax_rate', 16.0],
            ['tax_number', null],
            ['price_includes_tax', false],
            ['country_tax_rates', [
                ['country' => 'KE', 'name' => 'VAT', 'rate' => 16.0],
                ['country' => 'UG', 'name' => 'VAT', 'rate' => 18.0],
                ['country' => 'TZ', 'name' => 'VAT', 'rate' => 18.0],
            ]],
        ];

        foreach ($taxSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'tax', 'name' => $setting[0]],
                [
                    'locked' => false,
                    'payload' => json_encode($setting[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // No need to delete - settings tables remain
    }
};
