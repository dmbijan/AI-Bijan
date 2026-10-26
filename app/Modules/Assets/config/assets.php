<?php

return [
    // Asset categories
    'categories' => [
        'equipment',
        'vehicles',
        'furniture',
        'electronics',
        'buildings',
        'land',
        'machinery',
        'software',
        'intangible',
    ],

    // Depreciation methods
    'depreciation_methods' => [
        'straight_line' => 'Straight Line',
        'declining_balance' => 'Declining Balance',
        'units_of_production' => 'Units of Production',
        'sum_years_digits' => 'Sum of Years Digits',
    ],

    // Default depreciation
    'default_depreciation' => [
        'method' => 'straight_line',
        'useful_life_years' => 10,
        'salvage_value_percentage' => 10,
    ],

    // Maintenance settings
    'maintenance' => [
        'types' => ['preventive', 'corrective', 'predictive', 'emergency'],
        'priorities' => ['low', 'medium', 'high', 'critical'],
        'statuses' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
    ],

    // Asset lifecycle
    'lifecycle_statuses' => [
        'purchased',
        'installed',
        'active',
        'maintenance',
        'stored',
        'disposed',
        'lost',
        'damaged',
    ],

    // Approval workflow
    'approval_required' => true,
    'disposal_approval_required' => true,
];
