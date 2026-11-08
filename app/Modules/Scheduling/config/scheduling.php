<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduling — penjadualan tempahan ala Cal.diy
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/calcom/cal.diy
    |
    | Melengkapkan modul `Booking` dengan:
    |   - pautan penjadualan awam (bertoken)
    |   - pengiraan slot tersedia dengan buffer & notifikasi minimum
    |   - routing round-robin / beban-terendah antara provider
    |   - jadual pasukan (team calendar)
    |
    */

    'enabled' => (bool) env('SCHEDULING_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_links_per_tenant' => (int) env('SCHEDULING_MAX_LINKS', 50),
        'max_slots_per_day' => (int) env('SCHEDULING_MAX_SLOTS', 96),
        'max_booking_days_ahead' => (int) env('SCHEDULING_MAX_DAYS', 90),
    ],

    /*
    | Tetapan slot lalai.
    */
    'slot' => [
        'default_duration_minutes' => (int) env('SCHEDULING_DURATION', 30),
        'default_interval_minutes' => (int) env('SCHEDULING_INTERVAL', 15),
        'default_buffer_before' => (int) env('SCHEDULING_BUFFER_BEFORE', 0),
        'default_buffer_after' => (int) env('SCHEDULING_BUFFER_AFTER', 0),
        'minimum_notice_minutes' => (int) env('SCHEDULING_MIN_NOTICE', 120),
    ],

    /*
    | Routing provider.
    */
    'routing' => [
        'default_strategy' => env('SCHEDULING_ROUTING', 'round_robin'),
        'strategies' => ['round_robin', 'least_loaded', 'random', 'skill_match'],
        'max_daily_bookings_per_provider' => (int) env('SCHEDULING_MAX_DAILY', 8),
    ],

    /*
    | Pautan awam.
    */
    'public_links' => [
        'require_email' => (bool) env('SCHEDULING_REQUIRE_EMAIL', true),
        'require_phone' => (bool) env('SCHEDULING_REQUIRE_PHONE', false),
        'allow_cancellation' => (bool) env('SCHEDULING_ALLOW_CANCEL', true),
        'cancellation_hours' => (int) env('SCHEDULING_CANCEL_HOURS', 24),
        'auto_expire_days' => (int) env('SCHEDULING_AUTO_EXPIRE', 0),
    ],

    /*
    | Notifikasi.
    */
    'notifications' => [
        'email_confirmation' => (bool) env('SCHEDULING_EMAIL_CONFIRM', true),
        'sms_reminder' => (bool) env('SCHEDULING_SMS_REMINDER', false),
        'reminder_hours_before' => (int) env('SCHEDULING_REMINDER_HOURS', 24),
    ],

];
