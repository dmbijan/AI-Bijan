<?php

namespace App\Modules\Omnichannel\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'omnichannel_conversations';

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'contact_id',
        'agent_id',
        'subject',
        'status',
        'priority',
        'source_contact_identifier',
        'source_contact_name',
        'source_contact_email',
        'source_contact_phone',
        'last_message_at',
        'message_count',
        'first_response_at',
        'resolved_at',
        'tags',
        'notes',
        'custom_fields',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'metadata' => 'array',
        'message_count' => 'integer',
        'last_message_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function agent()
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // Accessors
    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'open' => 'success',
            'pending' => 'warning',
            'archived' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

    public function getPriorityBadgeClassAttribute()
    {
        return [
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
        ][$this->priority] ?? 'secondary';
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByChannel($query, $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Methods
    public function assignAgent($agentId)
    {
        $this->update(['agent_id' => $agentId]);
        return $this;
    }

    public function open()
    {
        $this->update([
            'status' => 'open',
            'last_message_at' => now(),
        ]);
        return $this;
    }

    public function close()
    {
        $this->update([
            'status' => 'archived',
            'resolved_at' => now(),
        ]);
        return $this;
    }

    public function pending()
    {
        $this->update([
            'status' => 'pending',
        ]);
        return $this;
    }

    public function incrementMessageCount()
    {
        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);
        return $this;
    }

    public function markFirstResponse()
    {
        if (!$this->first_response_at) {
            $this->update(['first_response_at' => now()]);
        }
        return $this;
    }

    public function addTag($tag)
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
        return $this;
    }

    public function getResponseTimeMinutes()
    {
        if ($this->first_response_at && $this->created_at) {
            return $this->created_at->diffInMinutes($this->first_response_at);
        }
        return null;
    }
}
