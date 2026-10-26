<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentComment extends Model
{
    use BelongsToTenant;

    protected $table = 'content_comments';

    public const STATUS_NEW = 'new';
    public const STATUS_FLAGGED = 'flagged';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_RESOLVED = 'resolved';

    public const SENTIMENT_POSITIVE = 'positive';
    public const SENTIMENT_NEUTRAL = 'neutral';
    public const SENTIMENT_NEGATIVE = 'negative';
    public const SENTIMENT_TOXIC = 'toxic';

    protected $fillable = [
        'tenant_id',
        'content_platform_id',
        'content_piece_id',
        'external_id',
        'platform_post_id',
        'author_name',
        'author_id',
        'author_avatar',
        'body',
        'sentiment',
        'sentiment_score',
        'status',
        'ai_reply_suggestion',
        'ai_confidence',
        'reply_body',
        'replied_at',
        'like_count',
        'posted_at',
        'is_from_ai',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sentiment_score' => 'float',
        'ai_confidence' => 'float',
        'like_count' => 'integer',
        'posted_at' => 'datetime',
        'replied_at' => 'datetime',
        'is_from_ai' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
        'like_count' => 0,
        'is_from_ai' => false,
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(ContentPlatform::class, 'content_platform_id');
    }

    public function piece(): BelongsTo
    {
        return $this->belongsTo(ContentPiece::class, 'content_piece_id');
    }

    public function perlukanTindakan(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_FLAGGED], true);
    }
}
