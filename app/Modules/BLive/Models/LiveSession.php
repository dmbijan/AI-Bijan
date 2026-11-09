<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Live session - satu sesi=live host.
 *
 * Status flow:
 *   draft -> scheduled -> live -> paused -> ended
 *
 * `session_mode` menentukan siapa bercakap:
 *   - ai_assisted  : AI bercakap, manusia boleh ambil alih
 *   - ai_autonomous : AI bercakap sepenuhnya (still boleh dihentikan)
 *   - human_led    : manusia bercakap, AI hanya cadang
 */
class LiveSession extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_live_sessions';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_LIVE = 'live';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_ENDED = 'ended';

    public const STATUS_FAILED = 'failed';

    public const MODE_AI_ASSISTED = 'ai_assisted';

    public const MODE_AI_AUTONOMOUS = 'ai_autonomous';

    public const MODE_HUMAN_LED = 'human_led';

    public const AUTONOMY_SUPERVISED = 'supervised';

    public const AUTONOMY_FULL = 'full';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'avatar_id',
        'host_personality_id',
        'title',
        'session_mode',
        'autonomy_level',
        'status',
        'resolution',
        'orientation',
        'fps',
        'bitrate_kbps',
        'language',
        'product_ids',
        'settings',
        'render_minutes_used',
        'comments_handled',
        'speech_count',
        'cost_rm',
        'scheduled_at',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'settings' => 'array',
        'fps' => 'integer',
        'bitrate_kbps' => 'integer',
        'render_minutes_used' => 'integer',
        'comments_handled' => 'integer',
        'speech_count' => 'integer',
        'cost_rm' => 'float',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $attributes = [
        'session_mode' => self::MODE_AI_ASSISTED,
        'autonomy_level' => self::AUTONOMY_SUPERVISED,
        'status' => self::STATUS_DRAFT,
        'resolution' => '1920x1080',
        'orientation' => 'landscape',
        'fps' => 30,
        'bitrate_kbps' => 4500,
        'language' => 'ms',
        'render_minutes_used' => 0,
        'comments_handled' => 0,
        'speech_count' => 0,
        'cost_rm' => 0,
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    public function personality(): BelongsTo
    {
        return $this->belongsTo(HostPersonality::class, 'host_personality_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(StreamDestination::class, 'live_session_id');
    }

    public function controls(): HasMany
    {
        return $this->hasMany(LiveControl::class, 'live_session_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LiveMessage::class, 'live_session_id');
    }

    public function moderationEvents(): HasMany
    {
        return $this->hasMany(ModerationEvent::class, 'live_session_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(LiveMetric::class, 'live_session_id');
    }

    /**
     * Adakah sesi sedang berjalan (live atau paused)?
     */
    public function aktif(): bool
    {
        return in_array($this->status, [self::STATUS_LIVE, self::STATUS_PAUSED], true);
    }

    /**
     * Adakah sesi boleh bermula?
     */
    public function bolehMula(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    /**
     * Tempoh sesi dalam minit (0 jika belum bermula).
     */
    public function tempohMenit(): float
    {
        if ($this->started_at === null) {
            return 0.0;
        }

        $akhir = $this->ended_at ?: now();

        return round(($akhir->getTimestamp() - $this->started_at->getTimestamp()) / 60, 2);
    }
}
