<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-HRMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk modul HRMS: Employee, Shift, Leave & Attendance
    |
    */

    // Working hours configuration
    'working_hours' => [
        'default_start_time' => env('HRMS_WORK_START', '09:00'),
        'default_end_time' => env('HRMS_WORK_END', '17:00'),
        'default_working_days' => 5, // Mon-Fri
        'grace_period_minutes' => env('HRMS_GRACE_PERIOD', 15),
        'overtime_threshold' => 8, // Hours per day for overtime calculation
    ],

    // Leave configuration
    'leave' => [
        'types' => [
            'annual' => ['name' => 'Annual Leave', 'max_days' => 14, 'encashable' => true],
            'medical' => ['name' => 'Medical Leave', 'max_days' => 14, 'encashable' => false, 'requires_doc' => true],
            'unpaid' => ['name' => 'Unpaid Leave', 'max_days' => null, 'encashable' => false],
            'maternity' => ['name' => 'Maternity Leave', 'max_days' => 90, 'encashable' => false],
            'paternity' => ['name' => 'Paternity Leave', 'max_days' => 7, 'encashable' => false],
            'wedding' => ['name' => 'Wedding Leave', 'max_days' => 3, 'encashable' => false],
            'haji' => ['name' => 'Haji Leave', 'max_days' => 14, 'encashable' => false],
            'emergency' => ['name' => 'Emergency Leave', 'max_days' => 3, 'encashable' => false],
        ],
        'auto_approve_below_days' => env('HRMS_LEAVE_AUTO_APPROVE', 2),
        'advance_leave_allowed' => true,
        'max_advance_days' => 7,
    ],

    // Attendance configuration
    'attendance' => [
        'check_in_methods' => ['manual', 'biometric', 'gps', 'web'],
        'geo_fencing_enabled' => env('HRMS_GEOFENCE_ENABLED', false),
        'geo_fencing_radius_meters' => env('HRMS_GEOFENCE_RADIUS', 100),
        'require_photo_on_checkin' => env('HRMS_CHECKIN_PHOTO', false),
        'auto_logout_after_hours' => 12, // Auto logout if no check-out
    ],

    // Department configuration
    'departments' => [
        'default_structure' => [
            'Management',
            'Operations',
            'Finance',
            'Human Resources',
            'IT',
            'Marketing',
            'Sales',
            'Customer Service',
        ],
    ],

    // Employment types
    'employment_types' => [
        'full_time' => ['name' => 'Full Time', 'benefits' => true],
        'part_time' => ['name' => 'Part Time', 'benefits' => false],
        'contract' => ['name' => 'Contract', 'benefits' => false],
        'freelance' => ['name' => 'Freelance', 'benefits' => false],
        'intern' => ['name' => 'Intern', 'benefits' => false],
    ],

    // Notification settings
    'notifications' => [
        'leave_approval' => true,
        'leave_rejection' => true,
        'overdue_timesheet' => true,
        'upcoming_holiday' => true,
        'birthday_reminder' => true,
        'work_anniversary' => true,
    ],
];
