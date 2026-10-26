<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blast Engine (BAB 82 / Fasa 2)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi enjin blast: pembekal channel, had kadar, ambang
    | deliverability, dan templat mesej.
    |
    */

    'app_url' => env('BLAST_APP_URL', env('APP_URL', 'http://localhost:8000')),

    'from_address' => env('BLAST_FROM_ADDRESS', 'no-reply@aibijan.com'),
    'from_name' => env('BLAST_FROM_NAME', 'AI BIJAN'),
    'reply_to' => env('BLAST_REPLY_TO', 'no-reply@aibijan.com'),

    'quotas' => [
        // Harian per tenant (semua channel digabung)
        'daily_per_tenant' => (int) env('BLAST_DAILY_PER_TENANT', 2000),
        // Harian per domain
        'daily_per_domain' => (int) env('BLAST_DAILY_PER_DOMAIN', 500),
        // Sekejap per channel (token bucket)
        'per_second' => [
            'email' => (int) env('BLAST_EMAIL_PER_SECOND', 20),
            'sms' => (int) env('BLAST_SMS_PER_SECOND', 10),
            'whatsapp' => (int) env('BLAST_WHATSAPP_PER_SECOND', 80),
            'video' => 1,
        ],
        // Batch size satu giliran queue
        'batch_size' => (int) env('BLAST_BATCH_SIZE', 50),
    ],

    'deliverability' => [
        // Auto-pause kempen bila melebihi ambang
        'bounce_threshold' => (float) env('BLAST_BOUNCE_THRESHOLD', 5.0),
        'complaint_threshold' => (float) env('BLAST_COMPLAINT_THRESHOLD', 0.1),
        // Hari untuk suppress selepas bounce
        'suppress_days_on_bounce' => (int) env('BLAST_SUPPRESS_DAYS', 30),
    ],

    'providers' => [
        'email' => [
            'enabled' => (bool) env('BLAST_EMAIL_ENABLED', true),
            // 'log' = mod simulasi (tidak hantar), 'smtp', 'mailgun'
            'mode' => env('BLAST_EMAIL_MODE', 'log'),
            'default' => env('BLAST_EMAIL_PROVIDER', 'smtp'),
            'mailgun' => [
                'key' => env('MAILGUN_SECRET'),
                'domain' => env('MAILGUN_DOMAIN'),
            ],
        ],

        'sms' => [
            'enabled' => (bool) env('BLAST_SMS_ENABLED', false),
            'default' => env('BLAST_SMS_PROVIDER', 'twilio'),
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
        ],

        'whatsapp' => [
            'enabled' => (bool) env('BLAST_WHATSAPP_ENABLED', false),
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
            // Template yang telah diluluskan Meta
            'default_template' => env('WHATSAPP_DEFAULT_TEMPLATE'),
        ],

        'video' => [
            'enabled' => (bool) env('BLAST_VIDEO_ENABLED', false),
            'default' => env('BLAST_VIDEO_PROVIDER', 'heygen'),
            'heygen' => [
                'key' => env('HEYGEN_API_KEY'),
            ],
            'synthesia' => [
                'key' => env('SYNTHESIA_API_KEY'),
            ],
        ],
    ],

    'webhooks' => [
        // Secret untuk pengesahan tanda tangan webhook provider
        'mailgun' => env('BLAST_WEBHOOK_SECRET_MAILGUN'),
        'twilio' => env('BLAST_WEBHOOK_SECRET_TWILIO'),
        'meta' => env('BLAST_WEBHOOK_SECRET_META'),
    ],
];
