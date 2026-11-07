<?php

namespace App\Modules\SharedInbox\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mesej dalam perbincangan.
 */
class InboxMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'inbox_messages';

    public const TYPE_INCOMING = 'incoming';

    public const TYPE_OUTGOING = 'outgoing';

    public const TYPE_ACTIVITY = 'activity';

    public const TYPE_NOTE = 'note';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'sender_id',
        'sender_type',
        'type',
        'content',
        'attachments',
        'is_ai_generated',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_ai_generated' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_INCOMING,
        'sender_type' => 'contact',
        'is_ai_generated' => false,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(InboxConversation::class, 'conversation_id');
    }
}
