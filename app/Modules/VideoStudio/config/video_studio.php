<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VideoStudio — orkestrasi skill video AI BIJAN
    |--------------------------------------------------------------------------
    |
    | Membungkus tiga skill dalam `.github/skills/`:
    |   - aibijan-mograph      (motion-graphics explainer 2D/2.5D)
    |   - aibijan-motion      (filem sinematik 3D 9:16)
    |   - aibijan-video-editor(sunting rakaman sedia ada)
    |
    | Skill ini di-rebrand daripada VOYNANN v1.7.0 / v1.5.0 / v3.9.1.
    */

    'enabled' => (bool) env('VIDEO_STUDIO_ENABLED', true),

    /*
    | Lokasi skill (relatif kepada base_path).
    */
    'skills_path' => env('VIDEO_STUDIO_SKILLS_PATH', '.github/skills'),

    /*
    | Tetapan render lalai.
    */
    'defaults' => [
        'aspect_ratio' => env('VIDEO_STUDIO_ASPECT', '9:16'),
        'frame_rate' => (int) env('VIDEO_STUDIO_FPS', 60),
        'skill' => env('VIDEO_STUDIO_SKILL', 'mograph'),
    ],

    /*
    | Had
    */
    'limits' => [
        'max_duration_seconds' => (int) env('VIDEO_STUDIO_MAX_DURATION', 1800),
        'max_input_mb' => (int) env('VIDEO_STUDIO_MAX_INPUT_MB', 500),
        'max_projects' => (int) env('VIDEO_STUDIO_MAX_PROJECTS', 100),
        'concurrent_renders' => (int) env('VIDEO_STUDIO_CONCURRENT', 1),
    ],

    /*
    | Keperluan luaran (disemak oleh preflight).
    */
    'requirements' => [
        'node' => true,
        'npm' => true,
        'ffmpeg' => true,
        'chromium' => (bool) env('VIDEO_STUDIO_REQUIRE_CHROMIUM', false),
    ],

    /*
    | Preflight
    */
    'preflight' => [
        'timeout_seconds' => (int) env('VIDEO_STUDIO_PREFLIGHT_TIMEOUT', 30),
        'cache_seconds' => (int) env('VIDEO_STUDIO_PREFLIGHT_CACHE', 300),
    ],

    /*
    | Stor
    */
    'storage' => [
        'disk' => env('VIDEO_STUDIO_DISK', 'public'),
        'work_dir' => env('VIDEO_STUDIO_WORK_DIR', 'video-studio'),
        'retain_projects' => (int) env('VIDEO_STUDIO_RETAIN', 200),
    ],

    /*
    | Nama kemas kini QC.
    */
    'qc' => [
        'check_audio_continuity' => (bool) env('VIDEO_STUDIO_QC_AUDIO', true),
        'check_caption_sync' => (bool) env('VIDEO_STUDIO_QC_CAPTIONS', true),
        'min_bitrate_kbps' => (int) env('VIDEO_STUDIO_QC_BITRATE', 4000),
        'min_duration_seconds' => (float) env('VIDEO_STUDIO_QC_MIN_DURATION', 1.0),
    ],

];
