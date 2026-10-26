<?php

/**
 * Konfigurasi B-Expense (BAB 89 P1).
 *
 * ⚠️ Modul ini TIDAH memproses wang tunai — pembayaran kekal Finance/Invoicing.
 */
return [
    'enabled' => env('EXPENSE_ENABLED', true),

    /*
    |----------------------------------------------------------------------
    | Had keselamatan
    |----------------------------------------------------------------------
    */
    'limits' => [
        'max_items_per_expense' => 200,
        'max_receipts_per_expense' => 20,
        'approval_threshold' => 200.00,   // RM200 — melebihi perlu kelulusan
        'receipt_threshold' => 50.00,     // RM50 — resit wajib
    ],

    /*
    |----------------------------------------------------------------------
    | Mileage & per diem
    |----------------------------------------------------------------------
    */
    'mileage' => [
        'default_rate' => 0.60,           // Kadar LHDN RM0.60/km
        'enabled' => true,
    ],

    'per_diem' => [
        'default_rate' => 50.00,          // RM50/hari (default)
        'enabled' => true,
        'rates_by_location' => [
            'kuala_lumpur' => 50.00,
            'kuching' => 60.00,
            'kotakinabalu' => 70.00,
            'johor_bahru' => 50.00,
            'penang' => 55.00,
            'langkawi' => 65.00,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Kategori lalai — dijana sekali (idempoten) bila modul diaktifkan
    |----------------------------------------------------------------------
    */
    'default_categories' => [
        [
            'code' => 'TRAVEL',
            'name' => 'Travel & Transport',
            'name_bm' => 'Perjalanan & Pengangkutan',
            'sort_order' => 10,
            'merchant_keywords' => ['petronas', 'shell', 'petrol', 'toll', 'grab', 'carpark', 'touch n go', 'tng', 'rapid kl', 'airasia'],
        ],
        [
            'code' => 'MEALS',
            'name' => 'Meals & Entertainment',
            'name_bm' => 'Makan & Hiburan',
            'sort_order' => 20,
            'merchant_keywords' => ['mcdonald', 'kfc', 'starbucks', 'restoran', 'cafe', 'old town', 'burger', 'nasi', 'cafe'],
        ],
        [
            'code' => 'OFFICE',
            'name' => 'Office Supplies',
            'name_bm' => 'Bekalan Pejabat',
            'sort_order' => 30,
            'merchant_keywords' => ['stationery', 'pensonic', 'sharp', 'office', 'paper', 'printer', 'fuji'],
        ],
        [
            'code' => 'SOFTWARE',
            'name' => 'Software & Subscriptions',
            'name_bm' => 'Perisian & Langganan',
            'sort_order' => 40,
            'merchant_keywords' => ['aws', 'google', 'microsoft', 'adobe', 'hosting', 'domain', 'namecheap', 'cloudflare', 'digital ocean'],
        ],
        [
            'code' => 'MARKETING',
            'name' => 'Marketing & Advertising',
            'name_bm' => 'Pemasaran & Pengiklanan',
            'sort_order' => 50,
            'is_billable' => true,
            'merchant_keywords' => ['facebook', 'instagram', 'tiktok', 'ads', 'meta', 'linkedin', 'google ads', 'canva', 'shapr'],
        ],
        [
            'code' => 'UTILITIES',
            'name' => 'Utilities & Telecommunications',
            'name_bm' => 'Utiliti & Telekomunikasi',
            'sort_order' => 60,
            'merchant_keywords' => ['tnb', 'celcom', 'maxis', 'digi', 'unifi', 'maxis', 'electric', 'water', 'tm'],
        ],
        [
            'code' => 'PROFESSIONAL',
            'name' => 'Professional Fees',
            'name_bm' => 'Yuran Profesional',
            'sort_order' => 70,
            'merchant_keywords' => ['audit', 'law firm', 'consultant', 'solicitor', 'notary', 'certified'],
        ],
        [
            'code' => 'RENT',
            'name' => 'Rent & Utilities',
            'name_bm' => 'Sewa & Guna Bangunan',
            'sort_order' => 80,
            'merchant_keywords' => ['rent', 'sewa', 'warehouse', 'office rental', 'storage'],
        ],
        [
            'code' => 'VEHICLE',
            'name' => 'Vehicle & Fuel',
            'name_bm' => 'Kenderaan & Bahan Bakar',
            'sort_order' => 90,
            'merchant_keywords' => ['petronas', 'shell', 'vehicle', 'tyre', 'tire', 'workshop', 'bengkel', 'car service'],
        ],
        [
            'code' => 'OTHER',
            'name' => 'Other Expenses',
            'name_bm' => 'Perbelanjaan Lain-lain',
            'sort_order' => 99,
            'requires_approval' => true,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Nota integrasi — guna semula domain sedia ada (BAB 87.8)
    |----------------------------------------------------------------------
    */
    'integration' => [
        'document_ai' => 'Guna App\Modules\DocumentAI untuk OCR & ekstrak resit.',
        'finance' => 'Guna App\Modules\Finance untuk jurnal & akaun COA.',
        'assets' => 'Guna App\Modules\Assets untuk storan fail & retensi.',
        'reminder' => 'Guna App\Modules\Reminder untuk amaran bayaran & bajet.',
        'workflow' => 'Guna App\Modules\WorkflowStudio untuk aliran kelulusan berperingkat.',
        'hr' => 'Guna App\Modules\HRMS untuk data staf & perbatuan.',
    ],
];
