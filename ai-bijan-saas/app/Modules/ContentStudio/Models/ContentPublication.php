<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPublication extends Model
{
    use BelongsToTenant;

    protected $table = 'content_publications';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PUBLISHING = 'publishing';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'tenant_id',
        'content_piece_id',
        'content_platform_id',
        'status',
        'platform_post_id',
        'platform_url',
        'platform_payload',
        'error_message',
        'attempts',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'platform_payload' => 'array',
        'attempts' => 'integer',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_QUEUED,
        'attempts' => 0,
    ];

    public function piece(): BelongsTo
    {
        return $this->belongsTo(ContentPiece::class, 'content_piece_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(ContentPlatform::class, 'content_platform_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
