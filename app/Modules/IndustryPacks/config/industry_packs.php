<?php

// Industry Packs — Configuration profiles per industry
return [
    // Available industries
    'industries' => [
        'retail' => [
            'name' => 'Retail & E-commerce',
            'description' => 'Retail stores, online shops, marketplaces',
            'enabled_modules' => ['pos', 'marketplace', 'inventory', 'loyalty', 'affiliate', 'marketing'],
            'default_dashboard' => 'retail',
        ],
        'manufacturing' => [
            'name' => 'Manufacturing',
            'description' => 'Manufacturing & production facilities',
            'enabled_modules' => ['scm', 'manufacturing', 'inventory', 'projects', 'assets', 'cogs'],
            'default_dashboard' => 'manufacturing',
        ],
        'services' => [
            'name' => 'Services & Consulting',
            'description' => 'Professional services, consulting firms',
            'enabled_modules' => ['projects', 'hrms', 'timesheet', 'crm', 'virtual_office'],
            'default_dashboard' => 'services',
        ],
        'healthcare' => [
            'name' => 'Healthcare & Medical',
            'description' => 'Clinics, hospitals, medical practices',
            'enabled_modules' => ['booking', 'crm', 'inventory', 'finance', 'hrms'],
            'default_dashboard' => 'healthcare',
        ],
        'food_beverage' => [
            'name' => 'Food & Beverage',
            'description' => 'Restaurants, cafes, food service',
            'enabled_modules' => ['pos', 'inventory', 'cogs', 'loyalty', 'marketing'],
            'default_dashboard' => 'food_beverage',
        ],
        'construction' => [
            'name' => 'Construction & Building',
            'description' => 'Construction companies, contractors',
            'enabled_modules' => ['projects', 'assets', 'inventory', 'hrms', 'crm'],
            'default_dashboard' => 'construction',
        ],
        'logistics' => [
            'name' => 'Logistics & Transport',
            'description' => 'Shipping, freight, transportation',
            'enabled_modules' => ['logistics', 'fleet', 'inventory', 'crm', 'finance'],
            'default_dashboard' => 'logistics',
        ],
        'education' => [
            'name' => 'Education & Training',
            'description' => 'Schools, training centers, LMS',
            'enabled_modules' => ['booking', 'crm', 'finance', 'documents', 'site'],
            'default_dashboard' => 'education',
        ],
        'real_estate' => [
            'name' => 'Real Estate & Property',
            'description' => 'Property management, real estate agencies',
            'enabled_modules' => ['crm', 'booking', 'finance', 'documents', 'site'],
            'default_dashboard' => 'real_estate',
        ],
        'agriculture' => [
            'name' => 'Agriculture & Farming',
            'description' => 'Farms, agriculture, agribusiness',
            'enabled_modules' => ['inventory', 'projects', 'crm', 'logistics', 'finance'],
            'default_dashboard' => 'agriculture',
        ],
    ],

    // Dashboard layouts per industry
    'dashboards' => [
        'retail' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Today Sales', 'metric' => 'daily_sales'],
                ['type' => 'line_chart', 'title' => 'Sales Trend', 'metric' => 'sales_trend_7d'],
                ['type' => 'bar_chart', 'title' => 'Top Products', 'metric' => 'top_products'],
                ['type' => 'kpi_card', 'title' => 'Transactions', 'metric' => 'transaction_count'],
                ['type' => 'pie_chart', 'title' => 'Payment Methods', 'metric' => 'payment_breakdown'],
                ['type' => 'table', 'title' => 'Low Stock Items', 'metric' => 'low_stock_alerts'],
            ],
        ],
        'manufacturing' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Production Output', 'metric' => 'production_today'],
                ['type' => 'line_chart', 'title' => 'Production Efficiency', 'metric' => 'efficiency_trend'],
                ['type' => 'gauge', 'title' => 'Capacity Utilization', 'metric' => 'capacity_usage'],
                ['type' => 'bar_chart', 'title' => 'Work Orders Status', 'metric' => 'work_orders_status'],
                ['type' => 'kpi_card', 'title' => 'COGS %', 'metric' => 'cogs_percentage'],
                ['type' => 'table', 'title' => 'Quality Issues', 'metric' => 'quality_alerts'],
            ],
        ],
        'services' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Billable Hours', 'metric' => 'billable_hours'],
                ['type' => 'line_chart', 'title' => 'Revenue Trend', 'metric' => 'revenue_trend'],
                ['type' => 'pie_chart', 'title' => 'Project Distribution', 'metric' => 'projects_by_type'],
                ['type' => 'kpi_card', 'title' => 'Active Projects', 'metric' => 'active_projects'],
                ['type' => 'table', 'title' => 'Timesheet Pending', 'metric' => 'pending_timesheets'],
                ['type' => 'kpi_card', 'title' => 'Utilization Rate', 'metric' => 'utilization_rate'],
            ],
        ],
        'healthcare' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Appointments Today', 'metric' => 'appointments_today'],
                ['type' => 'line_chart', 'title' => 'Patient Visits', 'metric' => 'patient_visits_trend'],
                ['type' => 'kpi_card', 'title' => 'Revenue Today', 'metric' => 'daily_revenue'],
                ['type' => 'table', 'title' => 'Upcoming Appointments', 'metric' => 'upcoming_appointments'],
                ['type' => 'pie_chart', 'title' => 'Service Types', 'metric' => 'service_distribution'],
                ['type' => 'kpi_card', 'title' => 'No-show Rate', 'metric' => 'noshow_rate'],
            ],
        ],
        'food_beverage' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Today Revenue', 'metric' => 'daily_revenue'],
                ['type' => 'line_chart', 'title' => 'Hourly Sales', 'metric' => 'hourly_sales'],
                ['type' => 'bar_chart', 'title' => 'Menu Items Performance', 'metric' => 'menu_performance'],
                ['type' => 'kpi_card', 'title' => 'Table Turnover', 'metric' => 'table_turnover'],
                ['type' => 'kpi_card', 'title' => 'COGS %', 'metric' => 'food_cost_percentage'],
                ['type' => 'table', 'title' => 'Inventory Alerts', 'metric' => 'inventory_alerts'],
            ],
        ],
        'construction' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Active Sites', 'metric' => 'active_sites'],
                ['type' => 'line_chart', 'title' => 'Project Progress', 'metric' => 'progress_trend'],
                ['type' => 'bar_chart', 'title' => 'Budget vs Actual', 'metric' => 'budget_variance'],
                ['type' => 'kpi_card', 'title' => 'Equipment Usage', 'metric' => 'equipment_utilization'],
                ['type' => 'table', 'title' => 'Pending Approvals', 'metric' => 'pending_approvals'],
                ['type' => 'kpi_card', 'title' => 'Safety Incidents', 'metric' => 'safety_incidents'],
            ],
        ],
        'logistics' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Shipments Today', 'metric' => 'shipments_today'],
                ['type' => 'line_chart', 'title' => 'Delivery Performance', 'metric' => 'delivery_trend'],
                ['type' => 'table', 'title' => 'In-Transit Shipments', 'metric' => 'in_transit'],
                ['type' => 'kpi_card', 'title' => 'Fleet Utilization', 'metric' => 'fleet_utilization'],
                ['type' => 'pie_chart', 'title' => 'Shipment Types', 'metric' => 'shipment_types'],
                ['type' => 'kpi_card', 'title' => 'On-Time Rate', 'metric' => 'on_time_rate'],
            ],
        ],
        'education' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Enrolled Students', 'metric' => 'total_enrolled'],
                ['type' => 'line_chart', 'title' => 'Enrollment Trend', 'metric' => 'enrollment_trend'],
                ['type' => 'bar_chart', 'title' => 'Courses by Category', 'metric' => 'courses_by_category'],
                ['type' => 'kpi_card', 'title' => 'Revenue This Month', 'metric' => 'monthly_revenue'],
                ['type' => 'table', 'title' => 'Upcoming Classes', 'metric' => 'upcoming_classes'],
                ['type' => 'kpi_card', 'title' => 'Completion Rate', 'metric' => 'completion_rate'],
            ],
        ],
        'real_estate' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Active Listings', 'metric' => 'active_listings'],
                ['type' => 'line_chart', 'title' => 'Sales Pipeline', 'metric' => 'pipeline_value'],
                ['type' => 'pie_chart', 'title' => 'Property Types', 'metric' => 'property_types'],
                ['type' => 'kpi_card', 'title' => 'Properties Sold', 'metric' => 'properties_sold'],
                ['type' => 'table', 'title' => 'Pending Transactions', 'metric' => 'pending_transactions'],
                ['type' => 'kpi_card', 'title' => 'Avg Days on Market', 'metric' => 'avg_days_market'],
            ],
        ],
        'agriculture' => [
            'widgets' => [
                ['type' => 'kpi_card', 'title' => 'Active Farms', 'metric' => 'active_farms'],
                ['type' => 'line_chart', 'title' => 'Harvest Yield', 'metric' => 'yield_trend'],
                ['type' => 'bar_chart', 'title' => 'Crop Distribution', 'metric' => 'crop_distribution'],
                ['type' => 'kpi_card', 'title' => 'Livestock Count', 'metric' => 'livestock_total'],
                ['type' => 'table', 'title' => 'Weather Alerts', 'metric' => 'weather_alerts'],
                ['type' => 'kpi_card', 'title' => 'Inventory Levels', 'metric' => 'stock_levels'],
            ],
        ],
    ],

    // Pre-configured metrics per industry
    'metrics' => [
        'retail' => [
            ['name' => 'Daily Sales', 'key' => 'daily_sales', 'type' => 'sum', 'source' => 'sales_orders'],
            ['name' => 'Average Order Value', 'key' => 'aov', 'type' => 'average', 'source' => 'sales_orders'],
            ['name' => 'Conversion Rate', 'key' => 'conversion_rate', 'type' => 'percentage', 'source' => 'website_analytics'],
            ['name' => 'Return Rate', 'key' => 'return_rate', 'type' => 'percentage', 'source' => 'returns'],
            ['name' => 'Customer Count', 'key' => 'customer_count', 'type' => 'count', 'source' => 'customers'],
            ['name' => 'Inventory Turnover', 'key' => 'inventory_turnover', 'type' => 'ratio', 'source' => 'inventory'],
        ],
        'manufacturing' => [
            ['name' => 'Production Volume', 'key' => 'production_volume', 'type' => 'sum', 'source' => 'work_orders'],
            ['name' => 'OEE (Overall Equipment Effectiveness)', 'key' => 'oee', 'type' => 'percentage', 'source' => 'equipment'],
            ['name' => 'Defect Rate', 'key' => 'defect_rate', 'type' => 'percentage', 'source' => 'quality_control'],
            ['name' => 'Cycle Time', 'key' => 'cycle_time', 'type' => 'average', 'source' => 'work_orders'],
            ['name' => 'Capacity Utilization', 'key' => 'capacity_utilization', 'type' => 'percentage', 'source' => 'production'],
            ['name' => 'COGS Ratio', 'key' => 'cogs_ratio', 'type' => 'percentage', 'source' => 'cogs'],
        ],
        'services' => [
            ['name' => 'Billable Hours', 'key' => 'billable_hours', 'type' => 'sum', 'source' => 'timesheets'],
            ['name' => 'Utilization Rate', 'key' => 'utilization_rate', 'type' => 'percentage', 'source' => 'timesheets'],
            ['name' => 'Project Margin', 'key' => 'project_margin', 'type' => 'percentage', 'source' => 'projects'],
            ['name' => 'Active Projects', 'key' => 'active_projects', 'type' => 'count', 'source' => 'projects'],
            ['name' => 'Revenue per Consultant', 'key' => 'revenue_per_consultant', 'type' => 'average', 'source' => 'finance'],
            ['name' => 'Client Retention Rate', 'key' => 'client_retention', 'type' => 'percentage', 'source' => 'crm'],
        ],
        'healthcare' => [
            ['name' => 'Patient Visits', 'key' => 'patient_visits', 'type' => 'count', 'source' => 'appointments'],
            ['name' => 'No-show Rate', 'key' => 'noshow_rate', 'type' => 'percentage', 'source' => 'appointments'],
            ['name' => 'Avg Wait Time', 'key' => 'avg_wait_time', 'type' => 'average', 'source' => 'appointments'],
            ['name' => 'Revenue per Patient', 'key' => 'revenue_per_patient', 'type' => 'average', 'source' => 'finance'],
            ['name' => 'Bed Occupancy Rate', 'key' => 'bed_occupancy', 'type' => 'percentage', 'source' => 'facilities'],
            ['name' => 'Patient Satisfaction', 'key' => 'patient_satisfaction', 'type' => 'average', 'source' => 'feedback'],
        ],
        'food_beverage' => [
            ['name' => 'Daily Revenue', 'key' => 'daily_revenue', 'type' => 'sum', 'source' => 'sales_orders'],
            ['name' => 'Table Turnover Rate', 'key' => 'table_turnover', 'type' => 'average', 'source' => 'pos'],
            ['name' => 'Food Cost %', 'key' => 'food_cost_pct', 'type' => 'percentage', 'source' => 'cogs'],
            ['name' => 'Average Check', 'key' => 'avg_check', 'type' => 'average', 'source' => 'pos'],
            ['name' => 'Waste Percentage', 'key' => 'waste_pct', 'type' => 'percentage', 'source' => 'inventory'],
            ['name' => 'Customer Rating', 'key' => 'customer_rating', 'type' => 'average', 'source' => 'reviews'],
        ],
        'construction' => [
            ['name' => 'Projects On Schedule', 'key' => 'projects_on_schedule', 'type' => 'percentage', 'source' => 'projects'],
            ['name' => 'Budget Variance', 'key' => 'budget_variance', 'type' => 'percentage', 'source' => 'projects'],
            ['name' => 'Safety Incidents', 'key' => 'safety_incidents', 'type' => 'count', 'source' => 'safety'],
            ['name' => 'Equipment Utilization', 'key' => 'equipment_util', 'type' => 'percentage', 'source' => 'assets'],
            ['name' => 'Labor Productivity', 'key' => 'labor_productivity', 'type' => 'ratio', 'source' => 'hrms'],
            ['name' => 'Change Orders', 'key' => 'change_orders', 'type' => 'count', 'source' => 'projects'],
        ],
        'logistics' => [
            ['name' => 'Shipments Delivered', 'key' => 'shipments_delivered', 'type' => 'count', 'source' => 'shipments'],
            ['name' => 'On-Time Delivery Rate', 'key' => 'on_time_rate', 'type' => 'percentage', 'source' => 'shipments'],
            ['name' => 'Fleet Utilization', 'key' => 'fleet_utilization', 'type' => 'percentage', 'source' => 'fleet'],
            ['name' => 'Cost per Mile', 'key' => 'cost_per_mile', 'type' => 'average', 'source' => 'finance'],
            ['name' => 'Load Factor', 'key' => 'load_factor', 'type' => 'percentage', 'source' => 'shipments'],
            ['name' => 'Damage Rate', 'key' => 'damage_rate', 'type' => 'percentage', 'source' => 'shipments'],
        ],
        'education' => [
            ['name' => 'Total Enrollments', 'key' => 'total_enrollments', 'type' => 'count', 'source' => 'students'],
            ['name' => 'Student Retention Rate', 'key' => 'retention_rate', 'type' => 'percentage', 'source' => 'students'],
            ['name' => 'Course Completion Rate', 'key' => 'completion_rate', 'type' => 'percentage', 'source' => 'courses'],
            ['name' => 'Student Satisfaction', 'key' => 'student_satisfaction', 'type' => 'average', 'source' => 'feedback'],
            ['name' => 'Revenue per Course', 'key' => 'revenue_per_course', 'type' => 'average', 'source' => 'finance'],
            ['name' => 'Teacher-Student Ratio', 'key' => 'teacher_student_ratio', 'type' => 'ratio', 'source' => 'hrms'],
        ],
        'real_estate' => [
            ['name' => 'Active Listings', 'key' => 'active_listings', 'type' => 'count', 'source' => 'properties'],
            ['name' => 'Days on Market', 'key' => 'days_on_market', 'type' => 'average', 'source' => 'properties'],
            ['name' => 'Sale Price Ratio', 'key' => 'sale_price_ratio', 'type' => 'percentage', 'source' => 'transactions'],
            ['name' => 'Lease Renewal Rate', 'key' => 'lease_renewal', 'type' => 'percentage', 'source' => 'leases'],
            ['name' => 'Occupancy Rate', 'key' => 'occupancy_rate', 'type' => 'percentage', 'source' => 'properties'],
            ['name' => 'Commission Earned', 'key' => 'commission_earned', 'type' => 'sum', 'source' => 'finance'],
        ],
        'agriculture' => [
            ['name' => 'Harvest Yield', 'key' => 'harvest_yield', 'type' => 'sum', 'source' => 'production'],
            ['name' => 'Land Utilization', 'key' => 'land_utilization', 'type' => 'percentage', 'source' => 'farms'],
            ['name' => 'Livestock Health Index', 'key' => 'livestock_health', 'type' => 'average', 'source' => 'veterinary'],
            ['name' => 'Feed Conversion Ratio', 'key' => 'feed_conversion', 'type' => 'ratio', 'source' => 'production'],
            ['name' => 'Crop Insurance Coverage', 'key' => 'insurance_coverage', 'type' => 'percentage', 'source' => 'finance'],
            ['name' => 'Seasonal Revenue', 'key' => 'seasonal_revenue', 'type' => 'sum', 'source' => 'finance'],
        ],
    ],

    // Default cost components per industry
    'cost_components' => [
        'retail' => ['raw_materials', 'packaging', 'shipping_in', 'store_rental'],
        'manufacturing' => ['raw_materials', 'direct_labor', 'manufacturing_overhead', 'packaging', 'energy'],
        'services' => ['direct_labor', 'software_licenses', 'office_overhead', 'professional_development'],
        'healthcare' => ['medical_supplies', 'direct_labor', 'equipment_amortization', 'compliance_costs'],
        'food_beverage' => ['raw_materials', 'direct_labor', 'packaging', 'utilities', 'rent'],
        'construction' => ['materials', 'labor', 'equipment_rental', 'subcontractor_costs', 'permits'],
        'logistics' => ['fuel', 'driver_labor', 'vehicle_maintenance', 'warehousing', 'insurance'],
        'education' => ['curriculum_development', 'instructor_fees', 'facility_costs', 'technology'],
        'real_estate' => ['property_acquisition', 'renovation_costs', 'property_management', 'marketing'],
        'agriculture' => ['seeds', 'fertilizers', 'labor', 'equipment', 'irrigation', 'transportation'],
    ],

    // Template documents per industry
    'templates' => [
        'retail' => ['receipt', 'invoice', 'return_receipt', 'gift_card'],
        'manufacturing' => ['purchase_order', 'bom', 'work_order', 'quality_inspection'],
        'services' => ['proposal', 'statement_of_work', 'timesheet', 'invoice'],
        'healthcare' => ['prescription', 'patient_record', 'invoice', 'referral_letter'],
        'food_beverage' => ['receipt', 'invoice', 'recipe_card', 'inventory_checklist'],
        'construction' => ['quote', 'change_order', 'purchase_order', 'safety_report'],
        'logistics' => ['waybill', 'packing_list', 'invoice', 'customs_declaration'],
        'education' => ['enrollment_form', 'invoice', 'certificate', 'transcript'],
        'real_estate' => ['listing_agreement', 'purchase_agreement', 'lease_agreement', 'inspection_report'],
        'agriculture' => ['purchase_order', 'harvest_log', 'inventory_sheet', 'sales_receipt'],
    ],

    // Workflow templates per industry
    'workflows' => [
        'retail' => ['point_of_sale', 'inventory_reorder', 'return_exchange', 'loyalty_points'],
        'manufacturing' => ['work_order', 'material_request', 'quality_control', 'production_report'],
        'services' => ['project_creation', 'timesheet_approval', 'expense_claim', 'resource_allocation'],
        'healthcare' => ['patient_checkin', 'appointment_scheduling', 'prescription_processing', 'billing'],
        'food_beverage' => ['order_processing', 'table_management', 'inventory_order', 'recipe_production'],
        'construction' => ['project_bid', 'material_procurement', 'progress_tracking', 'handover'],
        'logistics' => ['shipment_creation', 'route_planning', 'delivery_confirmation', 'claims'],
        'education' => ['student_enrollment', 'class_scheduling', 'grade_entry', 'certification'],
        'real_estate' => ['property_listing', 'viewing_scheduling', 'offer_negotiation', 'closing'],
        'agriculture' => ['planting_schedule', 'harvest_planning', 'inventory_management', 'sales_distribution'],
    ],
];
