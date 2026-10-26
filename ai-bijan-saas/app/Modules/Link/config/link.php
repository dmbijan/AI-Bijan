<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Link Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options untuk Dynamic Links, Digital Cards & Click Tracking.
    |
    */

    // Link Page settings
    'link_page' => [
        'enabled' => true,
        'short_code_length' => 8,
        'max_links_per_tenant' => 1000,
        'default_status' => 'active',
        'allow_custom_slugs' => true,
        'require_approval' => false,
        'expiry_default_days' => 365,
        'password_required' => false,
    ],

    // Digital Card settings
    'digital_card' => [
        'enabled' => true,
        'short_code_length' => 8,
        'max_cards_per_tenant' => 500,
        'default_status' => 'active',
        'templates' => [
            'business' => 'Business Card',
            'personal' => 'Personal Card',
            'minimal' => 'Minimal Card',
            'creative' => 'Creative Card',
        ],
        'default_template' => 'business',
        'vcard_enabled' => true,
        'qr_code_enabled' => true,
        'social_links_enabled' => true,
        'custom_css_enabled' => true,
        'expiry_default_days' => 365,
    ],

    // Click Tracking settings
    'tracking' => [
        'enabled' => true,
        'track_ip' => true,
        'track_user_agent' => true,
        'track_referrer' => true,
        'track_geolocation' => true,
        'track_device' => true,
        'track_browser' => true,
        'track_os' => true,
        'cookie_duration_days' => 30,
        'unique_click_window_hours' => 24,
        'bot_detection' => true,
        'rate_limit_per_ip' => 100,
        'rate_limit_period_seconds' => 60,
    ],

    // URL Shortener settings
    'url_shortener' => [
        'enabled' => true,
        'prefix' => '/go/',
        'domain' => null, // Use current domain if null
        'redirect_type' => '302', // 301 or 302
        'append_source_params' => true,
        'utm_auto_append' => true,
    ],

    // QR Code settings
    'qr_code' => [
        'enabled' => true,
        'size' => 300, // pixels
        'format' => 'png', // png, svg, jpg
        'margin' => 10,
        'color' => '#000000',
        'background' => '#ffffff',
    ],

    // vCard settings
    'vcard' => [
        'enabled' => true,
        'version' => '3.0',
        'include_logo' => true,
        'max_logo_size_kb' => 100,
        'supported_fields' => [
            'name',
            'company',
            'job_title',
            'email',
            'phone',
            'website',
            'address',
            'linkedin',
            'twitter',
            'facebook',
            'instagram',
        ],
    ],

    // Analytics settings
    'analytics' => [
        'enabled' => true,
        'retention_days' => 365,
        'real_time_enabled' => true,
        'top_performers_count' => 10,
        'trends_periods' => ['daily', 'weekly', 'monthly'],
    ],

    // Currency
    'currency' => 'MYR',

    // Feature flags
    'features' => [
        'link_analytics' => true,
        'card_analytics' => true,
        'bulk_operations' => true,
        'api_access' => true,
        'custom_domains' => false,
        'white_label' => false,
    ],
];
