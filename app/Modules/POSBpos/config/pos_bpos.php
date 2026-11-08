<?php

return [

    /*
    |--------------------------------------------------------------------------
    | POSBpos — ciri POS lanjutan daripada B-POS
    |--------------------------------------------------------------------------
    |
    | Melengkapkan modul `POS` AI BIJAN dengan ciri daripada
    | https://github.com/dmbijan/bpos yang belum wujud:
    |
    |   - Kad hadiah (gift card)
    |   - Tempahan meja
    |   - Kehadiran & jadual syif staf
    |   - Food court + meja boleh dikongsi
    |   - Kaunter nombor giliran
    |   - Baris gilir sync offline
    |   - Kedai online
    |   - Bil cawangan + bayaran langganan
    |
    | ⚠️ Wang sentiasa integer SEN (bukan float) — elak ralat pembundaran.
    */

    'enabled' => (bool) env('POS_BPOS_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_gift_cards' => (int) env('POS_BPOS_MAX_GIFTCARDS', 5000),
        'max_reservations_per_day' => (int) env('POS_BPOS_MAX_RESERVATIONS', 200),
        'max_food_courts' => (int) env('POS_BPOS_MAX_FOODCOURTS', 20),
        'max_online_stores' => (int) env('POS_BPOS_MAX_STORES', 10),
        'max_sync_attempts' => (int) env('POS_BPOS_MAX_SYNC_ATTEMPTS', 5),
    ],

    /*
    | Kad hadiah
    */
    'gift_card' => [
        'default_validity_days' => (int) env('POS_BPOS_GC_DAYS', 365),
        'allow_partial_redeem' => (bool) env('POS_BPOS_GC_PARTIAL', true),
        'min_issue_sen' => (int) env('POS_BPOS_GC_MIN', 1000),
        'max_issue_sen' => (int) env('POS_BPOS_GC_MAX', 1000000),
        'code_prefix' => env('POS_BPOS_GC_PREFIX', 'GC'),
    ],

    /*
    | Tempahan meja
    */
    'reservation' => [
        'default_duration_minutes' => (int) env('POS_BPOS_RSV_MINUTES', 90),
        'max_party_size' => (int) env('POS_BPOS_RSV_MAX_PAX', 40),
        'min_notice_minutes' => (int) env('POS_BPOS_RSV_NOTICE', 60),
        'max_advance_days' => (int) env('POS_BPOS_RSV_ADVANCE', 30),
    ],

    /*
    | Kehadiran staf
    */
    'attendance' => [
        'late_threshold_minutes' => (int) env('POS_BPOS_LATE_MIN', 5),
        'require_register' => (bool) env('POS_BPOS_ATT_REQUIRE_REG', false),
        'auto_close_after_hours' => (int) env('POS_BPOS_ATT_AUTO_CLOSE', 16),
    ],

    /*
    | Food court & giliran
    */
    'food_court' => [
        'default_tables' => (int) env('POS_BPOS_FC_TABLES', 10),
        'default_seats' => (int) env('POS_BPOS_FC_SEATS', 4),
        'queue_prefix' => env('POS_BPOS_FC_QUEUE_PREFIX', 'A'),
        'max_queue_number' => (int) env('POS_BPOS_FC_MAX_QUEUE', 999),
    ],

    /*
    | Sync offline
    */
    'sync' => [
        'batch_size' => (int) env('POS_BPOS_SYNC_BATCH', 50),
        'retry_delay_seconds' => (int) env('POS_BPOS_SYNC_RETRY', 30),
        'max_attempts' => (int) env('POS_BPOS_SYNC_MAX', 5),
    ],

    /*
    | Bil cawangan & langganan
    */
    'billing' => [
        'currency' => env('POS_BPOS_CURRENCY', 'MYR'),
        'per_branch_sen' => (int) env('POS_BPOS_PER_BRANCH_SEN', 1800), // RM18
        'due_days' => (int) env('POS_BPOS_BILL_DUE_DAYS', 14),
        'gateway' => env('POS_BPOS_GATEWAY', 'chip'),
    ],

];
