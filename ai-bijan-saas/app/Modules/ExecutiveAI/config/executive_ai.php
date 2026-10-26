<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Executive AI (BAB 82 / Fasa 5)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi pembantu eksekutif: tahap autonomy lalai, briefing,
    | dan had untuk voice/video.
    |
    */

    // Tahap autonomy lalai untuk tenant baharu.
    // 0 = Tanya (hanya baca). Jangan naikkan tanpa sebab.
    'default_autonomy_level' => (int) env('EXECUTIVE_AI_DEFAULT_LEVEL', 0),

    // Hademption: berapa kali EOL Autonomous boleh menggantikan pengesahan.
    'max_daily_actions' => (int) env('EXECUTIVE_AI_MAX_DAILY_ACTIONS', 50),

    'briefing' => [
        // Masa briefing pagi (HH:MM)
        'time' => env('EXECUTIVE_AI_BRIEFING_TIME', '07:30'),
        'types' => ['daily', 'weekly', 'monthly'],
        'max_items' => (int) env('EXECUTIVE_AI_BRIEFING_MAX_ITEMS', 10),
    ],

    'voice' => [
        'enabled' => (bool) env('EXECUTIVE_AI_VOICE_ENABLED', false),
        'language' => env('EXECUTIVE_AI_VOICE_LANG', 'ms'),
        'max_duration_seconds' => (int) env('EXECUTIVE_AI_VOICE_MAX', 60),
        // Push-to-talk sahaja — TIDAK ada rakaman mikrofon senyap.
        'push_to_talk_only' => true,
    ],

    'proactive' => [
        'enabled' => (bool) env('EXECUTIVE_AI_PROACTIVE_ENABLED', false),
        // Jenis amaran
        'alerts' => [
            'hot_prospect_aging' => ['hours' => 48, 'label' => 'Prospek panas tiada respons'],
            'sla_breach' => ['label' => 'SLA helpdesk hampir luput'],
            'stock_critical' => ['label' => 'Stok kritikal'],
            'cashflow_warning' => ['label' => 'Amaran aliran tunai'],
            'blast_bounce_high' => ['label' => 'Bounce blast tinggi'],
        ],
    ],

    'approval' => [
        //-actions yang membolehkan AI hantar terus pada tahap 4
        'auto_actions' => ['automated_followup'],
        // Had automatik — maksimum 10 follow-up sehari
        'daily_action_cap' => (int) env('EXECUTIVE_AI_ACTION_CAP', 10),
    ],
];
