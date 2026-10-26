<?php

namespace App\Modules\Reminder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reminder extends Model
{
    use BelongsToTenant;

    protected $table = 'reminders';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SNOOZED = 'snoozed';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DISMISSED = 'dismissed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'tenant_id',
        'reminder_type_id',
        'type_key',
        'title',
        'description',
        'action_note',
        'subject_type',
        'subject_id',
        'subject_label',
        'assigned_to',
        'assigned_role',
        'channels',
        'status',
        'priority',
        'due_at',
        'snoozed_until',
        'sent_at',
        'acknowledged_at',
        'completed_at',
        'escalated_at',
        'escalation_count',
        'reminder_count',
        'ai_priority_score',
        'ai_reasoning',
        'ai_escalated',
        'meta',
    ];

    protected $casts = [
        'channels' => 'array',
        'meta' => 'array',
        'due_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'completed_at' => 'datetime',
        'escalated_at' => 'datetime',
        'escalation_count' => 'integer',
        'reminder_count' => 'integer',
        'ai_priority_score' => 'float',
        'ai_escalated' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'priority' => self::PRIORITY_NORMAL,
        'escalation_count' => 0,
        'reminder_count' => 1,
        'ai_escalated' => false,
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReminderType::class, 'reminder_type_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ReminderDelivery::class, 'reminder_id');
    }

    public function aktif(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_SNOOZED], true);
    }

    public function lewat(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_SNOOZED], true);
    }

    public function terbuka(): bool
    {
        return $this->snoozed_until !== null && $this->snoozed_until->isFuture();
    }
}
