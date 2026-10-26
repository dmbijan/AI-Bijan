<?php

return [
    /*
    |--------------------------------------------------------------------------
    | COD Engine (BAB 84 / Idea #6)
    |--------------------------------------------------------------------------
    |
    | Cash on Delivery untuk PKS Malaysia — kaedah pembayaran dominan
    | di Malaysia yang TIDAK ada pesaing MY.
    |
    */

    'surcharge_default' => (float) env('COD_SURCHARGE_DEFAULT', 0),
    'min_order_default' => (float) env('COD_MIN_ORDER_DEFAULT', 0),
    'max_order_default' => (float) env('COD_MAX_ORDER_DEFAULT', 3000),

    'couriers' => [
        'Pos Laju', 'J&T Express', 'Ninja Van', 'Shopee Express',
        'Lalamove', 'GoSend', 'Flash', 'Kerry Express',
    ],

    //-states yang COD biasanya TIDAK disokong (kampung, pulau luar)
    'excluded_states_default' => [
        'Labuan', 'Langkawi', 'Tioman',
    ],

    'confirmation' => [
        //_channels untuk pengesahan COD sebelum hantar
        'channels' => ['whatsapp', 'sms', 'call'],
        'hours_before_dispatch' => (int) env('COD_CONFIRM_HOURS', 4),
    ],

    'analytics' => [
        // Amaran bila kadar kegagalan melebihi peratus ini
        'failure_rate_alert' => (float) env('COD_FAILURE_ALERT', 15.0),
    ],
];
