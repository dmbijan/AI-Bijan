<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penyerahan / handover hasil kerja kepada pelanggan.
 */
class ServiceDelivery extends Model
{
    use BelongsToTenant;

    public const TYPE_HANDOVER = 'handover';
    public const TYPE_GALLERY = 'gallery';
    public const TYPE_REPORT = 'report';
    public const TYPE_PHYSICAL = 'physical';

    public const TYPES = [
        self::TYPE_HANDOVER,
        self::TYPE_GALLERY,
        self::TYPE_REPORT,
        self::TYPE_PHYSICAL,
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DELIVERED,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    protected $table = 'service_deliveries';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'delivery_type',
        'status',
        'items',
        'delivered_at',
        'received_by_name',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'items' => 'array',
        'attachments' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    /** Adakah pelanggan sudah menerima penyerahan ini? */
    public function diterima(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
}
