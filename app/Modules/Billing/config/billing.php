<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Harga & pelan langganan
    |--------------------------------------------------------------------------
    |
    | ⚠️ MODEL BERBEZA (dibetulkan 27 Okt 2026):
    | Langganan RM388 TIDAK termasuk token AI. Token ialah PAKEJ BERBAYAR yang
    | WAJIB dipilih semasa langganan — ia HASIL TAMBAHAN dari pelanggan yang
    | sama, bukan kos.
    |
    |   Hasil per pelanggan = langganan + token yang mereka beli
    |   Komisen reseller   = RM100 tetap (tidak berkait dengan pembelian token)
    |
    | `platform_share` ialah bahagian platform DARI LANGGANAN SAHAJA.
    | Token dijual pada harga berasingan dan marginnya bergantung pada kadar
    | pembekal AI sebenar — JANGAN andaikan margin token sebelum kos pembekal
    | disahkan (rujuk dashboard kos AI per pelanggan).
    |
    */

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 88,
            'platform_share' => 88,
            'token_wajib' => true,
        ],
        'growth' => [
            'name' => 'Growth',
            // Harga jualan melalui reseller — RM388 sebulan
            'price' => 388,
            'platform_share' => 288,
            'token_wajib' => true,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 1888,
            'platform_share' => 1888,
            'token_wajib' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pakej token AI — WAJIB semasa langganan
    |--------------------------------------------------------------------------
    |
    | Semua pakej ialah bayaran BULANAN yang ditambah pada langganan.
    | Tiada pakej percuma — token mesti dibeli supaya AI boleh digunakan.
    |
    | ⚠️ HARGA DISAHKAN 27 Okt 2026 daripada kontrak pembekal sebenar.
    | Margin jual 90.9% seragam pada semua pakej (lihat `kos_pembekal`).
    |
    | PAKEJ    TOKEN JUAL   TOKEN PENGGUNAAN   JUAL   BELI   UNTUNG  MARGIN
    | ─────────────────────────────────────────────────────────────────────
    | t60m     60 juta     had 80 juta        RM55    RM5     RM50    90.9%
    | t130m    130 juta    had 140 juta       RM88    RM8     RM80    90.9%
    | t180m    180 juta    had 200 juta       RM110   RM10    RM100   90.9%
    | t1b      1 bilion    had 1.1 bilion     RM550   RM50    RM500   90.9%
    | t2b      2 bilion    had 2.25 bilion    RM1100  RM100   RM1000  90.9%
    |
    | ⚠️ `tokens` = jumlah yang DIBERI kepada pelanggan.
    |    `kos_pembekal` = harga kita beli dari pembekal (tidak didedahkan).
    |    `harga_beli_tokens` = saiz pakej pembekal yang digunakan untuk membeli.
    |
    | ⚠️ PENTING: pakej pelanggan (cth 60 juta) mesti dibeli dalam pakej
    | pembekal yang CUKUP BESAR (80 juta). Baki 20 juta ialah buffer —
    | jika pelanggan guna lebih, kita masih ada token.
    |
    | Semua pakej sah 30 hari (sama seperti pembekal).
    |
    */

    'token_packages' => [
        't60m' => [
            'name' => '60 Juta token',
            'tokens' => 60_000_000,
            'price' => (float) env('TOKEN_PACKAGE_60M', 55),
            'kos_pembekal' => (float) env('TOKEN_COST_60M', 5),
            'harga_beli_tokens' => 80_000_000,
            'validity_days' => 30,
        ],
        't130m' => [
            'name' => '130 Juta token',
            'tokens' => 130_000_000,
            'price' => (float) env('TOKEN_PACKAGE_130M', 88),
            'kos_pembekal' => (float) env('TOKEN_COST_130M', 8),
            'harga_beli_tokens' => 140_000_000,
            'validity_days' => 30,
        ],
        't180m' => [
            'name' => '180 Juta token',
            'tokens' => 180_000_000,
            'price' => (float) env('TOKEN_PACKAGE_180M', 110),
            'kos_pembekal' => (float) env('TOKEN_COST_180M', 10),
            'harga_beli_tokens' => 200_000_000,
            'validity_days' => 30,
        ],
        't1b' => [
            'name' => '1 Bilion token',
            'tokens' => 1_000_000_000,
            'price' => (float) env('TOKEN_PACKAGE_1B', 550),
            'kos_pembekal' => (float) env('TOKEN_COST_1B', 50),
            'harga_beli_tokens' => 1_100_000_000,
            'validity_days' => 30,
        ],
        't2b' => [
            'name' => '2 Bilion token',
            'tokens' => 2_000_000_000,
            'price' => (float) env('TOKEN_PACKAGE_2B', 1100),
            'kos_pembekal' => (float) env('TOKEN_COST_2B', 100),
            'harga_beli_tokens' => 2_250_000_000,
            'validity_days' => 30,
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
    |
    | ⚠️ Komisen TIDAK berkait dengan pembelian token AI. Reseller dapat RM100
    | tetap walau berapa token pelanggan beli — jadi jangan tambah komisen
    | token tanpa sengaja.
    |
    | ⚠️ MARGIN: kos platform BELUM DIKIRA. Jangan dakwa margin sebelum
    | dashboard kos AI per pelanggan dibina dan kos pembekal disahkan.
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
