<?php

namespace App\Modules\OpenWhispr\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Sesi diktasi — satu rakaman suara yang diproses menjadi teks.
 *
 * inspired oleh https://github.com/OpenWhispr/openwhispr
 */
class DictationSession extends Model
{
    use BelongsToTenant;

    protected $table = 'dictation_sessions';

    public const STATUS_RECORDING = 'recording';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'language',
        'engine',
        'model',
        'status',
        'audio_path',
        'audio_size_bytes',
        'duration_seconds',
        'raw_text',
        'processed_text',
        'confidence',
        'word_count',
        'segments',
        'target_type',
        'target_id',
        'error_message',
        'processing_ms',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'segments' => 'array',
        'audio_size_bytes' => 'integer',
        'duration_seconds' => 'float',
        'confidence' => 'float',
        'word_count' => 'integer',
        'processing_ms' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_RECORDING,
        'language' => 'ms',
        'engine' => 'whisper',
        'confidence' => 0,
        'word_count' => 0,
        'processing_ms' => 0,
    ];

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
