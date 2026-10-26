<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Brain Hub Configuration
    |--------------------------------------------------------------------------
    |
    | AI-Powered Insight, Forecast & Recommendation Engine
    |
    */

    // AI Provider Settings
    'ai_provider' => [
        'enabled' => false,
        'default_provider' => 'openai', // openai, anthropic, azure, local
        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_MODEL', 'gpt-4'),
                'base_url' => env('OPENAI_BASE_URL'),
            ],
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'model' => env('ANTHROPIC_MODEL', 'claude-3-opus'),
            ],
            'azure' => [
                'api_key' => env('AZURE_AI_KEY'),
                'endpoint' => env('AZURE_AI_ENDPOINT'),
                'deployment' => env('AZURE_AI_DEPLOYMENT', 'gpt-4'),
            ],
        ],
        'max_tokens' => 2000,
        'temperature' => 0.3,
        'timeout_seconds' => 30,
    ],

    // Insight Generation
    'insights' => [
        'auto_generation_enabled' => true,
        'generation_interval_hours' => 6,
        'min_data_points' => 50,
        'max_insights_per_tenant' => 50,
        'categories' => [
            'financial',
            'sales',
            'operations',
            'hr',
            'marketing',
            'customer',
            'inventory',
            'logistics',
        ],
        'severity_levels' => ['low', 'medium', 'high', 'critical'],
        'retention_days' => 90,
    ],

    // Forecasting Settings
    'forecasting' => [
        'enabled' => true,
        'models' => [
            'linear_regression',
            'moving_average',
            'exponential_smoothing',
            'arima',
        ],
        'default_model' => 'exponential_smoothing',
        'confidence_levels' => [0.80, 0.90, 0.95],
        'default_horizon_days' => 30,
        'max_horizon_days' => 365,
        'min_history_days' => 90,
        'retraining_frequency' => 'weekly', // daily, weekly, monthly
    ],

    // Recommendation Engine
    'recommendations' => [
        'enabled' => true,
        'max_recommendations' => 20,
        'refresh_interval_minutes' => 15,
        'types' => [
            'actionable',      // Suggested actions
            'optimization',    // Process improvements
            'risk_mitigation', // Risk warnings
            'opportunity',     // Growth opportunities
            'cost_saving',     // Cost reduction
        ],
        'personalization_enabled' => true,
        'feedback_tracking' => true,
    ],

    // Pattern Recognition
    'patterns' => [
        'enabled' => true,
        'detection_window_days' => 90,
        'min_pattern_strength' => 0.7,
        'pattern_types' => [
            'seasonal',       // Seasonal patterns
            'trend',          // Long-term trends
            'cyclical',       // Business cycles
            'anomaly',        // Anomalous behavior
            'correlation',    // Cross-variable correlations
        ],
        'retention_days' => 180,
    ],

    // Notification Settings
    'notifications' => [
        'critical_alerts_immediate' => true,
        'daily_digest_enabled' => true,
        'digest_time' => '08:00',
        'channels' => ['in_app', 'email', 'webhook'],
        'quiet_hours' => [
            'enabled' => false,
            'start' => '22:00',
            'end' => '06:00',
            'timezone' => 'Asia/Kuala_Lumpur',
        ],
    ],

    // Data Sources
    'data_sources' => [
        'analytics_metrics' => true,
        'finance_data' => true,
        'sales_data' => true,
        'inventory_data' => true,
        'hr_data' => true,
        'marketing_data' => true,
        'external_data' => false, // Economic indicators, market data
    ],
];
