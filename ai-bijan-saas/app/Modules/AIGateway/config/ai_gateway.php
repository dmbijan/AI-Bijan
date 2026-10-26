<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Gateway — serveras.click Reseller Model (BAB 21)
    |--------------------------------------------------------------------------
    |
    | API keys adalah ADMIN ONLY — TIDAK PERNAH didedahkan kepada pelanggan.
    | Pelanggan hanya berinteraksi melalui UI/platform AI BIJAN.
    |
    */

    'provider' => [
        'name' => 'serveras.click',
        'base_url' => env('AI_GATEWAY_URL', 'https://serveras.click/v1'),
        // ADMIN ONLY — jangan sekali-kali hantar ke frontend
        'api_key' => env('AI_GATEWAY_KEY'),
        'auth_header' => 'Authorization',
        'auth_prefix' => 'Bearer ',
        'timeout_seconds' => env('AI_GATEWAY_TIMEOUT', 120),
        'connect_timeout_seconds' => 10,
        'max_retries' => 2,
        'retry_backoff_ms' => [500, 1500],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Routing by Task Type (BAB 21.3)
    |--------------------------------------------------------------------------
    */

    'model_routing' => [
        'reasoning' => 'asai/gpt-6-astra',
        'deep_reasoning' => 'asai/claude-opus-5',
        'coding' => 'asai/deepseek-v4-pro',
        'creative' => 'asai/muse-wonder-1-0',
        'quick' => 'asai/glm-5-3-flash',
        'long_context' => 'asai/kimi-k3',
        'multilingual' => 'asai/qwen-3-8-max',
        'realtime' => 'asai/grok-4-7',
        'balanced' => 'asai/minimax-m3',
        'default' => 'asai/minimax-m3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Tier Multipliers (BAB 22.5)
    |--------------------------------------------------------------------------
    |
    | credits = (input_tokens × input_rate) + (output_tokens × output_rate)
    | input_rate = base_rate × tier_multiplier
    |
    */

    'tiers' => [
        'flash' => [
            'label' => 'Flash',
            'multiplier' => 1.0,
            'models' => ['asai/glm-5-3-flash', 'asai/minimax-m3'],
            'best_for' => 'Chat ringkas, jawapan pantas',
        ],
        'standard' => [
            'label' => 'Standard',
            'multiplier' => 1.5,
            'models' => ['asai/qwen-3-8-max', 'asai/gpt-6-astra', 'asai/deepseek-v4-pro'],
            'best_for' => 'Coding, analisis, multilingual',
        ],
        'premium' => [
            'label' => 'Premium',
            'multiplier' => 2.0,
            'models' => ['asai/claude-opus-4-8', 'asai/claude-opus-5'],
            'best_for' => 'Penaakulan kompleks, penyelidikan',
        ],
        'ultra' => [
            'label' => 'Ultra',
            'multiplier' => 2.5,
            'models' => ['asai/claude-opus-5-5', 'asai/kimi-k3'],
            'best_for' => 'Konteks panjang, tugas lanjutan',
        ],
    ],

    'base_credit_rate' => 0.0001, // RM per token (Flash tier)

    /*
    |--------------------------------------------------------------------------
    | Credit Calculation Rules (BAB 22.5)
    |--------------------------------------------------------------------------
    */

    'credit_rules' => [
        'round_to' => 100,              // Bulat ke atas ke nearest 100
        'free_retry_within_seconds' => 300,
        'cache_hit_is_free' => true,
        'image_generation_rm' => [0.04, 0.12],
        'audio_transcription_credit_per_5s' => 1,
        'video_processing_credit_per_2s' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Credit Wallet (BAB 22)
    |--------------------------------------------------------------------------
    */

    'wallet' => [
        'plan_tokens_reset_monthly' => true,
        'topup_expiry_days' => 30,
        'promo_expiry_days' => 14,
        'low_balance_alerts' => [80, 50, 20],
        'auto_recharge' => [
            'enabled_by_default' => false,
            'threshold_percent' => 20,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget Caps & Guardrails (BAB 21.3)
    |--------------------------------------------------------------------------
    */

    'guardrails' => [
        'max_cost_per_request_rm' => 5.00,
        'max_tokens_per_request' => 200000,
        'max_requests_per_minute_per_tenant' => 60,
        'circuit_breaker' => [
            'enabled' => true,
            'failure_threshold' => 5,
            'cooldown_seconds' => 60,
        ],
        'fallback_model' => 'asai/glm-5-3-flash',
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Redaction (BAB 21.3)
    |--------------------------------------------------------------------------
    */

    'redaction' => [
        'enabled' => true,
        'patterns' => [
            'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            'phone_my' => '/\+?60[0-9]{8,10}/',
            'credit_card' => '/\b\d{13,19}\b/',
            'ic_number' => '/\b\d{6}-\d{2}-\d{4}\b/',
        ],
        'replacement' => '[REDACTED]',
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Metering (BAB 21.3)
    |--------------------------------------------------------------------------
    */

    'metering' => [
        'log_every_call' => true,
        'track_latency' => true,
        'track_token_split' => true,
        'reconcile_failures' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Availability
    |--------------------------------------------------------------------------
    */

    'features' => [
        'live_calls_enabled' => env('AI_GATEWAY_LIVE', false),
        'streaming_enabled' => false,
        'tool_calling_enabled' => false,
        'structured_output_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Safety (BAB 62) — pertahanan prompt injection
    |--------------------------------------------------------------------------
    |
    | max_patterns_before_block: bilangan corak yang perlu sepadan sebelum
    | permintaan disekat. Lalai 1 (ketat). Naikkan hanya jika banyak positif
    | palsu dalam penggunaan sebenar.
    |
    */

    'safety' => [
        'scan_input' => env('AI_GATEWAY_SCAN_INPUT', true),
        'scan_output' => env('AI_GATEWAY_SCAN_OUTPUT', true),
        'max_patterns_before_block' => (int) env('AI_GATEWAY_BLOCK_THRESHOLD', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embeddings & Vector Store (BAB 17A.4 / T-08)
    |--------------------------------------------------------------------------
    |
    | model: 'hash-v1' = deterministik tempatan (tiada kos, tiada API).
    |        Tukar ke model API bila sedia; vektor tidak boleh dicampur
    |        antara model — setiap chunk menyimpan embedding_model.
    |
    */

    'embeddings' => [
        'model' => env('AI_EMBEDDING_MODEL', 'hash-v1'),
        'dimensions' => (int) env('AI_EMBEDDING_DIMENSIONS', 256),
        'chunk_size' => (int) env('AI_EMBEDDING_CHUNK_SIZE', 800),
        'chunk_overlap' => (int) env('AI_EMBEDDING_CHUNK_OVERLAP', 100),
    ],

];
