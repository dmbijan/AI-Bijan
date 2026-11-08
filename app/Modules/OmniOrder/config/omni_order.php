<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OmniOrder — pesanan omnichannel & perlindungan hasil
    |--------------------------------------------------------------------------
    |
    | Berasal daripada https://luxana.com.my/
    |
    | Satu baris gilir untuk semua saluran jualan, kelulusan pukal,
    | cetak AWB, push kurier dan 5 alat perlindungan hasil.
    */

    'enabled' => (bool) env('OMNI_ORDER_ENABLED', true),

    /*
    | Saluran yang disokong.
    */
    'channels' => [
        'tiktok_shop', 'shopee', 'woocommerce', 'shopify', 'lazada', 'fluent_forms', 'manual',
    ],

    /*
    | Had
    */
    'limits' => [
        'max_orders_per_day' => (int) env('OMNI_ORDER_MAX_DAILY', 3000),
        'bulk_approve_max' => (int) env('OMNI_ORDER_BULK_MAX', 500),
        'max_couriers' => (int) env('OMNI_ORDER_MAX_COURIERS', 30),
    ],

    /*
    | Alat perlindungan hasil.
    */
    'protection' => [
        'duplicate_window_hours' => (int) env('OMNI_ORDER_DUP_WINDOW', 24),
        'high_value_threshold_rm' => (float) env('OMNI_ORDER_HIGH_VALUE_RM', 500),
        'risk_high_threshold' => (int) env('OMNI_ORDER_RISK_THRESHOLD', 50),
        'rejected_lookback_days' => (int) env('OMNI_ORDER_RISK_LOOKBACK', 90),
    ],

    /*
    | COD tracker.
    */
    'cod' => [
        'default_payout_days' => (int) env('OMNI_ORDER_COD_PAYOUT_DAYS', 7),
        'alert_after_days_overdue' => (int) env('OMNI_ORDER_COD_ALERT_DAYS', 2),
    ],

    /*
    | Kurier lalai.
    */
    'couriers' => [
        'jnt' => ['name' => 'J&T Express', 'cod' => true],
        'ninjavan' => ['name' => 'Ninja Van', 'cod' => true],
        'poslaju' => ['name' => 'Pos Laju', 'cod' => true],
        'spx' => ['name' => 'SPX Express', 'cod' => true],
        'citylink' => ['name' => 'City-Link', 'cod' => true],
        'dhl' => ['name' => 'DHL eCommerce', 'cod' => false],
        'flash' => ['name' => 'Flash Express', 'cod' => true],
        'easyparcel' => ['name' => 'EasyParcel', 'cod' => false],
    ],

    /*
    | Sync.
    */
    'sync' => [
        'interval_minutes' => (int) env('OMNI_ORDER_SYNC_INTERVAL', 15),
        'auto_approve' => (bool) env('OMNI_ORDER_AUTO_APPROVE', false),
        'batch_size' => (int) env('OMNI_ORDER_SYNC_BATCH', 100),
    ],

];
