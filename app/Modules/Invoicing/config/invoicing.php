<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'default' => env('INVOICING_CURRENCY', 'MYR'),

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration (SST - Sales & Service Tax)
    |--------------------------------------------------------------------------
    | Malaysia SST rates: 6% or 8% (sales tax), 10% (service tax)
    | @see https://hasil.gov.my
    */
    'tax' => [
        'sst_rate' => env('INVOICING_SST_RATE', 0), // Default 0% (can be 6, 8, or 10)
        'enable_sst' => env('INVOICING_ENABLE_SST', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | e-Invois LHDN Configuration
    |--------------------------------------------------------------------------
    | @see https://myinvois.hasil.gov.my/
    */
    'einvois' => [
        'enabled' => env('INVOICING_EINVOIS_ENABLED', false),
        'api_base_url' => env('INVOICING_EINVOIS_API_URL', 'https://sandbox-api.myinvois.hasil.gov.my/v1'),
        'api_key' => env('INVOICING_EINVOIS_API_KEY'),
        'api_secret' => env('INVOICING_EINVOIS_API_SECRET'),
        'environment' => env('INVOICING_EINVOIS_ENV', 'sandbox'), // sandbox | production
        
        // Seller organization details (tenant registration info)
        'seller_registration_no' => env('INVOICING_SELLER_REG_NO'),
        'seller_ein' => env('INVOICING_SELLER_EIN'),
        'seller_address' => env('INVOICING_SELLER_ADDRESS'),
        'seller_postal_code' => env('INVOICING_SELLER_POSTAL_CODE'),
        'seller_email' => env('INVOICING_SELLER_EMAIL'),
        'seller_phone' => env('INVOICING_SELLER_PHONE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Numbering
    |--------------------------------------------------------------------------
    */
    'numbering' => [
        'quotation_prefix' => 'QT',
        'invoice_prefix' => 'INV',
        'credit_note_prefix' => 'CN',
        'debit_note_prefix' => 'DN',
        'receipt_prefix' => 'RCPT',
        'reset_frequency' => 'yearly', // yearly | monthly | never
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'payment_terms_days' => env('INVOICING_PAYMENT_TERMS', 30),
        'quotation_validity_days' => env('INVOICING_QUOTATION_VALIDITY', 30),
        'auto_overdue_notification' => env('INVOICING_AUTO_OVERDUE', false),
        'overdue_notification_days' => env('INVOICING_OVERDUE_DAYS', 1),
    ],

];
