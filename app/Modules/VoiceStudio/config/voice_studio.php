<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VoiceStudio — enjin suara (TTS)
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/debpalash/VoiceStudio
    |
    | Menyediakan sintesis suara untuk PKS Malaysia: profil suara, penukaran
    | teks-ke-audio, dan penandaan gaya (gaya pencerahan, pengiklanan).
    |
    */

    'enabled' => (bool) env('VOICE_STUDIO_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_characters' => (int) env('VOICE_STUDIO_MAX_CHARS', 5000),
        'max_profiles_per_tenant' => (int) env('VOICE_STUDIO_MAX_PROFILES', 50),
        'tracks_retained' => (int) env('VOICE_STUDIO_RETAIN_TRACKS', 500),
    ],

    'default_engine' => env('VOICE_STUDIO_ENGINE', 'elevenlabs'),
    'default_format' => env('VOICE_STUDIO_FORMAT', 'mp3'),
    'timeout_seconds' => (int) env('VOICE_STUDIO_TIMEOUT', 120),

    /*
    | Anggaran kos (RM per aksara teks).
    */
    'cost_rm_per_character' => (float) env('VOICE_STUDIO_COST_PER_CHAR', 0.00008),

    /*
    | Tetapan enjin. `api_key` bernilai disulitkan (Crypt::encryptString).
    */
    'engines' => [
        'elevenlabs' => [
            'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io'),
            'api_key' => env('ELEVENLABS_API_KEY'),
            'model_id' => env('ELEVENLABS_MODEL_ID', 'eleven_multilingual_v2'),
            'default_voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'),
        ],
        'elevenlabs_local' => [
            'base_url' => env('ELEVENLABS_LOCAL_URL', 'http://localhost:5001'),
            'api_key' => env('ELEVENLABS_LOCAL_API_KEY'),
            'model_id' => env('ELEVENLABS_LOCAL_MODEL_ID', 'eleven_multilingual_v2'),
            'default_voice_id' => env('ELEVENLABS_LOCAL_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'),
        ],
        'openai' => [
            'base_url' => env('OPENAI_TTS_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_TTS_API_KEY'),
            'model' => env('OPENAI_TTS_MODEL', 'gpt-4o-mini-tts'),
            'default_voice' => env('OPENAI_TTS_VOICE', 'alloy'),
        ],
        'piper' => [
            'base_url' => env('PIPER_TTS_URL', 'http://localhost:5000'),
            'default_voice' => env('PIPER_DEFAULT_VOICE', 'en_US-lessac-medium'),
        ],
        'coqui' => [
            'base_url' => env('COQUI_TTS_URL', 'http://localhost:5002'),
        ],
    ],

    /*
    | Normalisasi teks sebelum sintesis.
    */
    'text' => [
        'strip_html' => true,
        'expand_abbreviations' => true,
        'max_sentences_chunk' => (int) env('VOICE_STUDIO_CHUNK_SENTENCES', 3),
    ],

    /*
    | Semakan automatik selepas dijana (untuk kawalan kualiti).
    */
    'quality' => [
        'min_duration_seconds' => 0.5,
        'max_duration_seconds' => 600,
        'detect_silence_ratio' => 0.9,
    ],

];
