<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unified Order Management (BAB 86 / Idea #19)
    |--------------------------------------------------------------------------
    |
    | Tetapan pesanan unified — satu tempat untuk semua jualan tenant.
    |
    */

    // Channels (saluran jualan) yang disokong secara outset
    'default_channels' => [
        'shopee', 'lazada', 'tiktok', 'amazon', 'pos', 'site', 'booking', 'cod',
    ],

    'stock' => [
        // Rizab stokreleased selepas berapa hari jika pesanan tidak diteruskan
        'reservation_ttl_days' => (int) env('ORDERS_RESERVATION_TTL_DAYS', 7),
        // Adakah rizab stok automatik bila pesanan masuk
        'auto_reserve' => (bool) env('ORDERS_AUTO_RESERVE', true),
        // Tolak baki stok ke platform selepas pesanan dibuat
        'push_stock_to_platform' => (bool) env('ORDERS_PUSH_STOCK', false),
        // Koli sec Synchronization platform
        'sync_interval_minutes' => (int) env('ORDERS_SYNC_INTERVAL', 15),
    ],

    'summary' => [
        // Tempoh lalai untuk ringkasan jualan
        'default_period_days' => (int) env('ORDERS_SUMMARY_DAYS', 30),
        // Ambang "perlu tindakan" (pesanan lama tanpa proses)
        'stale_after_hours' => (int) env('ORDERS_STALE_HOURS', 24),
    ],

    'reminders' => [
        // Skedul automatic untuk pesanan perimeter
        'send_payment_reminder_after_hours' => (int) env('ORDERS_PAYMENT_REMINDER_HOURS', 24),
        'send_followup_after_hours' => (int) env('ORDERS_FOLLOWUP_HOURS', 48),
        'escalate_after_hours' => (int) env('ORDERS_ESCALATE_HOURS', 72),
    ],
];
