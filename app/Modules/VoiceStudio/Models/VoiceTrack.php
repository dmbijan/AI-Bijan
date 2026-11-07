<?php

namespace App\Modules\VoiceStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu rekod sintesis suara (satu fail audio dijana).
 */
class VoiceTrack extends Model
{
    use BelongsToTenant;

    protected $table = 'voice_tracks';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'voice_profile_id',
        'engine',
        'text',
        'text_length',
        'audio_path',
        'audio_url',
        'duration_seconds',
        'file_size_bytes',
        'format',
        'status',
        'error_message',
        'cost_rm',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'text_length' => 'integer',
        'duration_seconds' => 'float',
        'file_size_bytes' => 'integer',
        'cost_rm' => 'float',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'format' => 'mp3',
        'text_length' => 0,
        'duration_seconds' => 0,
        'cost_rm' => 0,
    ];

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class, 'voice_profile_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
