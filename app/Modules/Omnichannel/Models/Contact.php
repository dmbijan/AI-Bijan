<?php

namespace App\Modules\Omnichannel\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'omnichannel_contacts';

    protected $fillable = [
        'tenant_id',
        'crm_contact_id',
        'source_channel_id',
        'name',
        'email',
        'phone_number',
        'social_handle',
        'avatar_url',
        'company',
        'tags',
        'custom_fields',
        'last_active_at',
        'total_conversations',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'metadata' => 'array',
        'last_active_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function crmContact()
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'crm_contact_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'source_channel_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Accessors
    public function getIdentifierAttribute()
    {
        return $this->phone_number ?? $this->email ?? $this->social_handle;
    }

    // Scopes
    public function scopeByChannel($query, $channelId)
    {
        return $query->where('source_channel_id', $channelId);
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // Methods
    public function incrementConversationCount()
    {
        $this->increment('total_conversations');
        $this->update(['last_active_at' => now()]);
        return $this;
    }

    public function upsertFromMessage($messageData)
    {
        $identifier = $messageData['sender_id'] ?? '';
        
        // Find or create contact by identifier (phone/email/social)
        $contact = self::firstOrCreate(
            [
                'tenant_id' => $messageData['tenant_id'],
                'phone_number' => $messageData['phone'] ?? null,
                'email' => $messageData['email'] ?? null,
            ],
            [
                'name' => $messageData['sender_name'] ?? null,
                'source_channel_id' => $messageData['channel_id'],
                'avatar_url' => $messageData['avatar'] ?? null,
                'tags' => [],
                'custom_fields' => [],
                'metadata' => ['source' => 'message_sync'],
            ]
        );

        $contact->update([
            'last_active_at' => now(),
            'name' => $messageData['sender_name'] ?? $contact->name,
            'avatar_url' => $messageData['avatar'] ?? $contact->avatar_url,
        ]);

        return $contact;
    }
}
