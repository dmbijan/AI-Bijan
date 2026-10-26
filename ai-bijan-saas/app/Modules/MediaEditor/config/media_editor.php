<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Editor (BAB 86 / Idea #27)
    |--------------------------------------------------------------------------
    |
    | Tenant edit gambar/video sendiri (corak Canva/CapCut).
    | Renderer sebenar (FFmpeg/Sharp/ImageMagick) dijalankan worker luar.
    |
    */

    'render' => [
        // Had saiz projek
        'max_layers' => (int) env('MEDIA_EDITOR_MAX_LAYERS', 50),
        'max_duration_seconds' => (int) env('MEDIA_EDITOR_MAX_DURATION', 300),
        // Format output
        'video_formats' => ['mp4', 'webm'],
        'image_formats' => ['png', 'jpg', 'gif'],
        // Kuota render harian per tenant
        'daily_render_limit' => (int) env('MEDIA_EDITOR_DAILY_RENDER', 50),
    ],

    'assets' => [
        // Sumber aset (stok)
        'stock_sources' => ['unsplash', 'pexels', 'pixabay'],
        'max_upload_kb' => (int) env('MEDIA_EDITOR_MAX_UPLOAD_KB', 20480),
        'allowed_image_types' => ['png', 'jpg', 'jpeg', 'webp', 'svg'],
        'allowed_video_types' => ['mp4', 'webm', 'mov'],
        'allowed_audio_types' => ['mp3', 'wav', 'aac'],
    ],

    'brand_kit' => [
        // Warna & fon jenama disimpan per tenant
        'default_colors' => ['#111111', '#ffffff', '#0066ff', '#00c853', '#ff1744'],
        'default_fonts' => ['Inter', 'Roboto', 'Poppins', 'Noto Sans'],
    ],

    'templates' => [
        // Muat naik template hanya untuk super admin
        'upload_requires_admin' => true,
    ],
];
