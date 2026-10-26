<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Affiliate Program Configuration
    |--------------------------------------------------------------------------
    */

    // Enable/disable affiliate program
    'enabled' => env('AFFILIATE_ENABLED', true),

    // Commission settings
    'commission' => [
        // Default commission type: percentage, fixed, tiered
        'default_type' => 'percentage',
        
        // Default commission rate (for percentage: 10 = 10%, for fixed: 10 = RM10)
        'default_rate' => 10,
        
        // Minimum commission amount before payout
        'min_payout_amount' => 50.00,
        
        // Payout currency
        'currency' => 'MYR',
        
        // Payout methods: bank_transfer, paypal, stripe, wallet
        'payout_methods' => ['bank_transfer', 'wallet'],
        
        // Auto-approve commissions
        'auto_approve' => false,
        
        // Commission hold period (days) before available for payout
        'hold_period_days' => 30,
        
        // Commission expiration (days after which unapproved commissions expire)
        'expiration_days' => 365,
    ],

    // Affiliate tiers
    'tiers' => [
        'bronze' => [
            'name' => 'Bronze Affiliate',
            'min_sales' => 0,
            'commission_multiplier' => 1.0,
        ],
        'silver' => [
            'name' => 'Silver Affiliate',
            'min_sales' => 50,
            'commission_multiplier' => 1.2,
        ],
        'gold' => [
            'name' => 'Gold Affiliate',
            'min_sales' => 200,
            'commission_multiplier' => 1.5,
        ],
        'platinum' => [
            'name' => 'Platinum Affiliate',
            'min_sales' => 1000,
            'commission_multiplier' => 2.0,
        ],
    ],

    // Referral settings
    'referral' => [
        // Cookie duration (days)
        'cookie_duration' => 90,
        
        // Max referrals per affiliate
        'max_referrals' => 0, // 0 = unlimited
        
        // Referral bonus (one-time reward for new affiliate signup)
        'signup_bonus' => 0,
        
        // First purchase bonus
        'first_purchase_bonus' => 0,
    ],

    // Tracking settings
    'tracking' => [
        // Enable link tracking
        'link_tracking' => true,
        
        // Enable click tracking
        'click_tracking' => true,
        
        // Enable conversion tracking
        'conversion_tracking' => true,
        
        // Attribution model: last_click, first_click, linear, time_decay
        'attribution_model' => 'last_click',
    ],

    // Payment settings
    'payment' => [
        // Payment schedule: manual, weekly, monthly, quarterly
        'schedule' => 'monthly',
        
        // Payment due day of month
        'due_day' => 1,
        
        // Invoice template
        'invoice_template' => 'affiliate_commission',
        
        // Tax handling: included, excluded, separate
        'tax_handling' => 'excluded',
        
        // Tax rate (percentage)
        'tax_rate' => 0,
    ],

    // Notification settings
    'notifications' => [
        // Notify affiliate on signup
        'notify_on_signup' => true,
        
        // Notify affiliate on first sale
        'notify_on_first_sale' => true,
        
        // Notify affiliate on commission earned
        'notify_on_commission' => true,
        
        // Notify affiliate on payout
        'notify_on_payout' => true,
        
        // Email template prefix
        'email_prefix' => 'affiliate',
    ],

    // Integration settings
    'integrations' => [
        // Integrate with marketplace orders
        'marketplace_integration' => true,
        
        // Integrate with POS sales
        'pos_integration' => false,
        
        // Integrate with invoicing
        'invoicing_integration' => true,
    ],
];
