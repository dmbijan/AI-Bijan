<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SocialBridge — penyiaran silang & penjadualan sosial
    |--------------------------------------------------------------------------
    |
    | Berasal daripada https://www.post-bridge.com/
    |
    | Siaran silang ke 10 platform, penjadualan, pengurusan kandungan,
    | carousel, video pukal dan analitik.
    */

    'enabled' => (bool) env('SOCIAL_BRIDGE_ENABLED', true),

    /*
    | Platform yang disokong.
    */
    'platforms' => [
        'x' => ['label' => 'X (Twitter)', 'max_chars' => 280, 'media' => true, 'carousel' => false],
        'instagram' => ['label' => 'Instagram', 'max_chars' => 2200, 'media' => true, 'carousel' => true],
        'linkedin' => ['label' => 'LinkedIn', 'max_chars' => 3000, 'media' => true, 'carousel' => false],
        'facebook' => ['label' => 'Facebook', 'max_chars' => 63206, 'media' => true, 'carousel' => true],
        'tiktok' => ['label' => 'TikTok', 'max_chars' => 2200, 'media' => true, 'carousel' => false],
        'youtube' => ['label' => 'YouTube', 'max_chars' => 5000, 'media' => true, 'carousel' => false],
        'bluesky' => ['label' => 'Bluesky', 'max_chars' => 300, 'media' => true, 'carousel' => false],
        'threads' => ['label' => 'Threads', 'max_chars' => 500, 'media' => true, 'carousel' => true],
        'pinterest' => ['label' => 'Pinterest', 'max_chars' => 500, 'media' => true, 'carousel' => true],
        'google_business' => ['label' => 'Google Business', 'max_chars' => 1500, 'media' => true, 'carousel' => false],
    ],

    /*
    | Had.
    */
    'limits' => [
        'max_accounts' => (int) env('SOCIAL_BRIDGE_MAX_ACCOUNTS', 99),
        'max_posts_per_day' => (int) env('SOCIAL_BRIDGE_MAX_POSTS_DAILY', 500),
        'batch_size' => (int) env('SOCIAL_BRIDGE_BATCH', 100),
        'max_media_per_post' => (int) env('SOCIAL_BRIDGE_MAX_MEDIA', 10),
    ],

    /*
    | Penjadualan.
    */
    'scheduling' => [
        'timezone' => env('SOCIAL_BRIDGE_TIMEZONE', 'Asia/Kuala_Lumpur'),
        'min_lead_minutes' => (int) env('SOCIAL_BRIDGE_MIN_LEAD', 1),
        'max_advance_days' => (int) env('SOCIAL_BRIDGE_MAX_ADVANCE', 365),
        'retry_failed_after_minutes' => (int) env('SOCIAL_BRIDGE_RETRY', 15),
    ],

    /*
    | Kadar siaran (elak kena had platform).
    */
    'rate_limit' => [
        'per_account_per_hour' => (int) env('SOCIAL_BRIDGE_RATE_HOURLY', 30),
        'delay_between_posts_seconds' => (int) env('SOCIAL_BRIDGE_DELAY', 2),
    ],

    /*
    | Analitik.
    */
    'analytics' => [
        'enabled' => (bool) env('SOCIAL_BRIDGE_ANALYTICS', true),
        'refresh_hours' => (int) env('SOCIAL_BRIDGE_ANALYTICS_REFRESH', 6),
    ],

];
