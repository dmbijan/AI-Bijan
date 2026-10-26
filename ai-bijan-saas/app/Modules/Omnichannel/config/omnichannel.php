<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Omnichannel Configuration
    |--------------------------------------------------------------------------
    */

    // Channel Settings
    'channels' => [
        'whatsapp' => [
            'enabled' => true,
            'provider' => 'twilio', // twilio, wablas, wa-business-api
            'phone_number' => null,
            'api_key' => null,
            'webhook_verify_token' => null,
            'business_account_id' => null,
        ],
        'email' => [
            'enabled' => true,
            'smtp_host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'smtp_port' => env('MAIL_PORT', 587),
            'smtp_username' => env('MAIL_USERNAME'),
            'smtp_password' => env('MAIL_PASSWORD'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@aibijan.com'),
            'from_name' => env('MAIL_FROM_NAME', 'AI-Bijan'),
        ],
        'facebook_messenger' => [
            'enabled' => false,
            'app_id' => null,
            'app_secret' => null,
            'page_access_token' => null,
            'webhook_verify_token' => null,
        ],
        'instagram' => [
            'enabled' => false,
            'app_id' => null,
            'app_secret' => null,
            'access_token' => null,
        ],
        'telegram' => [
            'enabled' => false,
            'bot_token' => null,
        ],
        'live_chat' => [
            'enabled' => true,
            'widget_color' => '#00EAFF',
            'widget_position' => 'bottom-right',
        ],
        'sms' => [
            'enabled' => false,
            'provider' => 'twilio', // twilio, aws-sns
            'api_key' => null,
            'phone_number' => null,
        ],
        'twitter_dm' => [
            'enabled' => false,
            'api_key' => null,
            'api_secret' => null,
            'access_token' => null,
            'access_token_secret' => null,
        ],
    ],

    // Conversation Settings
    'conversations' => [
        'status_flow' => ['open', 'pending', 'archived'],
        'auto_close_hours' => 48,
        'agent_assignment_strategy' => 'round-robin', // round-robin, least-busy, skill-based
        'max_conversations_per_agent' => 20,
        'idle_timeout_minutes' => 30,
        'typing_indicator_enabled' => true,
        'read_receipt_enabled' => true,
    ],

    // Message Settings
    'messages' => [
        'max_attachment_size_mb' => 25,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4', 'audio/*'],
        'media_storage_path' => 'omnichannel/media/',
        'message_retention_days' => 365,
        'auto_reply_enabled' => true,
        'out_of_office_enabled' => true,
    ],

    // Bot & Automation
    'automation' => [
        'chatbot_enabled' => true,
        'fallback_to_human' => true,
        'keywords_for_escalation' => ['agent', 'human', 'support', 'komplain'],
        'greeting_message' => 'Hi! Welcome to our support. How can we help you today?',
        'farewell_message' => 'Thank you for chatting with us!',
    ],

    // Agent Settings
    'agents' => [
        'status_options' => ['online', 'offline', 'busy', 'away'],
        'notification_enabled' => true,
        'sound_notification_enabled' => true,
    ],

    // Integration Settings
    'integrations' => [
        'b_crm_sync' => true,
        'b_helpdesk_sync' => true,
        'b_marketing_sync' => true,
        'webhook_enabled' => true,
    ],
];
