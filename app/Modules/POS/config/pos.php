<?php

return [
    // Payment methods
    'payment_methods' => [
        'cash' => 'Tunai',
        'card' => 'Kad (Debit/Credit)',
        'ewallet' => 'E-Wallet (Touch \'n Go, GrabPay, etc)',
        'credit' => 'Akaun Kredit',
        'voucher' => 'Voucher/Gift Card',
        'duitnow' => 'DuitNow QR',
        'online' => 'Bayaran Online (CHIP)',
        'split' => 'Pembayaran Berpisah',
    ],

    // Sale statuses
    'sale_statuses' => [
        'draft' => 'Rangka',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'refunded' => 'Dipulangkan',
        'partially_refunded' => 'Sebahagian Dipulangkan',
    ],

    // Shift statuses
    'shift_statuses' => [
        'open' => 'Dibuka',
        'closed' => 'Ditutup',
        'locked' => 'Dikunci',
    ],

    // Receipt settings
    'receipt' => [
        'footer_text' => 'Terima kasih atas kunjungan anda!',
        'print_copy_count' => 1,
        'show_tax_breakdown' => true,
        'show_payment_details' => true,
    ],

    // Cash shift settings
    'cash_shift' => [
        'float_cash' => 200.00, // Initial float/cash tabung
        'max_discrepancy' => 10.00, // Max allowed cash discrepancy
    ],

    // Tax settings
    'tax' => [
        'default_rate' => 0.06, // 6% SST (old) or new rate
        'enabled' => true,
        'inclusive' => false,
    ],

    // BAB 88 / T-78 — Service charge (caj perkhidmatan Malaysia)
    // Dipaparkan BERASINGAN daripada cukai; biasanya pesanan makan di tempat sahaja.
    'service_charge' => [
        'rate' => 10.0,               // % (0 = mati)
        'scope' => 'dine_in',         // dine_in | all
    ],

    // BAB 88 / T-82 — TNG merchant soundbox
    'tng_soundbox' => [
        'enabled' => false,
        'match_window_hours' => 12,   // padan dengan jualan TnG dalam tempoh ini
    ],

    // BAB 88 / T-83 — Kelulusan diskaun
    'discount_approval' => [
        'requires_approval_above' => 20.0,  // % — melebihi ini perlu kelulusan pengurus
        'max_percent' => 50.0,              // had mutlak
    ],

    // BAB 88 / T-76 — Paparan dapur
    'kitchen_display' => [
        'enabled' => false,          // aktifkan per tenant bila ada dapur
        'default_prep_minutes' => 15,
    ],

    // Discount settings
    'discount' => [
        'max_percentage' => 50,
        'requires_approval_above' => 20, // Requires manager approval above 20%
    ],
];
