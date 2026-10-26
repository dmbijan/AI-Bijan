<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B-Virtual Office Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk modul Virtual Office: Agent, Task, Board, Schedule
    |
    */

    // Default task assignment rules
    'assignment' => [
        'auto_assign_enabled' => env('VO_AUTO_ASSIGN_ENABLED', false),
        'max_concurrent_tasks' => env('VO_MAX_CONCURRENT_TASKS', 5),
        'priority_levels' => [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'urgent' => 4,
        ],
    ],

    // Board settings
    'boards' => [
        'max_columns_per_board' => 10,
        'default_column_names' => ['To Do', 'In Progress', 'Review', 'Done'],
        'card_limit_per_user' => env('VO_CARD_LIMIT_PER_USER', 50),
    ],

    // Schedule settings
    'schedule' => [
        'timezone' => env('TZ', 'Asia/Kuala_Lumpur'),
        'working_hours_start' => env('WORKING_HOURS_START', '09:00'),
        'working_hours_end' => env('WORKING_HOURS_END', '18:00'),
        'reminder_minutes_before' => [15, 60], // 15 min & 1 hour before
    ],

    // Agent (AI/Team member) settings
    'agents' => [
        'types' => ['human', 'ai', 'hybrid'],
        'ai_status_levels' => ['idle', 'active', 'busy', 'offline'],
        'max_agents_per_tenant' => env('VO_MAX_AGENTS', 20),
    ],

    // Notification settings
    'notifications' => [
        'task_assigned' => true,
        'task_due_soon' => true,
        'task_overdue' => true,
        'board_shared' => true,
        'schedule_reminder' => true,
    ],
];
