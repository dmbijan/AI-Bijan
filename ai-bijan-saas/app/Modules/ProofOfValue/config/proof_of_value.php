<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proof of Value (BAB 84 / Idea #5)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi metrik hasil per modul. Corak diambil daripada
    | ServiceTitan Pro Products yang/results boleh diukur.
    |
    */

    // Modul yang/metrik standard dijana untuk tenant baharu
    'default_modules' => [
        'inventory', 'sales', 'marketing', 'hr', 'finance', 'support', 'logistics', 'cod',
    ],

    'export' => [
        // Where PDF proof of value disimpan
        'disk' => env('POV_DISK', 'documents'),
        'filename_prefix' => env('POV_FILENAME_PREFIX', 'ProofOfValue'),
        // Rujukan tenant dalam PDF
        'include_branding' => (bool) env('POV_INCLUDE_BRANDING', true),
    ],

    'verification' => [
        // Metrik tanpa baseline tidak boleh disahkan
        'require_baseline' => (bool) env('POV_REQUIRE_BASELINE', true),
        // Minimum bilangan bacaan untuk dianggap "disahkan"
        'min_readings' => (int) env('POV_MIN_READINGS', 2),
    ],
];
