<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Open Generative AI — enjin generatif pelbagai provider
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/Anil-matcha/Open-Generative-AI
    |
    | Idea: AI BIJAN tidak terikat kepada satu pembekal model. Tenant boleh
    | sambungkan provider sendiri (OpenAI, Anthropic, Gemini, Ollama tempatan,
    | Replicate, atau pelayan serasi OpenAI) melalui `gen_ai_providers`.
    |
    */

    'enabled' => (bool) env('OPEN_GEN_AI_ENABLED', true),

    /*
    | Had dan kos
    */
    'max_prompt_chars' => (int) env('OPEN_GEN_AI_MAX_PROMPT', 32000),
    'max_output_tokens' => (int) env('OPEN_GEN_AI_MAX_TOKENS', 8192),
    'default_max_tokens' => (int) env('OPEN_GEN_AI_DEFAULT_TOKENS', 1024),
    'default_temperature' => (float) env('OPEN_GEN_AI_TEMPERATURE', 0.7),
    'timeout_seconds' => (int) env('OPEN_GEN_AI_TIMEOUT', 120),

    /*
    | Kadar kos (RM per token) mengikut kemahiran.
    | Dikira untuk jejak kos dalam `gen_ai_jobs.cost_rm`.
    */
    'cost_rates' => [
        'text' => (float) env('OPEN_GEN_AI_COST_TEXT', 0.0001),
        'code' => (float) env('OPEN_GEN_AI_COST_CODE', 0.00012),
        'image' => (float) env('OPEN_GEN_AI_COST_IMAGE', 0.0004),
        'video' => (float) env('OPEN_GEN_AI_COST_VIDEO', 0.002),
        'audio' => (float) env('OPEN_GEN_AI_COST_AUDIO', 0.0002),
    ],

    /*
    | Kebolehan yang boleh ditanda pada provider.
    */
    'capabilities' => ['text', 'image', 'video', 'audio', 'code'],

    /*
    | Bilangan job disimpan untuk jejak audit.
    */
    'retain_jobs' => (int) env('OPEN_GEN_AI_RETAIN_JOBS', 1000),

    /*
    | Tetapan lalai provider baharu.
    */
    'default_provider_kind' => env('OPEN_GEN_AI_DEFAULT_KIND', 'openai'),

];
