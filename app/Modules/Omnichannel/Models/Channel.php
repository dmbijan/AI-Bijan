<?php

namespace App\Modules\Omnichannel\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'omnichannel_channels';

    protected $fillable = [
        'tenant_id',
        'name',
        'type', // whatsapp, email, facebook, instagram, telegram, live_chat, sms, twitter
        'config',
        'is_active',
        'is_primary',
        'display_name',
        'avatar_url',
        'phone_number',
        'email_address',
        'social_handle',
        'webhook_url',
        'webhook_secret',
        'rate_limit',
        'status',
        'last_sync_at',
        'metadata',
    ];

    protected $casts = [
        'config' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
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
    public function getTypeIconAttribute()
    {
        return [
            'whatsapp' => 'fab fa-whatsapp',
            'email' => 'fas fa-envelope',
            'facebook_messenger' => 'fab fa-facebook-messenger',
            'instagram' => 'fab fa-instagram',
            'telegram' => 'fab fa-telegram',
            'live_chat' => 'fas fa-comments',
            'sms' => 'fas fa-sms',
            'twitter_dm' => 'fab fa-twitter',
        ][$this->type] ?? 'fas fa-comment';
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'connected' => 'success',
            'disconnected' => 'danger',
            'connecting' => 'info',
            'error' => 'warning',
        ][$this->status] ?? 'secondary';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Methods
    public function connect()
    {
        $this->update(['status' => 'connecting']);
        
        // TODO: Implement connection logic per channel type
        // WhatsApp: Verify webhook, get access token
        // Email: Test SMTP connection
        // Social: OAuth flow
        
        $this->update(['status' => 'connected']);
        return true;
    }

    public function disconnect()
    {
        $this->update(['status' => 'disconnected']);
        return true;
    }

    public function testConnection()
    {
        // TODO: Implement connection test per channel type
        return ['status' => 'tested', 'timestamp' => now()];
    }
}
