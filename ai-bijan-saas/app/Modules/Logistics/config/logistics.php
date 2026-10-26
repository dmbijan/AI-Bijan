<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Logistics Configuration
    |--------------------------------------------------------------------------
    |
    | Penghantaran, kurier, tracking, consignment note dan status penghantaran.
    | Kos freight-in berbanding kos penghantaran keluar.
    |
    */

    // Warehouse Settings
    'warehouse' => [
        'enable_multi_warehouse' => true,
        'default_warehouse_id' => null,
        'max_locations_per_warehouse' => 1000,
        'enable_bin_tracking' => true,
        'auto_create_default_warehouse' => true,
    ],

    // Stock Move Settings
    'stock_move' => [
        'enable_lot_tracking' => true,
        'enable_serial_tracking' => false,
        'enable_expiry_tracking' => false,
        'auto_reserve_on_order' => true,
        'reservation_expiration_hours' => 48,
        'allow_negative_stock' => false,
        'require_approval_above_value' => 10000, // MYR
    ],

    // Shipment Settings
    'shipment' => [
        'enable_courier_integration' => true,
        'default_courier' => env('DEFAULT_COURIER', 'dhl'),
        'supported_couriers' => ['dhl', 'ups', 'fedex', 'jandeln', 'gdex', 'poslaju', 'ninja'],
        'auto_create_label_on_payment' => true,
        'tracking_prefix' => 'BIJAN',
        'max_weight_kg' => 50,
        'default_package_type' => 'parcel',
        'package_types' => [
            'parcel' => 'Parcel/Box',
            'envelope' => 'Envelope/Letter',
            'pallet' => 'Pallet',
            'custom' => 'Custom Size',
        ],
    ],

    // Courier API Integration
    'courier' => [
        'dhl' => [
            'api_url' => env('DHL_API_URL', 'https://api.dhlworldmail.com'),
            'account_number' => env('DHL_ACCOUNT_NUMBER'),
            'api_key' => env('DHL_API_KEY'),
            'is_test_mode' => true,
        ],
        'jandeln' => [
            'api_url' => env('JANDELN_API_URL'),
            'account_number' => env('JANDELN_ACCOUNT_NUMBER'),
            'api_key' => env('JANDELN_API_KEY'),
            'is_test_mode' => true,
        ],
    ],

    // Shipping Cost Policy
    'shipping_cost' => [
        'allocation_method' => 'weight', // weight, volume, value, equal
        'include_freight_in' => true, // Include inbound freight cost
        'freight_in_allocation' => 'cost_of_goods', // Allocate freight-in to COGS
    ],

    // Notification Settings
    'notifications' => [
        'notify_on_low_stock' => true,
        'low_stock_threshold' => 10,
        'notify_on_shipment_update' => true,
        'notify_on_delivery' => true,
    ],
];
