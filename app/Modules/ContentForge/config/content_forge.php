<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ContentForge — penjanaan kandungan & pengoptimuman SEO
    |--------------------------------------------------------------------------
    |
    | Berasal daripada https://seowriting.ai/
    |
    | Artikel panjang, senarai produk, pemeriksa SEO, 50+ bahasa,
    | penjanaan pukal & auto-post WordPress.
    */

    'enabled' => (bool) env('CONTENT_FORGE_ENABLED', true),

    /*
    | Lalai penjanaan.
    */
    'defaults' => [
        'word_count' => (int) env('CONTENT_FORGE_WORD_COUNT', 1500),
        'language' => env('CONTENT_FORGE_LANGUAGE', 'ms'),
        'type' => env('CONTENT_FORGE_TYPE', 'article'),
        'model' => env('CONTENT_FORGE_MODEL', 'default'),
    ],

    /*
    | Bahasa yang disokong (50+).
    */
    'languages' => [
        'ms' => 'Bahasa Melayu',
        'en' => 'English',
        'zh' => '中文',
        'ta' => 'தமிழ்',
        'id' => 'Bahasa Indonesia',
        'ar' => 'العربية',
        'hi' => 'हिन्दी',
        'ja' => '日本語',
        'ko' => '한국어',
        'th' => 'ไทย',
        'vi' => 'Tiếng Việt',
        'tl' => 'Tagalog',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'pt' => 'Português',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'ru' => 'Русский',
        'tr' => 'Türkçe',
    ],

    /*
    | Jenis dokumen.
    */
    'types' => [
        'article' => 'Artikel',
        'product_roundup' => 'Senarai Produk',
        'product_review' => 'Ulasan Produk',
        'how_to' => 'Panduan Cara',
        'product_listing' => 'Senarai Produk Affiliate',
        'landing' => 'Halaman Pendaratan',
    ],

    /*
    | Had.
    */
    'limits' => [
        'max_word_count' => (int) env('CONTENT_FORGE_MAX_WORDS', 5000),
        'max_keywords' => (int) env('CONTENT_FORGE_MAX_KEYWORDS', 20),
        'bulk_max' => (int) env('CONTENT_FORGE_BULK_MAX', 100),
        'max_sites' => (int) env('CONTENT_FORGE_MAX_SITES', 20),
    ],

    /*
    | Pemeriksa SEO.
    */
    'seo' => [
        'title_min' => 30,
        'title_max' => 65,
        'meta_min' => 120,
        'meta_max' => 160,
        'min_h2' => 3,
        'min_links' => 2,
        'excellent_threshold' => 80,
        'good_threshold' => 60,
    ],

    /*
    | Auto-post WordPress.
    */
    'publish' => [
        'platform' => env('CONTENT_FORGE_PLATFORM', 'wordpress'),
        'auto_publish' => (bool) env('CONTENT_FORGE_AUTO_PUBLISH', false),
        'timeout_seconds' => (int) env('CONTENT_FORGE_TIMEOUT', 30),
    ],

];
