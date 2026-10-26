<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Log peristiwa domain (outbox ringkas) — BAB 87.4.
 *
 * Setiap peristiwa disimpan dahulu (status `pending`) kemudian
 * diproses oleh `ServiceOSService::prosesOutbox()`. Ini memberi
 * jaminan sekurang-kurangnya-sekali + kawalan idempotensi.
 */
class ServiceDomainEvent extends Model
{
    use BelongsToTenant;

    public const LEAD_QUALIFIED = 'LeadQualified';
    public const QUOTATION_CREATED = 'QuotationCreated';
    public const QUOTATION_ACCEPTED = 'QuotationAccepted';
    public const CONTRACT_ACCEPTED = 'ContractAccepted';
    public const BOOKING_CONFIRMED = 'BookingConfirmed';
    public const ASSIGNMENT_CREATED = 'AssignmentCreated';
    public const SCHEDULE_CONFLICT_DETECTED = 'ScheduleConflictDetected';
    public const PAYMENT_SUCCEEDED = 'PaymentSucceeded';
    public const PAYMENT_FAILED = 'PaymentFailed';
    public const PAYMENT_OVERDUE = 'PaymentOverdue';
    public const PROJECT_STARTED = 'ProjectStarted';
    public const PROJECT_COMPLETED = 'ProjectCompleted';
    public const DELIVERY_ACCEPTED = 'DeliveryAccepted';
    public const PROJECT_MARGIN_CALCULATED = 'ProjectMarginCalculated';

    public const EVENTS = [
        self::LEAD_QUALIFIED,
        self::QUOTATION_CREATED,
        self::QUOTATION_ACCEPTED,
        self::CONTRACT_ACCEPTED,
        self::BOOKING_CONFIRMED,
        self::ASSIGNMENT_CREATED,
        self::SCHEDULE_CONFLICT_DETECTED,
        self::PAYMENT_SUCCEEDED,
        self::PAYMENT_FAILED,
        self::PAYMENT_OVERDUE,
        self::PROJECT_STARTED,
        self::PROJECT_COMPLETED,
        self::DELIVERY_ACCEPTED,
        self::PROJECT_MARGIN_CALCULATED,
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_FAILED = 'failed';

    protected $table = 'service_domain_events';

    protected $fillable = [
        'tenant_id',
        'event_name',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'status',
        'attempts',
        'dispatched_at',
        'last_error',
        'idempotency_key',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'dispatched_at' => 'datetime',
    ];

    public function bolehCubaLagi(int $had = 3): bool
    {
        return $this->status !== self::STATUS_DISPATCHED && $this->attempts < $had;
    }
}
