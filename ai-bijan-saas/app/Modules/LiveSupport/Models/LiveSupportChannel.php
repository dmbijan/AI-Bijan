<?php

namespace App\Modules\LiveSupport\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSupportChannel extends Model
{
    use BelongsToTenant;

    protected $table = 'live_support_channels';

    public const TYPE_VOICE = 'voice';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CHAT = 'chat';
    public const TYPE_WHATSAPP_CALL = 'whatsapp_call';

    protected $fillable = [
        'tenant_id',
        'name',
        'channel_type',
        'provider',
        'inbound_number',
        'esim_iccid',
        'esim_provider',
        'config',
        'ai_enabled',
        'human_fallback',
        'ai_confidence_threshold',
        'status',
        'max_concurrent',
        'business_hours',
    ];

    protected $casts = [
        'config' => 'array',
        'business_hours' => 'array',
        'ai_enabled' => 'boolean',
        'human_fallback' => 'boolean',
        'ai_confidence_threshold' => 'float',
        'max_concurrent' => 'integer',
    ];

    protected $attributes = [
        'channel_type' => self::TYPE_VOICE,
        'ai_enabled' => true,
        'human_fallback' => true,
        'ai_confidence_threshold' => 0.75,
        'status' => 'active',
        'max_concurrent' => 5,
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(LiveSupportCall::class, 'live_support_channel_id');
    }

    public function aiBolehUrus(): bool
    {
        return $this->ai_enabled && $this->status === 'active';
    }
}
