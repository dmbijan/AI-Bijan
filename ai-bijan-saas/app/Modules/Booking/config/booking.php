<?php

return [
    // Service categories
    'service_categories' => [
        'haircut' => 'Potongan Rambut',
        'coloring' => 'Pewarnaan Rambut',
        'facial' => 'Rawatan Muka',
        'massage' => 'Urutan',
        'nails' => 'Kuku (Manicure/Pedicure)',
        'makeup' => 'Solekan',
        'consultation' => 'Perundingan',
        'medical' => 'Perubatan',
        'dental' => 'Gigi',
        'other' => 'Lain-lain',
    ],

    // Appointment statuses
    'appointment_statuses' => [
        'draft' => 'Rangka',
        'confirmed' => 'Disahkan',
        'pending' => 'Menunggu',
        'in_progress' => 'Sedang Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'no_show' => 'Tidak Hadir',
        'rescheduled' => 'Telah Dijadual Semula',
    ],

    // Slot durations (minutes)
    'slot_durations' => [
        15 => '15 minit',
        30 => '30 minit',
        45 => '45 minit',
        60 => '1 jam',
        90 => '1 jam 30 minit',
        120 => '2 jam',
    ],

    // Booking settings
    'booking' => [
        'advance_booking_days' => 30,      // Max days in advance
        'min_booking_notice_hours' => 2,   // Min hours before appointment
        'cancellation_window_hours' => 24, // Hours before appointment for free cancellation
        'buffer_time_minutes' => 15,       // Buffer between appointments
        'max_appointments_per_slot' => 1,  // Max concurrent appointments per provider/slot
    ],

    // Reminder settings
    'reminders' => [
        'enabled' => true,
        'channels' => ['sms', 'whatsapp', 'email'],
        'schedule' => [
            '24h' => '24 jam sebelum',
            '2h' => '2 jam sebelum',
            '30m' => '30 minit sebelum',
        ],
    ],

    // Timezone
    'timezone' => 'Asia/Kuala_Lumpur',

    // Business hours default
    'business_hours' => [
        'monday' => ['start' => '09:00', 'end' => '18:00'],
        'tuesday' => ['start' => '09:00', 'end' => '18:00'],
        'wednesday' => ['start' => '09:00', 'end' => '18:00'],
        'thursday' => ['start' => '09:00', 'end' => '18:00'],
        'friday' => ['start' => '09:00', 'end' => '18:00'],
        'saturday' => ['start' => '09:00', 'end' => '17:00'],
        'sunday' => null, // Closed
    ],
];
