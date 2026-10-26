<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Studio & Omnichannel Publishing (BAB 86 / Idea #21)
    |--------------------------------------------------------------------------
    |
    | Tetapan jana kandungan, penerbitan, moderasi komen & chat.
    |
    */

    'platforms' => [
        // Keupayaan lalai mengikut platform
        'capabilities' => [
            'facebook' => ['post', 'story', 'video', 'reel', 'carousel', 'comment', 'message', 'live'],
            'instagram' => ['post', 'story', 'reel', 'carousel', 'comment', 'message', 'live'],
            'tiktok' => ['post', 'video', 'comment', 'message'],
            'linkedin' => ['post', 'video', 'carousel', 'comment', 'message'],
            'x' => ['post', 'comment', 'message'],
            'whatsapp' => ['post', 'message', 'comment'],
            'youtube' => ['video', 'short', 'comment', 'message'],
            'telegram' => ['post', 'message', 'comment'],
            'google_business' => ['post', 'comment', 'message'],
        ],

        // Adapter platform (stub — perlukan OAuth token sebenar)
        'adapters' => [
            'facebook' => 'meta_graph',
            'instagram' => 'meta_graph',
            'tiktok' => 'tiktok_creative',
            'linkedin' => 'linkedin_api',
            'x' => 'x_api_v2',
            'whatsapp' => 'meta_cloud',
            'youtube' => 'google_api',
        ],
    ],

    'generation' => [
        // Task type AI Gateway
        'task_type' => env('CONTENT_AI_TASK_TYPE', 'content_generation'),
        'reply_task_type' => env('CONTENT_AI_REPLY_TASK_TYPE', 'content_reply'),
        'max_tokens' => (int) env('CONTENT_AI_MAX_TOKENS', 1200),
        'default_temperature' => (float) env('CONTENT_AI_TEMPERATURE', 0.8),
        // Had posting harian (default platform)
        'default_max_daily_posts' => (int) env('CONTENT_MAX_DAILY_POSTS', 10),
    ],

    'publishing' => [
        // Auto-publish lalai (tenant mesti benarkan secara eksplisit)
        'auto_publish_by_default' => (bool) env('CONTENT_AUTO_PUBLISH', false),
        // Auto-reply lalai
        'auto_reply_by_default' => (bool) env('CONTENT_AUTO_REPLY', false),
        // KEPERLUAN: auto_publish hanya jika platform->auto_publish = true
        'require_platform_opt_in' => true,
    ],

    'moderation' => [
        // Kata kunci kritikal — sentiasa escalate ke manusia
        'critical_keywords' => [
            'refund', 'complaint', 'saman', 'police', 'scam', 'consumer tribunal',
            'mAuthenticated', 'pen-tipu', 'tidakializer',
        ],
        // Sentimen automatik
        'auto_sentiment' => (bool) env('CONTENT_AUTO_SENTIMENT', true),
        // Keyakinan minimum untuk auto-reply
        'min_auto_reply_confidence' => (float) env('CONTENT_MIN_AUTO_REPLY_CONFIDENCE', 0.85),
    ],

    'analytics' => [
        'default_period_days' => (int) env('CONTENT_ANALYTICS_DAYS', 30),
        'metrics_retention_days' => (int) env('CONTENT_METRICS_RETENTION', 730),
    ],
];
