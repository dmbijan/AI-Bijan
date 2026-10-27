<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Harga & pelan langganan
    |--------------------------------------------------------------------------
    |
    | ⚠️ Ini HARGA JUAL. `platform_share` ialah bahagian platform.
    | Komisen reseller dikira dari `price` (jumlah pelanggan bayar), bukan
    | dari `platform_share`.
    |
    */

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 88,
            'platform_share' => 88,
        ],
        'growth' => [
            'name' => 'Growth',
            // Harga jualan melalui reseller — RM388 sebulan
            'price' => 388,
            'platform_share' => 288,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 1888,
            'platform_share' => 1888,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CHIP Collect
    |--------------------------------------------------------------------------
    |
    | ⚠️ Kunci API HANYA di pelayan. Jangan dedahkan ke frontend/log.
    | `webhook_public_key` ialah kunci awam CHIP (PEM) untuk mengesahkan
    | tandatangan RSA-SHA256. Tanpanya SEMUA webhook ditolak.
    |
    */

    'chip' => [
        'base_url' => env('CHIP_BASE_URL', 'https://gate.chip-in.asia/api/v1'),
        'api_key' => env('CHIP_API_KEY'),
        'brand_id' => env('CHIP_BRAND_ID'),
        'webhook_public_key' => env('CHIP_WEBHOOK_PUBLIC_KEY'),
        'webhook_tolerance_seconds' => (int) env('CHIP_WEBHOOK_TOLERANCE', 300),
        'timeout' => (int) env('CHIP_TIMEOUT', 30),
        'live' => (bool) env('CHIP_LIVE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Komisen reseller
    |--------------------------------------------------------------------------
    |
    | Model: RM388/bulan → reseller RM100/bulan selagi langgan.
    | ⚠️ AMARAN MARGIN: kos sebenar RM75–230/pelanggan. Jika kos > RM180,
    | jangan lancar komisen tanpa had — turunkan nilai atau hadkan bulan.
    |
    */

    'reseller' => [
        'commission_value' => (float) env('RESELLER_COMMISSION', 100),
        'commission_months' => env('RESELLER_COMMISSION_MONTHS'),   // null = selagi langgan
        'commission_cap_pct' => (float) env('RESELLER_CAP_PCT', 50),
        'matang_hari' => (int) env('RESELLER_MATANG_HARI', 30),
        'min_payout' => (float) env('RESELLER_MIN_PAYOUT', 100),
        'cookie_days' => (int) env('RESELLER_COOKIE_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | URL redirect
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'success' => env('BILLING_SUCCESS_URL', '/admin/tetapan?bayaran=berjaya'),
        'failure' => env('BILLING_FAILURE_URL', '/admin/tetapan?bayaran=gagal'),
        'daftar' => env('BILLING_REGISTER_URL', '/daftar'),
        'tidak_sah' => env('BILLING_INVALID_REF_URL', '/'),
    ],

];
