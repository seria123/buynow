<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialAuthSettings extends Settings
{
    // Google OAuth
    public bool $google_enabled;
    public ?string $google_client_id;
    public ?string $google_client_secret;
    public ?string $google_redirect;
    
    // Facebook OAuth
    public bool $facebook_enabled;
    public ?string $facebook_client_id;
    public ?string $facebook_client_secret;
    public ?string $facebook_redirect;
    
    // Twitter OAuth
    public bool $twitter_enabled;
    public ?string $twitter_client_id;
    public ?string $twitter_client_secret;
    public ?string $twitter_redirect;

    public static function group(): string
    {
        return 'social_auth';
    }

    public static function locks(): array
    {
        return [];
    }

    public static function fields(): array
    {
        return [
            'google_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Google Login',
                'description' => 'Allow users to sign in with Google',
            ],
            'google_client_id' => [
                'type' => 'string',
                'label' => 'Google Client ID',
                'description' => 'Google OAuth2 client ID',
            ],
            'google_client_secret' => [
                'type' => 'string',
                'label' => 'Google Client Secret',
                'description' => 'Google OAuth2 client secret',
            ],
            'google_redirect' => [
                'type' => 'string',
                'label' => 'Google Redirect URL',
                'description' => 'OAuth callback URL for Google',
            ],
            'facebook_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Facebook Login',
                'description' => 'Allow users to sign in with Facebook',
            ],
            'facebook_client_id' => [
                'type' => 'string',
                'label' => 'Facebook Client ID',
                'description' => 'Facebook OAuth app ID',
            ],
            'facebook_client_secret' => [
                'type' => 'string',
                'label' => 'Facebook Client Secret',
                'description' => 'Facebook OAuth app secret',
            ],
            'facebook_redirect' => [
                'type' => 'string',
                'label' => 'Facebook Redirect URL',
                'description' => 'OAuth callback URL for Facebook',
            ],
            'twitter_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Twitter Login',
                'description' => 'Allow users to sign in with Twitter',
            ],
            'twitter_client_id' => [
                'type' => 'string',
                'label' => 'Twitter Client ID',
                'description' => 'Twitter OAuth client ID',
            ],
            'twitter_client_secret' => [
                'type' => 'string',
                'label' => 'Twitter Client Secret',
                'description' => 'Twitter OAuth client secret',
            ],
            'twitter_redirect' => [
                'type' => 'string',
                'label' => 'Twitter Redirect URL',
                'description' => 'OAuth callback URL for Twitter',
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'google_enabled' => false,
            'google_client_id' => null,
            'google_client_secret' => null,
            'google_redirect' => null,
            'facebook_enabled' => false,
            'facebook_client_id' => null,
            'facebook_client_secret' => null,
            'facebook_redirect' => null,
            'twitter_enabled' => false,
            'twitter_client_id' => null,
            'twitter_client_secret' => null,
            'twitter_redirect' => null,
        ];
    }
}
