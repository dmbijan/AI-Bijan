<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTimelineEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'order_timeline_events';

    public const EVENT_CREATED = 'created';
    public const EVENT_STATUS_CHANGED = 'status_changed';
    public const EVENT_PAYMENT = 'payment';
    public const EVENT_SYNC = 'sync';
    public const EVENT_NOTE = 'note';
    public const EVENT_ITEM_ADDED = 'item_added';

    public const ACTOR_SYSTEM = 'system';
    public const ACTOR_USER = 'user';
    public const ACTOR_AI = 'ai';
    public const ACTOR_PLATFORM = 'platform';

    protected $fillable = [
        'tenant_id',
        'unified_order_id',
        'event_type',
        'from_value',
        'to_value',
        'description',
        'actor_type',
        'actor_id',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    protected $attributes = [
        'event_type' => self::EVENT_NOTE,
        'actor_type' => self::ACTOR_SYSTEM,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UnifiedOrder::class, 'unified_order_id');
    }
}
