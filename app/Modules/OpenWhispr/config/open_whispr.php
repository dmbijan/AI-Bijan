<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenWhispr — diktasi suara ke teks
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/OpenWhispr/openwhispr
    |
    | STT untuk PKS Malaysia — boleh hoskan model Whisper sendiri (privasi)
    | atau guna API awam. Dilengkapi kamus perniagaan tersuai.
    |
    */

    'enabled' => (bool) env('OPEN_WHISPR_ENABLED', true),

    /*
    | Enjin dan model
    */
    'default_engine' => env('OPEN_WHISPR_ENGINE', 'whisper_local'),
    'default_model' => env('OPEN_WHISPR_MODEL', 'base'),
    'default_language' => env('OPEN_WHISPR_LANGUAGE', 'ms'),
    'timeout_seconds' => (int) env('OPEN_WHISPR_TIMEOUT', 300),

    /*
    | Had
    */
    'limits' => [
        'max_audio_mb' => (int) env('OPEN_WHISPR_MAX_AUDIO_MB', 100),
        'max_duration_seconds' => (int) env('OPEN_WHISPR_MAX_DURATION', 3600),
        'sessions_retained' => (int) env('OPEN_WHISPR_RETAIN_SESSIONS', 200),
    ],

    /*
    | Tetapan enjin. `api_key` bernilai disulitkan (Crypt::encryptString).
    */
    'engines' => [
        'whisper_local' => [
            'base_url' => env('WHISPER_LOCAL_URL', 'http://localhost:8080'),
            'api_key' => env('WHISPER_LOCAL_API_KEY'),
        ],
        'openai' => [
            'base_url' => env('OPENAI_STT_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_STT_API_KEY'),
        ],
    ],

    /*
    | Model yang disokong (untuk UI).
    */
    'models' => [
        'tiny' => ['label' => 'Tiny — paling pantas, ketepatan rendah', 'multilingual' => true],
        'base' => ['label' => 'Base — seimbang (lalai)', 'multilingual' => true],
        'small' => ['label' => 'Small — lebih tepat', 'multilingual' => true],
        'medium' => ['label' => 'Medium — ketepatan tinggi', 'multilingual' => true],
        'large-v3' => ['label' => 'Large v3 — terbaik, paling berat', 'multilingual' => true],
    ],

    /*
    | Pemprosesan selepas transkripsi.
    */
    'processing' => [
        'strip_html' => true,
        'remove_fillers' => (bool) env('OPEN_WHISPR_REMOVE_FILLERS', true),
        'filler_words' => [
            'um', 'uh', 'er', 'ah', 'eh', 'em', 'mm', 'hmm',
            'erm', 'okay', 'ok', 'like', 'you know', 'so',
        ],
        'apply_vocabulary' => (bool) env('OPEN_WHISPR_APPLY_VOCAB', true),
        'capitalise_first' => true,
    ],

    /*
    | Tetapan hotkey / tetapan klien (untuk desktop companion).
    */
    'client' => [
        'hotkey_record' => env('OPEN_WHISPR_HOTKEY_RECORD', 'Ctrl+Shift+Space'),
        'hotkey_stop' => env('OPEN_WHISPR_HOTKEY_STOP', 'Ctrl+Shift+Space'),
        'auto_paste' => (bool) env('OPEN_WHISPR_AUTO_PASTE', false),
        'silence_threshold_db' => (int) env('OPEN_WHISPR_SILENCE_DB', -45),
    ],

];
