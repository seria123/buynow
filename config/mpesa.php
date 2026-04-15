<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Environment
    |--------------------------------------------------------------------------
    |
    | The environment for M-Pesa. Options: 'sandbox', 'production'
    |
    */
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Consumer Key
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa API consumer key from Safaricom Daraja portal
    |
    */
    'consumer_key' => env('MPESA_CONSUMER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Consumer Secret
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa API consumer secret from Safaricom Daraja portal
    |
    */
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Shortcode
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa business shortcode (usually 6 digits)
    |
    */
    'shortcode' => env('MPESA_SHORTCODE'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Passkey
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa passkey from Safaricom Daraja portal
    |
    */
    'passkey' => env('MPESA_PASSKEY'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Callback URL
    |--------------------------------------------------------------------------
    |
    | The URL where M-Pesa will send payment confirmation callbacks
    |
    */
    'callback_url' => env('MPESA_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Initiator
    |--------------------------------------------------------------------------
    |
    | The initiator name for M-Pesa transactions
    |
    */
    'initiator' => env('MPESA_INITIATOR', 'testapi'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Security Credential
    |--------------------------------------------------------------------------
    |
    | The security credential for M-Pesa transactions
    |
    */
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),

    /*
    |--------------------------------------------------------------------------
    | M-Pesa Base URLs
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],
];