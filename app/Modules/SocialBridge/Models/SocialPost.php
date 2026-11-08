<?php

namespace App\Modules\SocialBridge\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catatan sosial — kandungan yang boleh dijadual dan disiarkan.
 *
 * Berasal daripada Post Bridge.
 */
class SocialPost extends Model
{
    use BelongsToTenant;

    protected $table = 'social_posts';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHING = 'publishing';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_TEXT = 'text';

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const TYPE_CAROUSEL = 'carousel';

    public const TYPE_STORY = 'story';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'body',
        'type',
        'media',
        'status',
        'scheduled_at',
        'published_at',
        'timezone',
        'per_platform_overrides',
        'tags',
        'created_by',
    ];

    protected $casts = [
        'media' => 'array',
        'per_platform_overrides' => 'array',
        'tags' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_TEXT,
        'status' => self::STATUS_DRAFT,
        'timezone' => 'Asia/Kuala_Lumpur',
    ];

    public function sasaran(): HasMany
    {
        return $this->hasMany(SocialPostTarget::class, 'social_post_id');
    }

    public function dijadual(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->scheduled_at !== null;
    }

    /**
     * Kandungan untuk satu platform (overrid atau badan utama).
     */
    public function untukPlatform(string $platform): string
    {
        return $this->per_platform_overrides[$platform] ?? $this->body;
    }
}
