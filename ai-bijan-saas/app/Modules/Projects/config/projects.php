<?php

// Configuration for B-Projects module
return [
    /*
    |--------------------------------------------------------------------------
    | Project Settings
    |--------------------------------------------------------------------------
    */
    'project' => [
        'status_workflow' => [
            'draft', 'active', 'on_hold', 'completed', 'cancelled',
        ],
        'priority_levels' => [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ],
        'max_hours_per_day' => 24,
        'require_task_for_timesheet' => false,
        'auto_archive_days' => 90, // Archive completed projects after X days
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Settings
    |--------------------------------------------------------------------------
    */
    'task' => [
        'status_workflow' => [
            'todo', 'in_progress', 'review', 'done', 'cancelled',
        ],
        'allow_cyclic_dependencies' => false,
        'require_dependency_completion' => true,
        'max_subtasks' => 50,
        'default_estimate_hours' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Milestone Settings
    |--------------------------------------------------------------------------
    */
    'milestone' => [
        'auto_advance_on_completion' => true,
        'notify_before_due_days' => [7, 3, 1], // Notify 7, 3, 1 days before due
    ],

    /*
    |--------------------------------------------------------------------------
    | Timesheet Settings
    |--------------------------------------------------------------------------
    */
    'timesheet' => [
        'status_workflow' => [
            'draf', 'dihantar', 'diluluskan', 'ditolak',
        ],
        'approval_required' => true,
        'max_hours_per_day' => 24,
        'submission_window_days' => 14, // Can only submit timesheets for last 14 days
        'billable_rates' => [
            'developer' => 150, // RM per hour
            'designer' => 120,
            'pm' => 180,
            'qa' => 100,
            'default' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Settings
    |--------------------------------------------------------------------------
    */
    'documents' => [
        'max_file_size_mb' => 50,
        'allowed_types' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg', 'zip'],
        'auto_version' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'task_assigned' => true,
        'task_due_soon' => true,
        'milestone_reached' => true,
        'timesheet_approved' => true,
        'timesheet_rejected' => true,
        'project_completed' => true,
    ],
];
