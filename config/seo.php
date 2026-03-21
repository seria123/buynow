<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SEO Configuration
    |--------------------------------------------------------------------------
    */
    
    // Site name
    'site_name' => env('SEO_SITE_NAME', 'Buynow'),
    
    // Site description
    'site_description' => env('SEO_SITE_DESCRIPTION', 'Premium electronics and gadgets online store in Kenya'),
    
    // Site URL
    'site_url' => env('APP_URL', 'http://localhost'),
    
    // Default image for social sharing
    'default_image' => env('SEO_DEFAULT_IMAGE', '/images/og-image.png'),
    
    // Twitter username (without @)
    'twitter_site' => env('SEO_TWITTER_SITE', ''),
    
    // Twitter creator username
    'twitter_creator' => env('SEO_TWITTER_CREATOR', ''),
    
    // Locale
    'locale' => env('SEO_LOCALE', 'en_KE'),
    
    // Site language
    'language' => env('SEO_LANGUAGE', 'English'),
    
    // Copyright
    'copyright' => env('SEO_COPYRIGHT', '© ' . date('Y') . ' Buynow. All rights reserved.'),
    
    // robots.txt content
    'robots' => [
        'index' => true,
        'follow' => true,
    ],
];
