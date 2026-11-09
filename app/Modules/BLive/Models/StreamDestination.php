<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stream destination - satu sasaran penstriman bagi satu sesi live.
 *
 * Setiap platform mempunyai encoder profile, bitrate dan status
 * sendiri. Rahsia (stream key / OAuth token) TIDAK disimpan di sini
 * dalam teks biasa - hanya `credential_reference` yang konsisten.
 */
class StreamDestination extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_stream_destinations';

    public const PLATFORM_YOUTUBE = 'youtube';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const PLATFORM_TIKTOK = 'tiktok';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONNECTING = 'connecting';

    public const STATUS_LIVE = 'live';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'live_session_id',
        'platform',
        'channel_name',
        'channel_reference',
        'credential_reference',
        'stream_status',
        'ingest_url',
        'stream_key_reference',
        'bitrate_kbps',
        'resolution',
        'orientation',
        'fps',
        'last_error',
        'reconnect_count',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'bitrate_kbps' => 'integer',
        'fps' => 'integer',
        'reconnect_count' => 'integer',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    protected $attributes = [
        'stream_status' => self::STATUS_PENDING,
        'bitrate_kbps' => 4500,
        'resolution' => '1920x1080',
        'orientation' => 'landscape',
        'fps' => 30,
        'reconnect_count' => 0,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    /**
     * Adakah sasaran ini sedang menyiarkan?
     */
    public function menyiarkan(): bool
    {
        return $this->stream_status === self::STATUS_LIVE;
    }

    /**
     * Adakah platform ini disokong dan diaktifkan pada konfigurasi?
     */
    public function platformDisokong(): bool
    {
        $cfg = config('blive.platforms.'.$this->platform);

        return is_array($cfg) && ($cfg['enabled'] ?? false) === true;
    }
}
