<?php

return [
    // Product categories
    'product_categories' => [
        'electronics' => 'Elektronik',
        'fashion' => 'Fesyen',
        'home_living' => 'Rumah & Hidup',
        'health_beauty' => 'Kesihatan & Kecantikan',
        'food_beverage' => 'Makanan & Minuman',
        'sports' => 'Sukan & Hobi',
        'toys' => 'Mainan & Kanak-kanak',
        'automotive' => 'Automotif',
        'books' => 'Buku & Alat Tulis',
        'other' => 'Lain-lain',
    ],

    // Order statuses
    'order_statuses' => [
        'draft' => 'Rangka',
        'pending' => 'Menunggu',
        'confirmed' => 'Disahkan',
        'processing' => 'Diproses',
        'shipped' => 'Dihantar',
        'delivered' => 'Diterima',
        'cancelled' => 'Dibatalkan',
        'refunded' => 'Dipulangkan',
        'partially_refunded' => 'Sebahagian Dipulangkan',
    ],

    // Payment methods
    'payment_methods' => [
        'bank_transfer' => 'Pindahan Bank',
        'credit_card' => 'Kad Kredit/Debit',
        'ewallet' => "E-Wallet (Touch 'n Go, GrabPay)",
        'cod' => 'Bayaran Di Kaunter',
        'installment' => 'Caj Angsuran',
        'voucher' => 'Voucher/Gift Card',
    ],

    // Shipping methods
    'shipping_methods' => [
        'standard' => 'Penghantaran Standard (5-7 hari)',
        'express' => 'Penghantaran Ekspres (1-3 hari)',
        'same_day' => 'Penghantaran Hari Sama',
        'pickup' => 'Ambil Sendiri',
    ],

    // Commission rates
    'commission' => [
        'default_rate' => 0.05,      // 5% platform commission
        'min_rate' => 0.02,          // Minimum 2%
        'max_rate' => 0.15,          // Maximum 15%
        'vendor_tiers' => [
            ['min_sales' => 0, 'rate' => 0.05],
            ['min_sales' => 10000, 'rate' => 0.04],
            ['min_sales' => 50000, 'rate' => 0.03],
            ['min_sales' => 100000, 'rate' => 0.02],
        ],
    ],

    // Cart settings
    'cart' => [
        'max_items' => 100,
        'save_duration_days' => 30,
        'merge_guest_on_login' => true,
    ],

    // Review settings
    'reviews' => [
        'verified_only' => false,     // Allow non-verified reviews
        'moderation_required' => true, // Require approval before publishing
        'min_rating' => 1,
        'max_rating' => 5,
    ],

    // SEO settings
    'seo' => [
        'product_slug_prefix' => 'p',
        'category_slug_prefix' => 'c',
        'vendor_slug_prefix' => 'v',
    ],
];
