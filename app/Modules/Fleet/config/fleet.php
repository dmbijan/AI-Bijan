<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fleet & GPS (BAB 84 / Idea #10)
    |--------------------------------------------------------------------------
    |
    | Pengurusan armada, GPS, perjalanan, dan amaran keselamatan.
    | Corak ditiru daripada ServiceTitan "Fleet Pro".
    |
    */

    'alerts' => [
        // Peratus tahap bahan api untuk mencetuskan amaran
        'low_fuel_percent' => (float) env('FLEET_LOW_FUEL_PERCENT', 15),

        // Ambang perubahan laju (km/h per saat) untuk tingkah laku memandu
        'harsh_braking_threshold' => (float) env('FLEET_HARSH_BRAKING', 4.0),
        'harsh_acceleration_threshold' => (float) env('FLEET_HARSH_ACCEL', 3.5),

        // Had laju lalai (km/h) — MY lebuh raya = 110
        'speed_limit_kmh' => (float) env('FLEET_SPEED_LIMIT', 110),

        // Selang masa untuk elak amaran berulang (minit)
        'dedupe_window_minutes' => (int) env('FLEET_DEDUPE_MINUTES', 30),
    ],

    'history' => [
        // Berapa lama sejarah lokasi disimpan (hari)
        'retention_days' => (int) env('FLEET_HISTORY_DAYS', 90),
    ],

    'integrations' => [
        // Provider GPS yang disokong (adapter pattern)
        'samsara' => env('SAMSARA_API_KEY'),
        'fleetio' => env('FLEETIO_API_KEY'),
        'motive' => env('MOTIVE_API_KEY'),
        'verkada' => env('VERKADA_API_KEY'),
    ],

    'routing' => [
        'provider' => env('FLEET_ROUTING_PROVIDER', 'google'),
        'google_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'max_waypoints' => (int) env('FLEET_MAX_WAYPOINTS', 25),
    ],
];
