<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenSEO — intel Competitive & SEO terbuka
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/every-app/open-seo
    |
    | Melengkapkan modul `Seo` (metadata, audit, sitemap) dengan:
    |   - jejak kedudukan SERP dan sejarah
    |   -flagcountry kata kunci + skor keutamaan
    |   - pemantauan backlink
    |   - analisis celah kandungan vs pesaing
    |
    */

    'enabled' => (bool) env('OPEN_SEO_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_projects' => (int) env('OPEN_SEO_MAX_PROJECTS', 20),
        'max_keywords_per_project' => (int) env('OPEN_SEO_MAX_KEYWORDS', 2000),
        'max_backlinks_per_project' => (int) env('OPEN_SEO_MAX_BACKLINKS', 5000),
        'audit_timeout_seconds' => (int) env('OPEN_SEO_AUDIT_TIMEOUT', 20),
    ],

    /*
    | Sumber data luaran (SERP, backlink). Tiada = guna data manual.
    |
    | Setiap sumber menyokong: base_url, api_key (disulitkan), enabled.
    */
    'data_sources' => [
        'serp' => [
            'enabled' => (bool) env('OPEN_SEO_SERP_ENABLED', false),
            'base_url' => env('OPEN_SEO_SERP_URL', 'https://api.searchapi.io/v1'),
            'api_key' => env('OPEN_SEO_SERP_KEY'),
            'country' => env('OPEN_SEO_COUNTRY', 'my'),
            'language' => env('OPEN_SEO_LANGUAGE', 'ms'),
        ],
        'backlink' => [
            'enabled' => (bool) env('OPEN_SEO_BACKLINK_ENABLED', false),
            'base_url' => env('OPEN_SEO_BACKLINK_URL', 'https://api.dataforseo.com/v3'),
            'api_key' => env('OPEN_SEO_BACKLINK_KEY'),
        ],
    ],

    /*
    | Audit teknikal
    */
    'audit' => [
        'title_min' => 15,
        'title_max' => 60,
        'description_min' => 50,
        'description_max' => 160,
        'user_agent' => env('OPEN_SEO_USER_AGENT', 'AI-Bijan-SEO-Audit/1.0'),
        'cache_seconds' => (int) env('OPEN_SEO_AUDIT_CACHE', 600),
    ],

    /*
    | Skor keutamaan kata kunci (bobot peratus).
    */
    'priority_weights' => [
        'volume' => 40,
        'difficulty' => 40,
        'position' => 20,
    ],

    /*
    | Ambang kelayakan celah kandungan.
    */
    'gap' => [
        'max_difficulty' => 40,
        'min_volume' => 10,
    ],

    /*
    | Tetapan jadual SERP.
    */
    'serp' => [
        'history_limit' => (int) env('OPEN_SEO_SERP_HISTORY', 90),
        'tracked_positions' => [1, 3, 10, 100],
    ],

];
