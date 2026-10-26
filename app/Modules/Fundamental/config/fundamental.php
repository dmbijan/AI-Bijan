<?php

/**
 * Konfigurasi B-Fundamental (BAB 90).
 */
return [
    'enabled' => env('FUNDAMENTAL_ENABLED', true),

    /*
    |----------------------------------------------------------------------
    | Health check
    |----------------------------------------------------------------------
    */
    'health' => [
        'components' => ['database', 'cache', 'queue', 'storage'],
        'interval_seconds' => (int) env('FUNDAMENTAL_HEALTH_INTERVAL', 60),
        'timeout_ms' => (int) env('FUNDAMENTAL_HEALTH_TIMEOUT_MS', 2000),
        'degraded_threshold' => 500,
        'unhealthy_threshold' => 2000,
    ],

    /*
    |----------------------------------------------------------------------
    | Observability
    |----------------------------------------------------------------------
    */
    'observability' => [
        'enabled' => (bool) env('FUNDAMENTAL_OBSERVABILITY_ENABLED', true),
        'retention_days' => (int) env('FUNDAMENTAL_OBSERVABILITY_RETENTION', 30),
        'max_context_bytes' => 8192,
        'slow_query_ms' => (int) env('FUNDAMENTAL_SLOW_QUERY_MS', 1000),
    ],

    /*
    |----------------------------------------------------------------------
    | Metrics
    |----------------------------------------------------------------------
    */
    'metrics' => [
        'enabled' => (bool) env('FUNDAMENTAL_METRICS_ENABLED', true),
        'retention_days' => (int) env('FUNDAMENTAL_METRICS_RETENTION', 90),
        'default_limit' => 50,
    ],

    /*
    |----------------------------------------------------------------------
    | Alerts
    |----------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => (bool) env('FUNDAMENTAL_ALERTS_ENABLED', true),
        'evaluation_interval_minutes' => (int) env('FUNDAMENTAL_ALERT_INTERVAL', 5),
        'channels' => ['in_app', 'email', 'whatsapp', 'slack', 'webhook'],
    ],

    /*
    |----------------------------------------------------------------------
    | Queue monitoring
    |----------------------------------------------------------------------
    */
    'queue' => [
        'monitor_enabled' => (bool) env('FUNDAMENTAL_QUEUE_MONITOR_ENABLED', true),
        'failed_threshold' => (int) env('FUNDAMENTAL_QUEUE_FAILED_THRESHOLD', 1),
        'pending_threshold' => (int) env('FUNDAMENTAL_QUEUE_PENDING_THRESHOLD', 1000),
        'stuck_after_minutes' => (int) env('FUNDAMENTAL_QUEUE_STUCK_MINUTES', 15),
    ],
];
