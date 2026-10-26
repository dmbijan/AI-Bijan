<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tempahan peralatan untuk projek / sesi.
 */
class ServiceEquipmentReservation extends Model
{
    use BelongsToTenant;

    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_RESERVED,
        self::STATUS_CHECKED_OUT,
        self::STATUS_RETURNED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'service_equipment_reservations';

    protected $fillable = [
        'tenant_id',
        'service_equipment_id',
        'service_project_id',
        'service_session_id',
        'quantity',
        'reserved_from',
        'reserved_to',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_from' => 'datetime',
        'reserved_to' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(ServiceEquipment::class, 'service_equipment_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ServiceSession::class, 'service_session_id');
    }
}
