<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Moderation event - rekod moderasi untuk komen/ucapan live.
 */
class ModerationEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_moderation_events';

    public const SEVERITY_LOW = 'low';

    public const SEVERITY_MEDIUM = 'medium';

    public const SEVERITY_HIGH = 'high';

    public const SEVERITY_CRITICAL = 'critical';

    public const CATEGORY_SPAM = 'spam';

    public const CATEGORY_ABUSE = 'abuse';

    public const CATEGORY_SCAM = 'scam';

    public const CATEGORY_PROFANITY = 'profanity';

    public const CATEGORY_OFFENSIVE = 'offensive';

    public const CATEGORY_SENSITIVE = 'sensitive';

    public const SOURCE_AI = 'ai';

    public const SOURCE_HUMAN = 'human';

    public const SOURCE_ESCALATION = 'escalation';

    protected $fillable = [
        'tenant_id',
        'live_session_id',
        'severity',
        'category',
        'content',
        'reason',
        'is_blocked',
        'source',
        'moderated_by',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    protected $attributes = [
        'severity' => self::SEVERITY_LOW,
        'category' => self::CATEGORY_SPAM,
        'is_blocked' => false,
        'source' => self::SOURCE_AI,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    /**
     * Adakah moderasi ini memerlukan tindakan manusia dengan segera?
     */
    public function kritikal(): bool
    {
        return in_array($this->severity, [self::SEVERITY_HIGH, self::SEVERITY_CRITICAL], true);
    }
}
