<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identiti modul
    |--------------------------------------------------------------------------
    */
    'name' => 'B-Live AI',
    'module_key' => 'blive',
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Had (guardrails)
    |--------------------------------------------------------------------------
    |
    | Had ini mengawal kos GPU/voice/LLM dan menghalang runaway semasa live.
    | Setiap nilai boleh dikunci oleh tenant melalui had langganan.
    |
    */
    'limits' => [
        'max_avatar_per_tenant' => 25,
        'max_concurrent_live_sessions' => 3,
        'max_speech_characters' => 2000,
        'max_audience_queue' => 500,
        'max_takeover_response_ms' => 800,
        'max_session_minutes' => 480,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enjin avatar
    |--------------------------------------------------------------------------
    |
    | MetaHuman / Audio2Face memerlukan GPU dan lesen. Default ialah enjin
    | `simulated` supaya sistem boleh berfungsi (dan diuji) tanpa GPU.
    | `simulated` menghasilkan manifest deterministik, bukan video sebenar.
    |
    */
    'avatar_engine' => env('BLIVE_AVATAR_ENGINE', 'simulated'),

    'engines' => [
        'simulated' => [
            'driver' => 'simulated',
            'base_url' => null,
            'api_key' => null,
            'poll_interval_seconds' => 2,
            'timeout_seconds' => 30,
        ],
        'metahuman' => [
            'driver' => 'http',
            'base_url' => env('BLIVE_METAHUMAN_URL'),
            'api_key' => env('BLIVE_METAHUMAN_KEY'),
            'poll_interval_seconds' => 5,
            'timeout_seconds' => 120,
        ],
        'http' => [
            'driver' => 'http',
            'base_url' => env('BLIVE_AVATAR_ENGINE_URL'),
            'api_key' => env('BLIVE_AVATAR_ENGINE_KEY'),
            'poll_interval_seconds' => 5,
            'timeout_seconds' => 120,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enjin suara & otak
    |--------------------------------------------------------------------------
    |
    | `voice` menyepadukan modul VoiceStudio sedia ada (ElevenLabs) -
    | B-Live tidak menggandakan TTS. `brain` menyepadukan AIGateway.
    |
    */
    'voice' => [
        'provider' => env('BLIVE_VOICE_PROVIDER', 'voice_studio'),
        'default_locale' => env('BLIVE_VOICE_LOCALE', 'ms-MY'),
    ],

    'brain' => [
        'gateway' => 'ai_gateway',
        'task_type' => 'live_host',
        'temperature' => 0.7,
        'max_tokens' => 400,
        'fallback_to_human' => true,
        'confidence_threshold' => 0.55,
    ],

    /*
    |--------------------------------------------------------------------------
    | Penstriman
    |--------------------------------------------------------------------------
    */
    'streaming' => [
        'default_resolution' => '1920x1080',
        'default_bitrate_kbps' => 4500,
        'default_fps' => 30,
        'orientations' => ['portrait' => '1080x1920', 'landscape' => '1920x1080'],
        'ffmpeg_binary' => env('BLIVE_FFMPEG', 'ffmpeg'),
        'enable_ffmpeg' => env('BLIVE_ENABLE_FFMPEG', false),
        'max_destinations_per_session' => 3,
        'reconnect_attempts' => 3,
    ],

    'platforms' => [
        'youtube' => [
            'label' => 'YouTube Live',
            'driver' => 'rtmp',
            'mode' => 'official_api',
            'enabled' => true,
        ],
        'facebook' => [
            'label' => 'Facebook Live',
            'driver' => 'rtmps',
            'mode' => 'graph_api',
            'enabled' => true,
        ],
        'tiktok' => [
            'label' => 'TikTok LIVE',
            'driver' => 'rtmp',
            'mode' => 'manual_rtmp',
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Keselamatan & pematuhan
    |--------------------------------------------------------------------------
    */
    'safety' => [
        'require_ai_disclosure' => true,
        'disclosure_text' => 'This is an AI-generated virtual presenter.',
        'block_price_invention' => true,
        'require_approval_for_discounts' => true,
        'max_discount_percent' => 50,
        'emergency_stop_always_available' => true,
    ],

    'consent' => [
        'require_consent_record' => true,
        'allowed_consent_types' => ['ai_generated', 'licensed', 'individual_release'],
    ],

    'retention' => [
        'recording_days' => 30,
        'message_days' => 14,
        'metric_days' => 180,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commerce
    |--------------------------------------------------------------------------
    */
    'commerce' => [
        'require_product_approval' => true,
        'approved_price_field' => 'approved_price',
        'currency' => 'MYR',
    ],
];
