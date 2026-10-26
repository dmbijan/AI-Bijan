<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Live Support (BAB 86 / Idea #28)
    |--------------------------------------------------------------------------
    |
    | Customer service live diuruskan AI + eskalasi manusia + eSIM.
    |
    | ⚠️ Telefoni sebenar memerlukan kredensial provider (Twilio/operator eSIM).
    | Modul ini mengurus orkestrasi, transkripsi, eskalasi & rekod.
    |
    */

    'escalation' => [
        // Kata kunci yang SENTIASA mencetuskan eskalasi manusia
        'critical_keywords' => [
            'refund', 'complaint', 'saman', 'lawyer', 'police', 'scam', 'tipu',
            'consumer tribunal', 'tribunal', 'batal order', 'marah', 'teruk',
        ],
        // Keyakinan AI minimum (bawah ini → eskalasi)
        'min_confidence' => (float) env('LIVE_SUPPORT_MIN_CONFIDENCE', 0.75),
        // Sentiment negatif mencetuskan eskalasi
        'escalate_on_negative' => (bool) env('LIVE_SUPPORT_ESCALATE_NEGATIVE', true),
    ],

    'providers' => [
        // Provider telefoni yang disokong (kredensial per tenant)
        'voice' => ['twilio', 'vonage', 'esim_operator', 'webrtc'],
        'default' => env('LIVE_SUPPORT_PROVIDER', 'webrtc'),
    ],

    'esim' => [
        // Pendekatan: eSIM operator (bukan VoIP) untuk nombor tempatan MY
        'supported_operators' => ['maxis', 'celcomdigi', 'umobile'],
        // eSIM memerlukan pendaftaran MyDigitalID/KYC
        'require_kyc' => true,
    ],

    'limits' => [
        // Had panggilan serentak per saluran (lalai)
        'max_concurrent_default' => (int) env('LIVE_SUPPORT_MAX_CONCURRENT', 5),
        // Tempoh maksimum satu panggilan (saat)
        'max_call_seconds' => (int) env('LIVE_SUPPORT_MAX_CALL', 3600),
        // Retensi rekod panggilan (hari)
        'retention_days' => (int) env('LIVE_SUPPORT_RETENTION', 365),
    ],

    'ai' => [
        'summary_task_type' => env('LIVE_SUPPORT_SUMMARY_TASK', 'live_support_summary'),
        'max_summary_tokens' => (int) env('LIVE_SUPPORT_SUMMARY_TOKENS', 250),
    ],
];
