<?php

return [

    /*
    |--------------------------------------------------------------------------
    | B-Boardroom Workflow (BAB 20)
    |--------------------------------------------------------------------------
    |
    | Aliran cadangan 7 langkah. Boardroom TIDAK boleh menganggap persetujuan
    | ejen sebagai bukti kebenaran — dapatan mesti membezakan fakta, anggaran
    | dan andaian.
    |
    */

    'workflow_steps' => [
        1 => [
            'key' => 'owner_question',
            'label' => 'Pemilik mengemukakan persoalan atau keputusan',
            'actor' => 'owner',
        ],
        2 => [
            'key' => 'select_agents',
            'label' => 'Chief of Staff menentukan ejen yang relevan',
            'actor' => 'chief_of_staff',
        ],
        3 => [
            'key' => 'distribute_context',
            'label' => 'Ejen menerima konteks minimum yang diperlukan',
            'actor' => 'system',
        ],
        4 => [
            'key' => 'agent_findings',
            'label' => 'Setiap ejen mengemukakan dapatan, andaian, bukti dan risiko',
            'actor' => 'agents',
        ],
        5 => [
            'key' => 'detect_conflicts',
            'label' => 'Orchestrator mengesan percanggahan dan meminta semakan',
            'actor' => 'orchestrator',
        ],
        6 => [
            'key' => 'ai_ceo_summary',
            'label' => 'AI CEO menyediakan ringkasan keputusan dan pilihan',
            'actor' => 'ai_ceo',
        ],
        7 => [
            'key' => 'owner_decision',
            'label' => 'Pemilik memilih tindakan; sistem merekod keputusan',
            'actor' => 'owner',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Selection Rules
    |--------------------------------------------------------------------------
    |
    | Memetakan kategori persoalan kepada ejen yang relevan.
    |
    */

    'agent_selection' => [
        'financial' => ['cfo', 'ceo', 'ciso'],
        'marketing' => ['cmo', 'ads_specialist', 'analyst'],
        'operations' => ['coo', 'chro', 'analyst'],
        'sales' => ['ciso', 'cmo', 'analyst'],
        'hr' => ['chro', 'cfo', 'coo'],
        'strategy' => ['ceo', 'cfo', 'coo', 'cmo'],
        'risk' => ['ceo', 'cfo', 'coo'],
        'customer' => ['support', 'ciso', 'cmo'],
        'research' => ['researcher', 'analyst', 'ceo'],
        'default' => ['ceo', 'cfo', 'coo'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conflict Detection Thresholds
    |--------------------------------------------------------------------------
    */

    'conflict_detection' => [
        'enabled' => true,
        // Perbezaan skor keyakinan melebihi nilai ini dianggap percanggahan
        'confidence_divergence_threshold' => 0.3,
        // Ejen yang bercanggah perlu semakan semula
        'require_review_on_conflict' => true,
        'max_review_rounds' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Finding Classification (BAB 20.1)
    |--------------------------------------------------------------------------
    |
    | Dapatan MESTI membezakan antara fakta, anggaran dan andaian.
    |
    */

    'finding_types' => [
        'fact' => [
            'label' => 'Fakta',
            'description' => 'Disahkan daripada sumber data rasmi',
            'requires_source' => true,
        ],
        'estimate' => [
            'label' => 'Anggaran',
            'description' => 'Berasaskan data tetapi tidak disahkan sepenuhnya',
            'requires_source' => true,
        ],
        'assumption' => [
            'label' => 'Andaian',
            'description' => 'Diandaikan oleh AI, perlu pengesahan manusia',
            'requires_source' => false,
        ],
        'forecast' => [
            'label' => 'Ramalan',
            'description' => 'Unjuran masa depan berasaskan model',
            'requires_source' => true,
        ],
        'recommendation' => [
            'label' => 'Cadangan',
            'description' => 'Tindakan dicadangkan, bukan kenyataan fakta',
            'requires_source' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Status
    |--------------------------------------------------------------------------
    */

    'session_status' => [
        'draft',
        'in_progress',
        'awaiting_review',
        'awaiting_owner_decision',
        'decided',
        'closed',
        'cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Decision Options
    |--------------------------------------------------------------------------
    */

    'decision_options' => [
        'approve' => 'Lulus cadangan',
        'reject' => 'Tolak cadangan',
        'defer' => 'Tangguh keputusan',
        'request_more_info' => 'Minta maklumat tambahan',
        'modify' => 'Ubah suai dan lulus',
    ],

];
