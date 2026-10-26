<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Business Intelligence & Analytics Engine
    |
    */

    // Metric Settings
    'metric' => [
        'auto_refresh_enabled' => true,
        'refresh_interval_minutes' => 5,
        'max_history_days' => 365,
        'enable_real_time' => true,
        'batch_size' => 1000,
    ],

    // Report Settings
    'report' => [
        'scheduling_enabled' => true,
        'supported_formats' => ['pdf', 'excel', 'csv', 'json'],
        'max_report_size_mb' => 50,
        'default_page_size' => 50,
        'max_page_size' => 1000,
        'cache_duration_minutes' => 15,
    ],

    // Dashboard Settings
    'dashboard' => [
        'max_widgets_per_dashboard' => 20,
        'widget_refresh_interval' => 30, // seconds
        'enable_custom_dashboards' => true,
        'default_dashboard' => 'main',
        'enable_share_dashboards' => true,
    ],

    // Data Sources
    'data_sources' => [
        'enabled_modules' => [
            'payroll', 'finance', 'scm', 'crm', 'document_ai',
            'invoicing', 'virtual_office', 'hrms', 'projects',
            'assets', 'pos', 'booking', 'marketplace', 'site',
            'marketing', 'loyalty', 'affiliate', 'link', 'sign_docs',
            'omnichannel', 'logistics',
        ],
        'enable_cross_module_aggregation' => true,
        'enable_external_data' => false,
    ],

    // AI Insights (B-Brain Hub Integration)
    'ai_insights' => [
        'enabled' => false,
        'insight_generation_interval_hours' => 24,
        'min_data_points_for_insight' => 100,
        'confidence_threshold' => 0.75,
    ],

    // Export Settings
    'export' => [
        'enable_bulk_export' => true,
        'max_records_per_export' => 100000,
        'async_export_enabled' => true,
        'export_retention_hours' => 24,
    ],

    // Alert Settings
    'alerts' => [
        'threshold_based_alerts' => true,
        'anomaly_detection' => false,
        'alert_channels' => ['email', 'in_app', 'webhook'],
        'quiet_hours' => [
            'enabled' => false,
            'start' => '22:00',
            'end' => '06:00',
            'timezone' => 'Asia/Kuala_Lumpur',
        ],
    ],
];
