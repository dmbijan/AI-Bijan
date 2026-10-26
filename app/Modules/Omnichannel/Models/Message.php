<?php

namespace App\Modules\Omnichannel\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'omnichannel_messages';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'channel_id',
        'sender_type', // contact, agent, bot, system
        'sender_id',
        'direction', // inbound, outbound
        'content_type', // text, image, video, audio, file, location, template
        'content',
        'media_url',
        'media_path',
        'metadata',
        'status', // sent, delivered, read, failed
        'external_message_id',
        'external_reply_to_id',
        'error_message',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'content' => 'array',
        'metadata' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }

    // Accessors
    public function getPreviewAttribute()
    {
        if (is_array($this->content) && isset($this->content['text'])) {
            return mb_substr($this->content['text'], 0, 100);
        }
        return '[' . $this->content_type . ']';
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'sent' => 'info',
            'delivered' => 'success',
            'read' => 'success',
            'failed' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    // Scopes
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByText($query)
    {
        return $query->where('content_type', 'text');
    }

    // Methods
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        return $this;
    }

    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
        return $this;
    }

    public function markAsFailed($errorMessage = '')
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);
        return $this;
    }
}
