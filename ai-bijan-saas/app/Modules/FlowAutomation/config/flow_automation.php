<?php

return [

    /*
    |--------------------------------------------------------------------------
    | B-Flow / B-Trigger Automation (BAB 28)
    |--------------------------------------------------------------------------
    |
    | Workflow tanpa kod: Trigger → Condition → Action → Approval → Retry
    |
    */

    'trigger_types' => [
        'domain_event' => [
            'label' => 'Domain Event',
            'description' => 'Dicetus oleh event perniagaan (OrderPaid, InvoiceIssued, dll)',
            'config' => ['event_name'],
        ],
        'webhook' => [
            'label' => 'Webhook',
            'description' => 'Panggilan HTTP masuk dari sistem luar',
            'config' => ['signature_header', 'secret_ref'],
        ],
        'schedule' => [
            'label' => 'Jadual',
            'description' => 'Cron atau masa berulang',
            'config' => ['cron_expression', 'timezone'],
        ],
        'manual' => [
            'label' => 'Manual',
            'description' => 'Dicetus oleh pengguna melalui butang/API',
            'config' => ['allowed_roles'],
        ],
        'record_change' => [
            'label' => 'Perubahan Rekod',
            'description' => 'Rekod dicipta/dikemas kini/dipadam',
            'config' => ['table', 'operation', 'conditions'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Condition Operators
    |--------------------------------------------------------------------------
    */

    'condition_operators' => [
        'equals' => 'Sama dengan',
        'not_equals' => 'Tidak sama dengan',
        'greater_than' => 'Lebih besar daripada',
        'greater_or_equal' => 'Lebih besar atau sama',
        'less_than' => 'Kurang daripada',
        'less_or_equal' => 'Kurang atau sama',
        'contains' => 'Mengandungi',
        'not_contains' => 'Tidak mengandungi',
        'in' => 'Dalam senarai',
        'not_in' => 'Tidak dalam senarai',
        'is_empty' => 'Kosong',
        'is_not_empty' => 'Tidak kosong',
        'changed' => 'Telah berubah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Types
    |--------------------------------------------------------------------------
    */

    'action_types' => [
        'internal_api' => [
            'label' => 'Panggilan API Dalaman',
            'requires_approval' => false,
        ],
        'connector_api' => [
            'label' => 'Panggilan Connector Luaran',
            'requires_approval' => false,
        ],
        'ai_task' => [
            'label' => 'Tugas AI',
            'requires_approval' => false,
        ],
        'create_task' => [
            'label' => 'Cipta Tugasan',
            'requires_approval' => false,
        ],
        'send_notification' => [
            'label' => 'Hantar Notifikasi',
            'requires_approval' => false,
        ],
        'send_external_message' => [
            'label' => 'Hantar Mesej Luaran',
            'requires_approval' => true,
        ],
        'update_record' => [
            'label' => 'Kemas Kini Rekod',
            'requires_approval' => false,
        ],
        'financial_action' => [
            'label' => 'Tindakan Kewangan',
            'requires_approval' => true,
        ],
        'request_approval' => [
            'label' => 'Mohon Kelulusan',
            'requires_approval' => false,
        ],
        'webhook_out' => [
            'label' => 'Hantar Webhook Keluar',
            'requires_approval' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Execution Limits (BAB 28.3)
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'max_actions_per_run' => 50,
        'max_runs_per_hour' => 100,
        'max_runs_per_tenant_per_day' => 1000,
        'max_retry_attempts' => 3,
        'retry_backoff_seconds' => [30, 120, 600],
        'execution_timeout_seconds' => 300,
        'max_workflow_depth' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Run Status
    |--------------------------------------------------------------------------
    */

    'run_status' => [
        'pending',
        'running',
        'waiting_approval',
        'completed',
        'failed',
        'cancelled',
        'dead_letter',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection (BAB 28.3)
    |--------------------------------------------------------------------------
    */

    'protection' => [
        'require_dry_run_before_activation' => true,
        'require_test_mode_support' => true,
        'kill_switch_enabled' => true,
        'idempotency_required' => true,
        'log_all_runs' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Example Workflows
    |--------------------------------------------------------------------------
    */

    'example_workflows' => [
        'shopee_order_received' => [
            'name' => 'Pesanan Shopee Diterima',
            'trigger' => ['type' => 'webhook', 'event' => 'shopee.order.created'],
            'steps' => [
                'verify_signature',
                'create_order',
                'decrement_stock',
                'generate_invoice',
                'notify_customer',
                'log_analytics',
            ],
        ],
        'invoice_overdue_reminder' => [
            'name' => 'Peringatan Invois Tertunggak',
            'trigger' => ['type' => 'schedule', 'cron' => '0 9 * * *'],
            'conditions' => [['field' => 'days_overdue', 'operator' => 'greater_than', 'value' => 7]],
            'steps' => ['send_reminder', 'create_followup_task'],
        ],
        'low_stock_alert' => [
            'name' => 'Amaran Stok Rendah',
            'trigger' => ['type' => 'domain_event', 'event' => 'inventory.threshold_reached'],
            'steps' => ['notify_purchasing', 'draft_purchase_order'],
        ],
    ],

];
