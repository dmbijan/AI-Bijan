<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ScreenRecorder — rakaman skrin & perkongsian ala Cap
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/CapSoftware/Cap
    |
    | Melengkapkan modul `MediaEditor` dengan rakaman skrin + audio,
    | pautan perkongsian bermasa dan komen bercapah masa.
    |
    */

    'enabled' => (bool) env('SCREEN_RECORDER_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_file_mb' => (int) env('SCREEN_RECORDER_MAX_FILE_MB', 500),
        'max_duration_seconds' => (int) env('SCREEN_RECORDER_MAX_DURATION', 7200),
        'recordings_retained' => (int) env('SCREEN_RECORDER_RETAIN', 500),
        'max_comments_per_recording' => (int) env('SCREEN_RECORDER_MAX_COMMENTS', 200),
    ],

    /*
    | Tetapan rakaman.
    */
    'recording' => [
        'frame_rate' => (int) env('SCREEN_RECORDER_FPS', 30),
        'default_resolution' => env('SCREEN_RECORDER_RESOLUTION', '1920x1080'),
        'include_audio' => (bool) env('SCREEN_RECORDER_AUDIO', true),
        'include_cursor' => (bool) env('SCREEN_RECORDER_CURSOR', true),
        'thumbnail_timestamps' => [1, 5, 10],
    ],

    /*
    | Perkongsian.
    */
    'sharing' => [
        'default_privacy' => env('SCREEN_RECORDER_PRIVACY', 'private'),
        'allow_public_links' => (bool) env('SCREEN_RECORDER_ALLOW_PUBLIC', true),
        'allow_comments' => (bool) env('SCREEN_RECORDER_ALLOW_COMMENTS', true),
        'allow_download' => (bool) env('SCREEN_RECORDER_ALLOW_DOWNLOAD', true),
        'token_length' => (int) env('SCREEN_RECORDER_TOKEN_LEN', 32),
        'embed_enabled' => (bool) env('SCREEN_RECORDER_EMBED', true),
    ],

    /*
    | Integrasi dengan modul MediaEditor.
    */
    'media_editor' => [
        'enabled' => (bool) env('SCREEN_RECORDER_MEDIA_EDITOR', true),
        'auto_create_project' => (bool) env('SCREEN_RECORDER_AUTO_PROJECT', true),
        'project_kind' => env('SCREEN_RECORDER_PROJECT_KIND', 'screen-recording'),
    ],

    /*
    | Notifikasi.
    */
    'notifications' => [
        'notify_on_view' => (bool) env('SCREEN_RECORDER_NOTIFY_VIEW', false),
        'notify_on_comment' => (bool) env('SCREEN_RECORDER_NOTIFY_COMMENT', true),
    ],

];
