<?php

// Loyalty Program Configuration
return [
    // Member configuration
    'member' => [
        'code_prefix' => 'LOY-MEM-',
        'auto_enroll_on_purchase' => true,
        'max_members_per_tenant' => null, // null = unlimited
    ],

    // Points configuration
    'points' => [
        'earning_rules' => [
            'purchase_multiplier' => 1.0,      // 1 point per RM1 spent
            'signup_bonus' => 100,
            'birthday_bonus' => 50,
            'referral_bonus' => 75,
            'review_bonus' => 25,
            'social_share_bonus' => 10,
        ],
        'burning_rules' => [
            'min_points_to_redeem' => 10,
            'max_points_per_transaction' => 5000,
            'point_value' => 0.01,             // 1 point = RM0.01
        ],
        'expiration' => [
            'ttl_months' => 24,               // Points expire after 24 months
            'grace_period_days' => 30,         // Grace period before expiration
            'notify_before_days' => 60,        // Notification before expiry
        ],
        'thresholds' => [
            'low_balance_alert' => 50,
            'high_balance_threshold' => 10000,
        ],
    ],

    // Reward configuration
    'reward' => [
        'types' => ['discount', 'free_shipping', 'product', 'cashback', 'experience'],
        'statuses' => ['draft', 'active', 'expired', 'inactive'],
        'code_prefix' => 'LOY-REW-',
        'max_redemptions_per_member' => null,
        'redemption_cooldown_days' => 7,     // Minimum days between redemptions
    ],

    // Tier configuration
    'tier' => [
        'default_tiers' => [
            ['name' => 'Bronze', 'color' => '#CD7F32', 'min_points' => 0, 'multiplier' => 1.0, 'benefits' => []],
            ['name' => 'Silver', 'color' => '#C0C0C0', 'min_points' => 1000, 'multiplier' => 1.2, 'benefits' => ['early_access', 'birthday_bonus']],
            ['name' => 'Gold', 'color' => '#FFD700', 'min_points' => 5000, 'multiplier' => 1.5, 'benefits' => ['early_access', 'birthday_bonus', 'free_shipping', 'priority_support']],
            ['name' => 'Platinum', 'color' => '#E5E4E2', 'min_points' => 20000, 'multiplier' => 2.0, 'benefits' => ['early_access', 'birthday_bonus', 'free_shipping', 'priority_support', 'exclusive_events', 'personal_shopper']],
            ['name' => 'Diamond', 'color' => '#B9F2FF', 'min_points' => 50000, 'multiplier' => 2.5, 'benefits' => ['early_access', 'birthday_bonus', 'free_shipping', 'priority_support', 'exclusive_events', 'personal_shopper', 'vip_gift']],
        ],
        'evaluation_frequency' => 'daily',   // daily, weekly, monthly
        'demotion_enabled' => true,
        'demotion_grace_period' => 3,        // Number of evaluation periods before demotion
    ],

    // Ledger configuration
    'ledger' => [
        'max_entries_per_day' => null,       // Rate limiting for ledger entries
        'retention_years' => 7,              // Keep ledger records for 7 years
        'audit_trail' => true,
    ],

    // Analytics configuration
    'analytics' => [
        'roi_tracking' => true,
        'member_segmentation' => true,
        'churn_prediction' => false,         // Future enhancement
        'ltv_calculation' => true,
    ],

    // Notifications
    'notifications' => [
        'points_earned' => true,
        'points_expiring' => true,
        'tier_upgraded' => true,
        'tier_demoted' => false,
        'reward_available' => true,
        'milestone_reached' => true,
        'channels' => ['email', 'sms', 'whatsapp', 'in_app'],
    ],
];
