<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Business Intelligence (BAB 86 / Idea #23, #24, #25)
    |--------------------------------------------------------------------------
    |
    | AI self-improve, kajian bisnes, idea feed & competitor watch.
    |
    */

    'health' => [
        // Pemberat dimensi skor kesihatan (jumlah MESTI 1.0)
        'weights' => [
            'sales' => 0.25,
            'cashflow' => 0.25,
            'operations' => 0.15,
            'customer' => 0.15,
            'marketing' => 0.10,
            'compliance' => 0.10,
        ],
        // Ambang gred
        'grades' => ['A' => 85, 'B' => 70, 'C' => 55, 'D' => 40],
        // Tempoh analisis lalai (hari)
        'analysis_days' => (int) env('BI_ANALYSIS_DAYS', 30),
    ],

    'study' => [
        'task_type' => env('BI_STUDY_TASK_TYPE', 'business_study'),
        'max_tokens' => (int) env('BI_STUDY_MAX_TOKENS', 1500),
        'default_confidence' => (float) env('BI_DEFAULT_CONFIDENCE', 50),
        // Maks idea dijana daripada satu kajian
        'max_ideas_per_study' => (int) env('BI_MAX_IDEAS', 10),
    ],

    'ideas' => [
        // Purata idea dijana setiap kajian
        'impact_weights' => ['low' => 30, 'medium' => 60, 'high' => 90],
        'effort_weights' => ['low' => 1, 'medium' => 2, 'high' => 3],
    ],

    'competitor' => [
        // Tempoh maksimum menyimpan snapshot (hari)
        'snapshot_retention_days' => (int) env('BI_COMPETITOR_RETENTION', 730),
        // Had pesaing aktif per tenant (elak penyalahgunaan)
        'max_active' => (int) env('BI_MAX_COMPETITORS', 50),
        // Sumber data: manual|public_scrape|api|tenant_input
        'allowed_sources' => ['manual', 'public_scrape', 'api', 'tenant_input'],
        // ⚠️ KEPATUHAN: hanya data awam. JANGAN scrape data peribadi atau
        // melanggar terma perkhidmatan pesaing.
        'public_data_only' => true,
    ],

    'learning' => [
        // Simpan pembelajaran AI untuk penambahbaikan berterusan
        'enabled' => (bool) env('BI_LEARNING_ENABLED', true),
        // Keyakinan minimum untuk dianggap aktif
        'min_confidence' => (float) env('BI_LEARNING_MIN_CONFIDENCE', 40),
        'max_active' => (int) env('BI_LEARNING_MAX_ACTIVE', 100),
    ],

    'scheduler' => [
        // Jana sinyal pasaran setiap hari
        'daily_signal' => (bool) env('BI_DAILY_SIGNAL', true),
        // Simpan snapshot kesihatan setiap hari
        'daily_health_snapshot' => (bool) env('BI_DAILY_HEALTH', true),
    ],
];
