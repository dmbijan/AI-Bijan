<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentChatMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'content_chat_messages';

    protected $fillable = [
        'tenant_id',
        'content_chat_id',
        'external_id',
        'from',
        'body',
        'sent_at',
        'is_read',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    protected $attributes = [
        'is_read' => false,
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(ContentChat::class, 'content_chat_id');
    }
}
