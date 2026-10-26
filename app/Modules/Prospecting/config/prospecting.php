<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prospecting Engine (BAB 82 / Fasa 1)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mencari prospek: import CSV, connector API berlesen,
    | dan scraping web awam yang hormat robots.txt.
    |
    */

    'max_import_rows' => (int) env('PROSPECTING_MAX_IMPORT_ROWS', 5000),

    'scraper' => [
        // User-Agent yang mengenaikan diri. JANGAN pretend to be browser.
        'user_agent' => env('PROSPECTING_USER_AGENT', 'AI-Bijan-Prospecting/1.0 (+https://aibijan.com/bot)'),
        'timeout' => (int) env('PROSPECTING_SCRAPER_TIMEOUT', 20),
        'rate_limit_per_minute' => (int) env('PROSPECTING_RATE_LIMIT', 10),
        'max_pages_per_job' => (int) env('PROSPECTING_MAX_PAGES', 200),
        'respect_robots' => (bool) env('PROSPECTING_RESPECT_ROBOTS', true),
    ],

    'connectors' => [
        // Adapter connector pihak ketiga. Kunci API disimpan oleh tenant
        // (rujukan ProspectSource.config.credential_ref), BUKAN di sini.
        'available' => ['apollo', 'clay', 'manual'],
    ],

    'consent' => [
        // Reply_to address untuk e-mel blast (wajib untuk deliverability).
        'reply_to' => env('BLAST_REPLY_TO', 'no-reply@aibijan.com'),
        'max_age_days' => (int) env('PROSPECTING_CONSENT_MAX_AGE', 365),
    ],

    'scoring' => [
        // Berat setiap faktor (0-100 total).
        'weights' => [
            'has_email' => 20,
            'has_phone' => 10,
            'has_company' => 15,
            'has_job_title' => 10,
            'consent_granted' => 25,
            'verified' => 10,
            'website_present' => 5,
            'linkedin_present' => 5,
        ],
        'hot_threshold' => (int) env('PROSPECTING_HOT_THRESHOLD', 70),
        'warm_threshold' => (int) env('PROSPECTING_WARM_THRESHOLD', 45),
    ],
];
