<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reminder & Notification Center (BAB 86 / Idea #22)
    |--------------------------------------------------------------------------
    |
    | Tetapan pusat peringatan merentas semua modul.
    |
    */

    'ai' => [
        // Ambang skor untuk masuk kotak masuk AI
        'inbox_threshold' => (float) env('REMINDER_INBOX_THRESHOLD', 60),
        // Ambang untuk auto-eskalasi
        'escalate_threshold' => (float) env('REMINDER_ESCALATE_THRESHOLD', 75),
        // Task type AI Gateway
        'task_type' => env('REMINDER_AI_TASK_TYPE', 'reminder_triage'),
    ],

    'channels' => [
        // Kanal lalai
        'default' => ['in_app'],
        // Kanal yang disokong
        'supported' => ['in_app', 'email', 'whatsapp', 'sms', 'push'],
    ],

    'escalation' => [
        // Escalate selepas berapa jam lewat
        'after_hours' => (int) env('REMINDER_ESCALATE_AFTER_HOURS', 24),
        // Berapa kali escalate sebelumEscalate kepada pengurusan
        'max_escalations' => (int) env('REMINDER_MAX_ESCALATIONS', 3),
        // Prioriti selepas escalate
        'escalated_priority' => 'urgent',
    ],

    'snooze' => [
        'default_minutes' => (int) env('REMINDER_SNOOZE_DEFAULT', 60),
        'max_minutes' => (int) env('REMINDER_SNOOZE_MAX', 43200),
        'quick_options' => [15, 60, 240, 1440],
    ],

    'catalog' => [
        // Jana katalog lalai untuk tenant baharu
        'auto_generate_on_register' => (bool) env('REMINDER_AUTO_CATALOG', true),
    ],
];
