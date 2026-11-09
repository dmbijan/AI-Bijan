<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Live control - jejak audit setiap arahan kawalan.
 *
 * Setiap tindakan kawalan (takeover, pause, resume, speak, stop)
 * direkod supaya operator boleh mengaudit siapa mengawal hos AI
 * pada bila-bila masa.
 */
class LiveControl extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_live_controls';

    public const TYPE_TAKEOVER = 'takeover';

    public const TYPE_RELEASE = 'release';

    public const TYPE_PAUSE_AI = 'pause_ai';

    public const TYPE_RESUME_AI = 'resume_ai';

    public const TYPE_SPEAK = 'speak';

    public const TYPE_STOP = 'emergency_stop';

    public const TYPE_SCENE = 'scene_change';

    public const ISSUED_BY_AI = 'ai';

    public const ISSUED_BY_HUMAN = 'human';

    public const ISSUED_BY_SYSTEM = 'system';

    protected $fillable = [
        'tenant_id',
        'live_session_id',
        'control_type',
        'issued_by',
        'user_id',
        'payload',
        'result',
        'approved',
        'latency_ms',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'approved' => 'boolean',
        'latency_ms' => 'integer',
    ];

    protected $attributes = [
        'issued_by' => self::ISSUED_BY_SYSTEM,
        'approved' => false,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }
}
