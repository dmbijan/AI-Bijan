<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Vision Service (Frigate/YOLO) — hantar peristiwa CCTV ke AI BIJAN.
    'vision' => [
        'webhook_secret' => env('VISION_WEBHOOK_SECRET'),
        'alert_min_confidence' => (float) env('VISION_ALERT_MIN_CONFIDENCE', 0.8),
        'alerts_per_camera_per_hour' => (int) env('VISION_ALERTS_PER_HOUR', 10),
        'alert_log_channel' => env('VISION_ALERT_LOG_CHANNEL', 'single'),
        // Aktifkan hanya selepas pemacu B-Notify (WhatsApp/SMS) wujud.
        'alert_via_notify' => (bool) env('VISION_ALERT_VIA_NOTIFY', false),
    ],

];
