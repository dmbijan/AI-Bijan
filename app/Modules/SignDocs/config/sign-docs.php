<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Sign & Docs Configuration
    |--------------------------------------------------------------------------
    */

    // Document Settings
    'documents' => [
        'max_file_size_mb' => 50,
        'allowed_types' => ['pdf', 'docx', 'xlsx', 'pptx', 'jpg', 'png', 'gif', 'txt', 'csv'],
        'storage_path' => 'documents/',
        'thumbnail_size' => '300x200',
        'preview_max_pages' => 10,
        'version_history_enabled' => true,
        'max_versions' => 50,
    ],

    // Template Settings
    'templates' => [
        'categories' => [
            'invoice',
            'contract',
            'proposal',
            'report',
            'letter',
            'form',
            'certificate',
            'other',
        ],
        'placeholders_prefix' => '{{',
        'placeholders_suffix' => '}}',
    ],

    // Electronic Signature Settings
    'signatures' => [
        'types' => ['drawn', 'typed', 'uploaded', 'digital_certificate'],
        'max_draw_points' => 5000,
        'font_options' => [
            'Arial',
            'Times New Roman',
            'Courier New',
            'Helvetica',
        ],
        'signature_image_size' => '400x150',
        'digital_certificate_enabled' => false,
        'timestamp_enabled' => true,
        'audit_trail_enabled' => true,
    ],

    // Workflow Settings
    'workflow' => [
        'status_flow' => [
            'draft',
            'pending_review',
            'in_review',
            'pending_approval',
            'approved',
            'pending_signature',
            'signed',
            'archived',
            'rejected',
        ],
        'auto_advance' => false,
        'reminder_interval_hours' => 24,
        'max_reviewers' => 10,
        'concurrent_signing' => true,
        'signing_order_required' => false,
    ],

    // Integration Settings
    'integrations' => [
        'b_site_embed_enabled' => true,
        'b_crm_document_sync' => true,
        'b_projects_docs' => true,
        'email_notifications' => true,
        'webhook_enabled' => true,
    ],

    // Security Settings
    'security' => [
        'watermark_enabled' => false,
        'download_restrictions' => true,
        'print_restrictions' => true,
        'expiry_date_support' => true,
        'access_log_enabled' => true,
    ],
];
