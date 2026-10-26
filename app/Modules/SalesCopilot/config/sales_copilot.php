<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sales Copilot (BAB 82 / Fasa 3)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi pembantu jualan: brief prospek, klasifikasi balasan,
    | dan penjanaan proposal.
    |
    */

    'brief' => [
        'max_items' => (int) env('SALESCOPILOT_BRIEF_MAX_ITEMS', 15),
        'include_signals' => true,
    ],

    'reply_analysis' => [
        'enabled' => (bool) env('SALESCOPILOT_REPLY_ANALYSIS', true),
        // Ambang keyakinan untuk auto-classify
        'confidence_threshold' => (float) env('SALESCOPILOT_CONFIDENCE', 0.7),
        'auto_create_opportunity_on' => ['interested'],
    ],

    'proposal' => [
        'max_items' => (int) env('SALESCOPILOT_PROPOSAL_MAX_ITEMS', 30),
        'include_video_intro' => (bool) env('SALESCOPILOT_VIDEO_INTRO', false),
        'template' => env('SALESCOPILOT_PROPOSAL_TEMPLATE'),
    ],

    'follow_up' => [
        // Hari selepas mesej terakhir sebelum cadang follow-up
        'idle_days' => (int) env('SALESCOPILOT_IDLE_DAYS', 5),
        'max_per_day' => (int) env('SALESCOPILOT_FOLLOWUP_CAP', 20),
    ],
];
