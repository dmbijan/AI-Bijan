<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentChat extends Model
{
    use BelongsToTenant;

    protected $table = 'content_chats';

    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'content_platform_id',
        'external_conversation_id',
        'customer_name',
        'customer_id',
        'customer_avatar',
        'last_message',
        'last_message_from',
        'status',
        'unread_count',
        'message_count',
        'sentiment',
        'requires_human',
        'ai_summary',
        'last_message_at',
        'resolved_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'unread_count' => 'integer',
        'message_count' => 'integer',
        'requires_human' => 'boolean',
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'unread_count' => 0,
        'message_count' => 0,
        'requires_human' => false,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ContentChatMessage::class, 'content_chat_id');
    }

    public function platform()
    {
        return $this->belongsTo(ContentPlatform::class, 'content_platform_id');
    }

    public function perlukanTindakan(): bool
    {
        return $this->requires_human || $this->unread_count > 0;
    }
}
