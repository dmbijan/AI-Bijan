<?php

/**
 * Konfigurasi B-Entitlement (BAB 90).
 *
 * ⚠️ `allow_without_subscription` — development sahaja. DALAM PRODUKSI
 * mesti `false` supaya tenant tanpa langganan tidak boleh access modul
 * (deny-by-default).
 *
 * Skema `plans` (sudah wujud dalam platform core):
 *   code, name, max_users, max_locations, storage_mb,
 *   ai_credits_monthly, modules (JSON), price_monthly, is_active
 */
return [
    'enabled' => env('ENTITLEMENT_ENABLED', true),

    /**
     * Benarkan akses modul tanpa langganan direkod.
     *
     * Development: true (supaya tenant baharu boleh cuba).
     * Produksi: FALSE.
     */
    'allow_without_subscription' => (bool) env('ENTITLEMENT_ALLOW_WITHOUT_SUB', false),

    /*
    |----------------------------------------------------------------------
    | Period bagi setiap limit
    |----------------------------------------------------------------------
    */
    'limit_periods' => [
        'ai_tokens' => 'monthly',
        'ai_requests' => 'daily',
        'seats' => 'total',
        'storage_bytes' => 'total',
        'sms_credits' => 'monthly',
        'whatsapp_messages' => 'monthly',
        'email_messages' => 'monthly',
        'api_calls' => 'daily',
        'projects' => 'total',
        'orders' => 'monthly',
    ],

    /*
    |----------------------------------------------------------------------
    | Plan lalai
    |----------------------------------------------------------------------
    |
    | `modules` — module_key dalam bentuk snake_case (contoh: `content_studio`).
    | Enterprise guna `['*']` untuk semua modul.
    */
    'default_plans' => [
        [
            'code' => 'starter',
            'name' => 'Starter',
            'price_monthly' => 88,
            'max_users' => 5,
            'max_locations' => 1,
            'storage_mb' => 5120,
            'ai_credits_monthly' => 50000,
            'modules' => [
                'crm', 'inventory', 'invoicing', 'finance', 'pos', 'orders',
                'booking', 'site', 'seo', 'analytics', 'email', 'blast',
            ],
            'limits' => [
                ['limit_key' => 'seats', 'name' => 'Pengguna', 'limit_value' => 5, 'unit' => 'pengguna', 'period' => 'total'],
                ['limit_key' => 'ai_tokens', 'name' => 'AI Tokens', 'limit_value' => 50000, 'unit' => 'token', 'period' => 'monthly'],
                ['limit_key' => 'ai_requests', 'name' => 'Permintaan AI', 'limit_value' => 500, 'unit' => 'permintaan', 'period' => 'daily'],
                ['limit_key' => 'storage_bytes', 'name' => 'Storan', 'limit_value' => 5368709120, 'unit' => 'bait', 'period' => 'total'],
                ['limit_key' => 'sms_credits', 'name' => 'SMS', 'limit_value' => 500, 'unit' => 'SMS', 'period' => 'monthly'],
            ],
        ],
        [
            'code' => 'growth',
            'name' => 'Growth',
            'price_monthly' => 288,
            'max_users' => 25,
            'max_locations' => 5,
            'storage_mb' => 51200,
            'ai_credits_monthly' => 500000,
            'modules' => [
                'crm', 'inventory', 'invoicing', 'finance', 'payroll', 'pos', 'orders',
                'booking', 'site', 'seo', 'analytics', 'email', 'blast', 'marketing',
                'loyalty', 'affiliate', 'link', 'omnichannel', 'content_studio', 'media_studio',
                'reminder', 'business_intel', 'workflow_studio', 'expense', 'service_os',
                'helpdesk', 'community', 'proof_of_value', 'sign_docs', 'cod', 'fleet',
                'agent_studio', 'mcp_studio', 'ai_agents', 'executive_ai', 'ade',
            ],
            'limits' => [
                ['limit_key' => 'seats', 'name' => 'Pengguna', 'limit_value' => 25, 'unit' => 'pengguna', 'period' => 'total'],
                ['limit_key' => 'ai_tokens', 'name' => 'AI Tokens', 'limit_value' => 500000, 'unit' => 'token', 'period' => 'monthly'],
                ['limit_key' => 'ai_requests', 'name' => 'Permintaan AI', 'limit_value' => 5000, 'unit' => 'permintaan', 'period' => 'daily'],
                ['limit_key' => 'storage_bytes', 'name' => 'Storan', 'limit_value' => 53687091200, 'unit' => 'bait', 'period' => 'total'],
                ['limit_key' => 'sms_credits', 'name' => 'SMS', 'limit_value' => 5000, 'unit' => 'SMS', 'period' => 'monthly'],
                ['limit_key' => 'whatsapp_messages', 'name' => 'WhatsApp', 'limit_value' => 5000, 'unit' => 'mesej', 'period' => 'monthly'],
            ],
        ],
        [
            'code' => 'enterprise',
            'name' => 'Enterprise',
            'price_monthly' => 1888,
            'max_users' => null,
            'max_locations' => null,
            'storage_mb' => null,
            'ai_credits_monthly' => 5000000,
            'modules' => ['*'],
            'limits' => [
                ['limit_key' => 'seats', 'name' => 'Pengguna', 'limit_value' => null, 'unit' => 'pengguna', 'period' => 'total'],
                ['limit_key' => 'ai_tokens', 'name' => 'AI Tokens', 'limit_value' => null, 'unit' => 'token', 'period' => 'monthly'],
                ['limit_key' => 'ai_requests', 'name' => 'Permintaan AI', 'limit_value' => null, 'unit' => 'permintaan', 'period' => 'daily'],
                ['limit_key' => 'storage_bytes', 'name' => 'Storan', 'limit_value' => null, 'unit' => 'bait', 'period' => 'total'],
                ['limit_key' => 'sms_credits', 'name' => 'SMS', 'limit_value' => null, 'unit' => 'SMS', 'period' => 'monthly'],
                ['limit_key' => 'whatsapp_messages', 'name' => 'WhatsApp', 'limit_value' => null, 'unit' => 'mesej', 'period' => 'monthly'],
                ['limit_key' => 'api_calls', 'name' => 'API Calls', 'limit_value' => null, 'unit' => 'permintaan', 'period' => 'daily'],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Cache TTL untuk entitlement (detik)
    |----------------------------------------------------------------------
    */
    'cache_ttl' => (int) env('ENTITLEMENT_CACHE_TTL', 300),
];
