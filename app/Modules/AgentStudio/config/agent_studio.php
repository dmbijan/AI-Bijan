<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agent Studio (BAB 84 / Idea #8)
    |--------------------------------------------------------------------------
    |
    | Tetapan B-Ejen — builder ejen AI tanpa kod ala Zoho Agent Store.
    |
    */

    'templates' => [
        // Jana ejen automatik untuk tenant baharu
        'auto_generate_on_register' => (bool) env('AGENT_STUDIO_AUTO_GENERATE', false),
        // Templat default yang dicadangkan
        'default_templates' => ['sales_agent', 'support_agent'],
    ],

    'autonomy' => [
        // Tahap otonomi maksimum dibenarkan
        'max_level' => (int) env('AGENT_STUDIO_MAX_AUTONOMY', 2),
        // Ejen perlu sekurang-kurangnya N ujian lulus sebelum publish
        'min_passing_tests' => (int) env('AGENT_STUDIO_MIN_TESTS', 1),
    ],

    'execution' => [
        'timeout_ms' => (int) env('AGENT_STUDIO_TIMEOUT_MS', 60000),
        'max_tools_per_run' => (int) env('AGENT_STUDIO_MAX_TOOLS', 20),
        'default_model' => env('AGENT_STUDIO_DEFAULT_MODEL', 'default'),
        'default_temperature' => (float) env('AGENT_STUDIO_DEFAULT_TEMP', 0.70),
    ],

    'categories' => [
        'sales', 'support', 'ops', 'finance', 'hr', 'growth',
    ],
];
