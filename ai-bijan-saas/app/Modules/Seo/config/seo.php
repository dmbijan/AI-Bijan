<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO & Metadata Engine (BAB 86 / Idea #20)
    |--------------------------------------------------------------------------
    |
    | Had amalan industri untuk metadata, sitemap dan audit.
    |
    */

    'limits' => [
        'title_min' => 15,
        'title_max' => 60,
        'description_min' => 50,
        'description_max' => 160,
        'slug_max' => 75,
        'keywords_max' => 7,
    ],

    'audit' => [
        // Berat penalti mengikut keseriusan
        'severity_weights' => [
            'error' => 20,
            'warning' => 8,
            'info' => 3,
        ],
        // Audit automatik setiap kali metadata disimpan
        'auto_audit_on_save' => (bool) env('SEO_AUTO_AUDIT', true),
    ],

    'ai' => [
        // Guna AI Gateway untuk cadangan metadata
        'enabled' => (bool) env('SEO_AI_ENABLED', true),
        'task_type' => env('SEO_AI_TASK_TYPE', 'seo'),
        'max_tokens' => (int) env('SEO_AI_MAX_TOKENS', 800),
        // Auto-terapkan cadangan tanpa semakan manusia (perlu keyakinan > 0.9)
        'auto_apply_confidence' => (float) env('SEO_AUTO_APPLY_CONFIDENCE', 0.0),
    ],

    'sitemap' => [
        'default_changefreq' => 'weekly',
        'default_priority' => 0.5,
        // Had URL setiap sitemap (protocol had 50,000)
        'max_urls' => 50000,
        // Laluan awam sitemap
        'public_path' => 'sitemap.xml',
    ],

    'json_ld' => [
        // Type schema.org yang disokong
        'types' => ['product', 'page', 'article', 'video', 'category', 'blog_post'],
    ],
];
