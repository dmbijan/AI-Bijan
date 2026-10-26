<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Projek perkhidmatan — rekod induk setiap kerja (BAB 87.3).
 *
 * Aliran status:
 *   draft → quoted → confirmed → in_progress → delivered → completed
 *                                    ↘ on_hold / cancelled
 */
class ServiceProject extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Peta peralihan status yang sah. */
    public const ALIRAN = [
        self::STATUS_DRAFT => [self::STATUS_QUOTED, self::STATUS_CANCELLED],
        self::STATUS_QUOTED => [self::STATUS_CONFIRMED, self::STATUS_DRAFT, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_IN_PROGRESS, self::STATUS_ON_HOLD, self::STATUS_CANCELLED],
        self::STATUS_IN_PROGRESS => [self::STATUS_DELIVERED, self::STATUS_ON_HOLD, self::STATUS_CANCELLED],
        self::STATUS_ON_HOLD => [self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
        self::STATUS_DELIVERED => [self::STATUS_COMPLETED, self::STATUS_IN_PROGRESS],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    protected $table = 'service_projects';

    protected $fillable = [
        'tenant_id',
        'project_no',
        'client_id',
        'package_id',
        'project_id',
        'owner_user_id',
        'title',
        'description',
        'industry',
        'status',
        'priority',
        'start_date',
        'end_date',
        'delivery_date',
        'venue',
        'venue_address',
        'guest_count',
        'contract_value',
        'estimated_cost',
        'actual_cost',
        'margin_amount',
        'margin_percent',
        'currency',
        'progress_percent',
        'custom_fields',
        'internal_notes',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'delivery_date' => 'date',
        'contract_value' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'guest_count' => 'integer',
        'progress_percent' => 'integer',
        'custom_fields' => 'array',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ServiceClient::class, 'client_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class, 'service_project_id')->orderBy('sequence');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(ServiceQuotation::class, 'service_project_id')->orderByDesc('version');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ServiceContract::class, 'service_project_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class, 'service_project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ServiceTask::class, 'service_project_id')->orderBy('sequence');
    }

    public function equipmentReservations(): HasMany
    {
        return $this->hasMany(ServiceEquipmentReservation::class, 'service_project_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ServiceDelivery::class, 'service_project_id');
    }

    /** Bolehkah status ditukar ke status baharu? */
    public function bolehTukarKe(string $status): bool
    {
        return in_array($status, self::ALIRAN[$this->status] ?? [], true);
    }

    /** Adakah projek masih aktif (belum selesai/batal)? */
    public function masihAktif(): bool
    {
        return ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    /** Semakan konflik tarikh dengan projek lain. */
    public function bertindihDengan(self $lain): bool
    {
        if (! $this->start_date || ! $this->end_date || ! $lain->start_date || ! $lain->end_date) {
            return false;
        }

        return $this->start_date->lte($lain->end_date) && $this->end_date->gte($lain->start_date);
    }
}
