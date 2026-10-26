<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Studio (BAB 82 / Fasa 4)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk pemprosesan media: video understanding, penjanaan
    | video, dan tangkapan skrin.
    |
    */

    'disk' => env('MEDIA_DISK', 'media'),

    'limits' => [
        'max_upload_mb' => (int) env('MEDIA_MAX_UPLOAD_MB', 500),
        'max_duration_seconds' => (int) env('MEDIA_MAX_DURATION', 3600),
        'max_keyframes' => (int) env('MEDIA_MAX_KEYFRAMES', 120),
        'process_resolution' => env('MEDIA_PROCESS_RESOLUTION', '720p'),
    ],

    'retention' => [
        // Hari sebelum aset automatik dipadam
        'default_days' => (int) env('MEDIA_RETENTION_DAYS', 90),
        'purge_batch_size' => (int) env('MEDIA_PURGE_BATCH', 100),
    ],

    'understanding' => [
        'languages' => ['ms', 'en', 'id', 'zh', 'msa'],
        'max_questions' => (int) env('MEDIA_MAX_QUESTIONS', 8),
        'generate_qa_pairs' => (bool) env('MEDIA_GENERATE_QA', true),
    ],

    'generation' => [
        'default_provider' => env('MEDIA_VIDEO_PROVIDER', 'heygen'),
        'max_duration_seconds' => (int) env('MEDIA_GEN_MAX_DURATION', 90),
        'watermark_text' => env('MEDIA_WATERMARK_TEXT', 'AI-generated'),
    ],

    'providers' => [
        'heygen' => ['key' => env('HEYGEN_API_KEY'), 'enabled' => (bool) env('HEYGEN_ENABLED', false)],
        'synthesia' => ['key' => env('SYNTHESIA_API_KEY'), 'enabled' => (bool) env('SYNTHESIA_ENABLED', false)],
        'd_id' => ['key' => env('D_ID_API_KEY'), 'enabled' => (bool) env('D_ID_ENABLED', false)],
    ],

    'moderation' => [
        'enabled' => (bool) env('MEDIA_MODERATION_ENABLED', true),
        'block_violence' => true,
        'block_sexual' => true,
        'block_political' => true,
        'block_impersonation' => true,
    ],

    'screen_capture' => [
        'enabled' => (bool) env('MEDIA_SCREEN_CAPTURE_ENABLED', true),
        'mask_patterns' => [
            'nombor kad kredit' => '/\b(?:\d[ -]*?){13,19}\b/',
            'emel peribadi' => '/\b[\w.+-]+@[\w-]+\.[\w.]+\b/',
            'nombor telefon MY' => '/\b(?:\+?60|0)1\d[-\s]?\d{3,4}[-\s]?\d{4}\b/',
        ],
    ],
];
