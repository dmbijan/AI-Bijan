<?php

/**
 * Konfigurasi Keselamatan AI BIJAN (BAB 90).
 *
 * AUDIT BAB 90 (S1-S12) — jurang keselamatan yang ditemui semasa audit
 * repositori. Nilai di sini ialah tetapan lalai yang selamat.
 */
return [
    /*
    |----------------------------------------------------------------------
    | Rate limiting
    |----------------------------------------------------------------------
    */
    'rate_limits' => [
        // Had global API (per tenant + user + IP).
        // 600/minit — cukup untuk aplikasi biasa, terlalu rendah untuk
        // beban kerja legitimate (cth. bulk import / jana dokumen).
        'api' => [
            'max_attempts' => (int) env('SECURITY_API_RATE_LIMIT', 600),
            'decay_seconds' => (int) env('SECURITY_API_RATE_DECAY', 60),
        ],

        // Had ketat untuk laluan sensitif
        'strict' => [
            'max_attempts' => (int) env('SECURITY_STRICT_RATE_LIMIT', 20),
            'decay_seconds' => (int) env('SECURITY_STRICT_RATE_DECAY', 60),
        ],

        // Login (sedia ada di AuthController, ditunjukkan di sini sebagai rujukan)
        'login' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
        ],

        // Laluan dikecualikan daripada throttle (health check, dll)
        'exempt_paths' => [
            'up',
            'api/v1/health/*',
            'api/v1/webhooks/*',
            'sanctum/csrf-cookie',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Content-Security-Policy
    |----------------------------------------------------------------------
    */
    'csp' => [
        // ⚠️ Nonce dan 'unsafe-inline' adalah SALING EKSKLUSIF dalam CSP:
        // jika nonce hadir, 'unsafe-inline' diabaikan. Livewire menyuntik
        // <script> inline TANPA nonce → mesti guna 'unsafe-inline' sahaja.
        // Set true HANYA jika semua skrip inline anda menggunakan nonce.
        'use_nonce' => (bool) env('SECURITY_CSP_USE_NONCE', false),

        // ⚠️ Alpine.js (Livewire/Filament) memerlukan 'unsafe-eval' untuk
        // menilai ekspresi x-* dan wire:*. Tanpa ini, SEMUA interaksi
        // Livewire rosak (butang tidak berfungsi, borang tidak submit).
        'allow_unsafe_eval' => (bool) env('SECURITY_CSP_UNSAFE_EVAL', true),

        // ⚠️ Livewire menyuntik <script> inline (data-csrf, wire state).
        'allow_unsafe_inline_scripts' => (bool) env('SECURITY_CSP_UNSAFE_INLINE', true),

        // Tambahan untuk 'script-src'
        'script_extra' => '',

        // Tambahan untuk 'style-src' — Filament guna Bunny Fonts
        'style_extra' => 'https://fonts.bunny.net',

        // Tambahan untuk 'font-src' — fail fon sebenar
        'font_extra' => 'https://fonts.bunny.net',

        // Tambahan untuk 'img-src'
        'img_extra' => '',

        // Tambahan untuk 'connect-src'
        'connect_extra' => '',
    ],

    /*
    |----------------------------------------------------------------------
    | Header tambahan
    |----------------------------------------------------------------------
    */
    'headers' => [
        'hsts_max_age' => 31536000,
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'camera=(), microphone=(), geolocation=(self), payment=(self), usb=()',
    ],

    /*
    |----------------------------------------------------------------------
    | Idempotency
    |----------------------------------------------------------------------
    */
    'idempotency' => [
        'enabled' => (bool) env('SECURITY_IDEMPOTENCY_ENABLED', true),
        'header' => 'Idempotency-Key',
        'max_key_length' => 100,
    ],

    /*
    |----------------------------------------------------------------------
    | Kata laluan & akaun
    |----------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 12,
        'require_mixed_case' => true,
        'require_number' => true,
        'require_symbol' => true,
        'expiry_days' => 0,          // 0 = tidak expire
        'history_length' => 5,       // jangan guna 5 kata laluan terakhir
    ],

    /*
    |----------------------------------------------------------------------
    | Audit
    |----------------------------------------------------------------------
    */
    'audit' => [
        // Tahan retensi audit log (hari)
        'retention_days' => (int) env('SECURITY_AUDIT_RETENTION_DAYS', 2555), // 7 tahun
        'log_reads' => false,
        'log_privileged' => true,
    ],

    /*
    |----------------------------------------------------------------------
    | Backup & pemulihan
    |----------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => (bool) env('SECURITY_BACKUP_ENABLED', false),
        'path' => env('SECURITY_BACKUP_PATH', base_path('storage/app/private/backups')),
        'retention_days' => (int) env('SECURITY_BACKUP_RETENTION_DAYS', 30),
        'rpo_minutes' => (int) env('SECURITY_BACKUP_RPO_MINUTES', 60),
        'rto_minutes' => (int) env('SECURITY_BACKUP_RTO_MINUTES', 240),
        'encrypt' => true,
    ],
];
