<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsAppSuite — automasi WhatsApp
    |--------------------------------------------------------------------------
    |
    | Berasal daripada https://wasapbot.my/
    |
    | Chatbot AI, auto-responder, kata kunci, blast pukal dengan anti-ban,
    | pengesah nombor, label, warm-up, pemberitahuan pesanan, webhook/API.
    */

    'enabled' => (bool) env('WHATSAPP_SUITE_ENABLED', true),

    /*
    | ⚠️ Penghantaran langsung. false = mesej dibariskan sahaja (selamat untuk
    | ujian). true memerlukan whatsapp-service (whatsapp-web.js) aktif.
    */
    'send_live' => (bool) env('WHATSAPP_SEND_LIVE', false),

    /*
    | Perkhidmatan whatsapp-web.js.
    */
    'service' => [
        'base_url' => env('WHATSAPP_SERVICE_URL', 'http://localhost:3001'),
        'api_key' => env('WHATSAPP_SERVICE_KEY'),
        'timeout_seconds' => (int) env('WHATSAPP_SERVICE_TIMEOUT', 30),
    ],

    /*
    | Had.
    */
    'limits' => [
        'max_devices' => (int) env('WHATSAPP_MAX_DEVICES', 10),
        'max_contacts' => (int) env('WHATSAPP_MAX_CONTACTS', 100000),
        'campaign_max_recipients' => (int) env('WHATSAPP_CAMPAIGN_MAX', 50000),
        'message_max_length' => (int) env('WHATSAPP_MSG_MAX', 4096),
    ],

    /*
    | Anti-ban.
    */
    'anti_ban' => [
        'default_delay_min' => (int) env('WHATSAPP_DELAY_MIN', 30),
        'default_delay_max' => (int) env('WHATSAPP_DELAY_MAX', 120),
        'pause_every' => (int) env('WHATSAPP_PAUSE_EVERY', 20),
        'pause_seconds' => (int) env('WHATSAPP_PAUSE_SECONDS', 300),
        'daily_limit_default' => (int) env('WHATSAPP_DAILY_LIMIT', 200),
    ],

    /*
    | Warm-up nombor baru — had harian berperingkat.
    */
    'warmup' => [
        'enabled' => (bool) env('WHATSAPP_WARMUP_ENABLED', true),
        'max_stage' => 4,
        'stage_limits' => [
            1 => 20,
            2 => 50,
            3 => 120,
            4 => 200,
        ],
    ],

    /*
    | Pemberitahuan pesanan.
    */
    'notifications' => [
        'enabled' => (bool) env('WHATSAPP_NOTIFICATIONS', true),
        'platforms' => ['woocommerce', 'shopify', 'shoppegram', 'onpay', 'custom'],
    ],

    /*
    | Integrasi.
    */
    'integrations' => [
        'webhook_enabled' => (bool) env('WHATSAPP_WEBHOOK', true),
        'api_enabled' => (bool) env('WHATSAPP_API', true),
        'pabbly' => (bool) env('WHATSAPP_PABBLY', false),
        'make' => (bool) env('WHATSAPP_MAKE', false),
    ],

];
