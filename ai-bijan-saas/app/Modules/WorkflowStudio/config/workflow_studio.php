<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Workflow Studio (BAB 86 / Idea #26)
    |--------------------------------------------------------------------------
    |
    | ⚠️ LISEN: n8n guna Sustainable Use License (BUKAN OSS). Modul ini ditulis
    | sendiri — n8n/Hermes/OpenClaw hanya RUJUKAN REKA BENTUK.
    |
    */

    'safety' => [
        // LALAI SELAMAT: semua workflow baharu dalam mod dry-run
        'default_dry_run' => (bool) env('WORKFLOW_DEFAULT_DRY_RUN', true),
        // Had node setiap run (elak infinit)
        'max_nodes_per_run' => (int) env('WORKFLOW_MAX_NODES', 100),
        // Had kedalaman traversal
        'max_depth' => (int) env('WORKFLOW_MAX_DEPTH', 25),
        // Senarai node yang sentiasa memerlukan kelulusan manusia
        'require_approval_nodes' => [
            'action.send_email',
            'action.send_whatsapp',
            'action.create_record',
            'action.update_record',
        ],
    ],

    'execution' => [
        'task_type' => env('WORKFLOW_AI_TASK_TYPE', 'workflow'),
        'timeout_seconds' => (int) env('WORKFLOW_TIMEOUT', 120),
        'max_concurrent_runs' => (int) env('WORKFLOW_MAX_CONCURRENT', 5),
    ],

    'templates' => [
        // Kategori template yang disokong
        'categories' => ['general', 'sales', 'marketing', 'ops', 'finance', 'hr', 'ai'],
        // Muat naik template hanya untuk super admin secara lalai
        'upload_requires_admin' => true,
    ],
];
