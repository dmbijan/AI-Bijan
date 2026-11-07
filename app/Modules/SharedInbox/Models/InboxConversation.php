<?php

namespace App\Modules\SharedInbox\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Perbincangan dalam peti masuk bersama.
 */
class InboxConversation extends Model
{
    use BelongsToTenant;

    protected $table = 'inbox_conversations';

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'shared_inbox_id',
        'conversation_no',
        'contact_name',
        'contact_email',
        'contact_phone',
        'channel',
        'subject',
        'status',
        'priority',
        'assigned_to',
        'tags',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'sla_response_due_at',
        'sla_resolution_due_at',
        'sla_response_breached',
        'sla_resolution_breached',
        'message_count',
        'last_message_at',
        'rating',
        'notes',
        'custom_attributes',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_attributes' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_response_due_at' => 'datetime',
        'sla_resolution_due_at' => 'datetime',
        'sla_response_breached' => 'boolean',
        'sla_resolution_breached' => 'boolean',
        'message_count' => 'integer',
        'last_message_at' => 'datetime',
        'rating' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'priority' => 'normal',
        'sla_response_breached' => false,
        'sla_resolution_breached' => false,
        'message_count' => 0,
    ];

    public function sharedInbox(): BelongsTo
    {
        return $this->belongsTo(SharedInbox::class, 'shared_inbox_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'conversation_id');
    }

    /**
     * Adakah SLA respons sudah dilampaui?
     */
    public function slaResponsLampau(): bool
    {
        if ($this->sla_response_breached || $this->first_response_at !== null) {
            return (bool) $this->sla_response_breached;
        }

        return $this->sla_response_due_at !== null && $this->sla_response_due_at->isPast();
    }
}
