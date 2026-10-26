<?php

namespace App\Modules\LiveSupport\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSupportCall extends Model
{
    use BelongsToTenant;

    protected $table = 'live_support_calls';

    public const STATUS_RINGING = 'ringing';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_AI_HANDLING = 'ai_handling';
    public const STATUS_HUMAN_HANDLING = 'human_handling';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED = 'missed';
    public const STATUS_FAILED = 'failed';

    public const HANDLER_AI = 'ai';
    public const HANDLER_HUMAN = 'human';

    protected $fillable = [
        'tenant_id',
        'live_support_channel_id',
        'external_call_id',
        'direction',
        'caller_number',
        'caller_name',
        'status',
        'handler_type',
        'handler_user_id',
        'ai_summary',
        'ai_actions',
        'sentiment',
        'escalation_reason',
        'recording_url',
        'duration_seconds',
        'confidence_avg',
        'started_at',
        'answered_at',
        'ended_at',
        'meta',
    ];

    protected $casts = [
        'ai_actions' => 'array',
        'meta' => 'array',
        'handler_user_id' => 'integer',
        'duration_seconds' => 'integer',
        'confidence_avg' => 'float',
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $attributes = [
        'direction' => 'inbound',
        'status' => self::STATUS_RINGING,
        'handler_type' => self::HANDLER_AI,
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(LiveSupportChannel::class, 'live_support_channel_id');
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(LiveSupportTranscript::class, 'live_support_call_id');
    }

    public function aktif(): bool
    {
        return in_array($this->status, [
            self::STATUS_RINGING,
            self::STATUS_ANSWERED,
            self::STATUS_AI_HANDLING,
            self::STATUS_HUMAN_HANDLING,
        ], true);
    }

    public function perluEskalasi(): bool
    {
        return $this->handler_type === self::HANDLER_AI
            && ($this->sentiment === 'negative' || $this->escalation_reason !== null);
    }
}
