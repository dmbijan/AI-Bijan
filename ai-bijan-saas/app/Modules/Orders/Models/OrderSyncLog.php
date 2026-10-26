<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSyncLog extends Model
{
    use BelongsToTenant;

    protected $table = 'order_sync_logs';

    public const DIRECTION_PULL = 'pull';
    public const DIRECTION_PUSH = 'push';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'tenant_id',
        'unified_order_id',
        'platform',
        'direction',
        'status',
        'records_synced',
        'records_failed',
        'error_message',
        'payload_summary',
        'duration_ms',
    ];

    protected $casts = [
        'payload_summary' => 'array',
        'records_synced' => 'integer',
        'records_failed' => 'integer',
        'duration_ms' => 'integer',
    ];

    protected $attributes = [
        'direction' => self::DIRECTION_PULL,
        'status' => self::STATUS_SUCCESS,
        'records_synced' => 0,
        'records_failed' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UnifiedOrder::class, 'unified_order_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
