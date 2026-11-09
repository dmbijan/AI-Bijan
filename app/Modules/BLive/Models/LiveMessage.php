<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Live message - satu utterance dalam sesi live.
 *
 * `role`:
 *   - host    : apa yang hos AI (atau manusia) cakap
 *   - audience: komen penonton
 *   - system  : notifikasi dalaman (joining, moderation)
 *
 * `source` menandakan siapa yang mencetuskan mesej host:
 *   ai, human, script, escalation
 */
class LiveMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_live_messages';

    public const ROLE_HOST = 'host';

    public const ROLE_AUDIENCE = 'audience';

    public const ROLE_SYSTEM = 'system';

    public const SOURCE_AI = 'ai';

    public const SOURCE_HUMAN = 'human';

    public const SOURCE_SCRIPT = 'script';

    public const SOURCE_ESCALATION = 'escalation';

    protected $fillable = [
        'tenant_id',
        'live_session_id',
        'role',
        'source',
        'language',
        'content',
        'content_length',
        'intent',
        'confidence',
        'is_question',
        'is_answered',
        'escalated_to_human',
        'audience_score',
        'platform',
        'external_id',
        'tokens_used',
        'cost_rm',
    ];

    protected $casts = [
        'content_length' => 'integer',
        'confidence' => 'float',
        'is_question' => 'boolean',
        'is_answered' => 'boolean',
        'escalated_to_human' => 'boolean',
        'audience_score' => 'integer',
        'tokens_used' => 'integer',
        'cost_rm' => 'float',
    ];

    protected $attributes = [
        'role' => self::ROLE_HOST,
        'source' => self::SOURCE_AI,
        'language' => 'ms',
        'content_length' => 0,
        'is_question' => false,
        'is_answered' => false,
        'escalated_to_human' => false,
        'audience_score' => 0,
        'tokens_used' => 0,
        'cost_rm' => 0,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }
}
