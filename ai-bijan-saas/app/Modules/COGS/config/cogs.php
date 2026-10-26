<?php

// B-COGS Engine — Cost of Goods Sold Calculation & Margin Analysis
return [
    // Cost calculation settings
    'cost_calculation' => [
        'default_method' => 'weighted_average', // fifo, lifo, weighted_average, specific_id
        'include_shipping' => true,
        'include_taxes' => false,
        'rounding_decimals' => 2,
    ],

    // Direct cost components
    'direct_costs' => [
        'raw_materials' => true,
        'direct_labor' => true,
        'manufacturing_overhead' => true,
        'packaging' => true,
        'shipping_in' => true,
        'duties_taxes' => false,
    ],

    // Overhead allocation
    'overhead_allocation' => [
        'method' => 'production_hours', // production_hours, direct_labor_cost, material_cost, revenue
        'period' => 'monthly',
        'include_fixed' => true,
        'include_variable' => true,
    ],

    // Margin analysis
    'margin_analysis' => [
        'min_gross_margin' => 20.0, // Minimum acceptable gross margin %
        'target_gross_margin' => 35.0, // Target gross margin %
        'alert_threshold' => 10.0, // Alert when margin drops below target - threshold
        'currency' => 'MYR',
    ],

    // Product costing
    'product_costing' => [
        'auto_calculate' => true,
        'recalc_on_purchase' => true,
        'recalc_on_production' => true,
        'history_retention_days' => 3650, // 10 years
    ],

    // Reporting
    'reporting' => [
        'cogs_report_frequency' => 'monthly',
        'margin_report_frequency' => 'monthly',
        'profitability_by_product' => true,
        'profitability_by_category' => true,
        'break_even_analysis' => true,
    ],

    // Integration
    'integrations' => [
        'finance_module' => true, // Post COGS entries to finance
        'inventory_module' => true, // Use inventory valuation
        'manufacturing_module' => true, // Use BOM costs
        'crm_module' => true, // Calculate deal profitability
    ],
];
