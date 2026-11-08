<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Workspace — dokumen & pangkalan data kolaboratif
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/AppFlowy-IO/AppFlowy
    |
    | Ruang kerja, halaman berasaskan blok, pangkalan data dengan pelbagai
    | paparan, komen dan versi sejarah — dalam AI BIJAN, untuk pelan
    | perniagaan, SOP, mesyuarat dan pangkalan pengetahuan syarikat.
    |
    */

    'enabled' => (bool) env('WORKSPACE_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_spaces' => (int) env('WORKSPACE_MAX_SPACES', 50),
        'max_pages_per_space' => (int) env('WORKSPACE_MAX_PAGES', 500),
        'max_blocks_per_page' => (int) env('WORKSPACE_MAX_BLOCKS', 2000),
        'max_rows_per_database' => (int) env('WORKSPACE_MAX_ROWS', 5000),
        'max_versions_per_page' => (int) env('WORKSPACE_MAX_VERSIONS', 50),
        'max_title_length' => (int) env('WORKSPACE_MAX_TITLE', 250),
    ],

    /*
    | Versi sejarah kandungan.
    */
    'versions' => [
        'enabled' => (bool) env('WORKSPACE_VERSIONS', true),
        'retain' => (int) env('WORKSPACE_RETAIN_VERSIONS', 50),
    ],

    /*
    | Ciri kolaborasi.
    */
    'collaboration' => [
        'allow_locking' => (bool) env('WORKSPACE_ALLOW_LOCKING', true),
        'comments_enabled' => (bool) env('WORKSPACE_COMMENTS', true),
        'auto_save_interval_seconds' => (int) env('WORKSPACE_AUTOSAVE', 5),
        'presence_enabled' => (bool) env('WORKSPACE_PRESENCE', true),
    ],

    /*
    | Paparan pangkalan data lalai.
    */
    'default_view' => env('WORKSPACE_DEFAULT_VIEW', 'grid'),

    /*
    | Import / export.
    */
    'import_export' => [
        'allowed_formats' => ['md', 'html', 'json', 'csv'],
        'max_import_mb' => (int) env('WORKSPACE_MAX_IMPORT_MB', 25),
    ],

];
