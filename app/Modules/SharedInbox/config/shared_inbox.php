<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SharedInbox — peti masuk bersama ala Chatwoot
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/chatwoot/chatwoot
    |
    | Melengkapkan modul `LiveSupport` (saluran suara/chat langsung) dengan
    | peti masuk multi-ejen: SLA, makro, ulasan sedia ada dan penugasan.
    |
    */

    'enabled' => (bool) env('SHARED_INBOX_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_inboxes' => (int) env('SHARED_INBOX_MAX_INBOXES', 20),
        'max_conversations_per_inbox' => (int) env('SHARED_INBOX_MAX_CONVERSATIONS', 5000),
        'max_canned_responses' => (int) env('SHARED_INBOX_MAX_CANNED', 500),
        'max_macros' => (int) env('SHARED_INBOX_MAX_MACROS', 200),
    ],

    /*
    | SLA lalai (minit).
    */
    'sla' => [
        'response_minutes' => (int) env('SHARED_INBOX_SLA_RESPONSE', 60),
        'resolution_minutes' => (int) env('SHARED_INBOX_SLA_RESOLUTION', 1440),
        'business_hours_only' => (bool) env('SHARED_INBOX_BUSINESS_HOURS_ONLY', false),
        'check_interval_minutes' => (int) env('SHARED_INBOX_SLA_CHECK', 5),
        'warn_threshold_percent' => (int) env('SHARED_INBOX_SLA_WARN', 80),
    ],

    /*
    | Penugasan ejen.
    */
    'assignment' => [
        'default_strategy' => env('SHARED_INBOX_ASSIGNMENT', 'round_robin'),
        'strategies' => ['round_robin', 'least_loaded', 'manual', 'skill_match'],
        'auto_assign' => (bool) env('SHARED_INBOX_AUTO_ASSIGN', true),
    ],

    /*
    | Ulasan sedia ada.
    */
    'canned_responses' => [
        'enabled' => (bool) env('SHARED_INBOX_CANNED_ENABLED', true),
        'max_content_length' => (int) env('SHARED_INBOX_CANNED_MAX', 4000),
        'shortcode_prefix' => env('SHARED_INBOX_CANNED_PREFIX', '/'),
    ],

    /*
    | Makro.
    */
    'macros' => [
        'enabled' => (bool) env('SHARED_INBOX_MACROS_ENABLED', true),
        'max_actions' => (int) env('SHARED_INBOX_MACRO_MAX_ACTIONS', 10),
    ],

    /*
    | AI autoplay.
    */
    'ai' => [
        'enabled' => (bool) env('SHARED_INBOX_AI_ENABLED', true),
        'confidence_threshold' => (float) env('SHARED_INBOX_AI_CONFIDENCE', 0.75),
        'auto_reply' => (bool) env('SHARED_INBOX_AI_AUTO_REPLY', false),
        'suggest_canned' => (bool) env('SHARED_INBOX_AI_SUGGEST', true),
    ],

];
