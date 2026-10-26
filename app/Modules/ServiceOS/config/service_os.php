<?php

/**
 * Konfigurasi B-ServiceOS (BAB 87).
 *
 * ⚠️ Modul ini TIDAK memproses wang. Pembayaran kekal di Invoicing/Finance.
 */
return [
    'enabled' => env('SERVICE_OS_ENABLED', true),

    'limits' => [
        'max_sessions_per_project' => 200,
        'max_tasks_per_project' => 500,
        'max_outbox_batch' => 100,
        'outbox_max_attempts' => 3,
    ],

    'defaults' => [
        'currency' => 'MYR',
        'country' => 'MY',
        'deposit_percent' => 30,
        'buffer_minutes' => 0,
        'priority' => 'normal',
    ],

    /*
    |----------------------------------------------------------------------
    | Industri yang disokong (kunci mesti sepadan dengan TEMPLAT_CHECKLIST)
    |----------------------------------------------------------------------
    */
    'industries' => [
        'umum' => 'Umum',
        'fotografi' => 'Fotografi & Videografi',
        'wedding' => 'Wedding & Event Planner',
        'kontraktor' => 'Kontraktor & Kerja Lapangan',
        'katering' => 'Katering & Perkhidmatan Acara',
    ],

    /*
    |----------------------------------------------------------------------
    | Peristiwa domain yang dihantar ke outbox
    |----------------------------------------------------------------------
    */
    'events' => [
        'LeadQualified',
        'QuotationCreated',
        'QuotationAccepted',
        'ContractAccepted',
        'BookingConfirmed',
        'AssignmentCreated',
        'ScheduleConflictDetected',
        'PaymentSucceeded',
        'PaymentFailed',
        'PaymentOverdue',
        'ProjectStarted',
        'ProjectCompleted',
        'DeliveryAccepted',
        'ProjectMarginCalculated',
    ],

    /*
    |----------------------------------------------------------------------
    | Nota integrasi — guna semula domain sedia ada (BAB 87.8)
    |----------------------------------------------------------------------
    */
    'integration' => [
        'crm' => 'Guna App\Modules\CRM untuk rekod pelanggan asas (jangan cipta pendua).',
        'invoicing' => 'Guna App\Modules\Invoicing untuk invois & pembayaran.',
        'finance' => 'Guna App\Modules\Finance untuk rekonsiliasi.',
        'signdocs' => 'Guna App\Modules\SignDocs untuk e-tandatangan kontrak.',
        'workflow' => 'Guna App\Modules\WorkflowStudio untuk automasi berasaskan peristiwa.',
        'reminder' => 'Guna App\Modules\Reminder untuk peringatan pembayaran & tarikh kerja.',
    ],
];
