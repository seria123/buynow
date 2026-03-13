<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    // M-Pesa Settings
    public bool $mpesa_enabled;
    public ?string $mpesa_environment;
    public ?string $mpesa_consumer_key;
    public ?string $mpesa_consumer_secret;
    public ?string $mpesa_shortcode;
    public ?string $mpesa_passkey;
    public ?string $mpesa_callback_url;
    
    // Stripe Settings
    public bool $stripe_enabled;
    public ?string $stripe_key;
    public ?string $stripe_secret;
    public ?string $stripe_webhook_secret;
    
    // PayPal Settings
    public bool $paypal_enabled;
    public ?string $paypal_client_id;
    public ?string $paypal_client_secret;
    public ?string $paypal_mode;
    
    // COD Settings
    public bool $cod_enabled;
    public ?string $cod_instructions;

    public static function group(): string
    {
        return 'payments';
    }

    public static function locks(): array
    {
        return [];
    }

    public static function fields(): array
    {
        return [
            'mpesa_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable M-Pesa',
                'description' => 'Enable M-Pesa as a payment option',
            ],
            'mpesa_environment' => [
                'type' => 'string',
                'label' => 'M-Pesa Environment',
                'description' => 'sandbox or production',
            ],
            'mpesa_consumer_key' => [
                'type' => 'string',
                'label' => 'M-Pesa Consumer Key',
                'description' => 'M-Pesa Daraja API consumer key',
            ],
            'mpesa_consumer_secret' => [
                'type' => 'string',
                'label' => 'M-Pesa Consumer Secret',
                'description' => 'M-Pesa Daraja API consumer secret',
            ],
            'mpesa_shortcode' => [
                'type' => 'string',
                'label' => 'M-Pesa Shortcode',
                'description' => 'M-Pesa business shortcode',
            ],
            'mpesa_passkey' => [
                'type' => 'string',
                'label' => 'M-Pesa Passkey',
                'description' => 'M-Pesa passkey for STK Push',
            ],
            'mpesa_callback_url' => [
                'type' => 'string',
                'label' => 'M-Pesa Callback URL',
                'description' => 'URL for M-Pesa payment callbacks',
            ],
            'stripe_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Stripe',
                'description' => 'Enable Stripe as a payment option',
            ],
            'stripe_key' => [
                'type' => 'string',
                'label' => 'Stripe Publishable Key',
                'description' => 'Stripe public key',
            ],
            'stripe_secret' => [
                'type' => 'string',
                'label' => 'Stripe Secret Key',
                'description' => 'Stripe secret key',
            ],
            'stripe_webhook_secret' => [
                'type' => 'string',
                'label' => 'Stripe Webhook Secret',
                'description' => 'Stripe webhook signing secret',
            ],
            'paypal_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable PayPal',
                'description' => 'Enable PayPal as a payment option',
            ],
            'paypal_client_id' => [
                'type' => 'string',
                'label' => 'PayPal Client ID',
                'description' => 'PayPal API client ID',
            ],
            'paypal_client_secret' => [
                'type' => 'string',
                'label' => 'PayPal Client Secret',
                'description' => 'PayPal API client secret',
            ],
            'paypal_mode' => [
                'type' => 'string',
                'label' => 'PayPal Mode',
                'description' => 'sandbox or live',
            ],
            'cod_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Cash on Delivery',
                'description' => 'Enable COD as a payment option',
            ],
            'cod_instructions' => [
                'type' => 'string',
                'label' => 'COD Instructions',
                'description' => 'Instructions for customers paying via COD',
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'mpesa_enabled' => true,
            'mpesa_environment' => 'sandbox',
            'mpesa_consumer_key' => null,
            'mpesa_consumer_secret' => null,
            'mpesa_shortcode' => null,
            'mpesa_passkey' => null,
            'mpesa_callback_url' => null,
            'stripe_enabled' => false,
            'stripe_key' => null,
            'stripe_secret' => null,
            'stripe_webhook_secret' => null,
            'paypal_enabled' => false,
            'paypal_client_id' => null,
            'paypal_client_secret' => null,
            'paypal_mode' => 'sandbox',
            'cod_enabled' => true,
            'cod_instructions' => 'Payment will be collected upon delivery.',
        ];
    }
}
