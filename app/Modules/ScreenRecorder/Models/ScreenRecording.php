<?php

namespace App\Modules\ScreenRecorder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rakaman skrin — satu video rakaman skrin + audio.
 *
 * inspired oleh https://github.com/CapSoftware/Cap
 */
class ScreenRecording extends Model
{
    use BelongsToTenant;

    protected $table = 'screen_recordings';

    public const STATUS_UPLOADING = 'uploading';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    public const PRIVACY_PUBLIC = 'public';

    public const PRIVACY_UNLISTED = 'unlisted';

    public const PRIVACY_PRIVATE = 'private';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'description',
        'recording_path',
        'recording_url',
        'thumbnail_path',
        'thumbnail_url',
        'status',
        'duration_seconds',
        'file_size_bytes',
        'resolution',
        'frame_rate',
        'has_audio',
        'has_mouse_cursor',
        'privacy',
        'share_token',
        'view_count',
        'download_count',
        'comment_count',
        'error_message',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'duration_seconds' => 'float',
        'file_size_bytes' => 'integer',
        'frame_rate' => 'integer',
        'has_audio' => 'boolean',
        'has_mouse_cursor' => 'boolean',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'comment_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_UPLOADING,
        'privacy' => self::PRIVACY_PRIVATE,
        'duration_seconds' => 0,
        'file_size_bytes' => 0,
        'frame_rate' => 30,
        'has_audio' => true,
        'has_mouse_cursor' => true,
        'view_count' => 0,
        'download_count' => 0,
        'comment_count' => 0,
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(RecordingComment::class, 'recording_id');
    }

    /**
     * Adakah rakuman boleh dikongsi secara awam?
     */
    public function bolehKongsi(): bool
    {
        return $this->privacy !== self::PRIVACY_PRIVATE && $this->status === self::STATUS_READY;
    }

    /**
     * @return array<string, string>
     */
    public static function privasi(): array
    {
        return [
            self::PRIVACY_PUBLIC => 'Awam',
            self::PRIVACY_UNLISTED => 'Tidak Tersenarai',
            self::PRIVACY_PRIVATE => 'Peribadi',
        ];
    }
}
