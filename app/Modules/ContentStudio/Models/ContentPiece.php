<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPiece extends Model
{
    use BelongsToTenant;

    protected $table = 'content_pieces';

    public const STATUS_IDEA = 'idea';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'title',
        'body',
        'hook',
        'cta',
        'content_type',
        'status',
        'brand_profile_id',
        'media_assets',
        'hashtags',
        'ai_meta',
        'scheduled_at',
        'published_at',
        'view_count',
        'engagement_count',
        'comment_count',
        'share_count',
        'like_count',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'media_assets' => 'array',
        'hashtags' => 'array',
        'ai_meta' => 'array',
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'engagement_count' => 'integer',
        'comment_count' => 'integer',
        'share_count' => 'integer',
        'like_count' => 'integer',
    ];

    protected $attributes = [
        'content_type' => 'post',
        'status' => self::STATUS_DRAFT,
        'view_count' => 0,
        'engagement_count' => 0,
        'comment_count' => 0,
        'share_count' => 0,
        'like_count' => 0,
    ];

    public function brandProfile(): BelongsTo
    {
        return $this->belongsTo(ContentBrandProfile::class, 'brand_profile_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(ContentPublication::class, 'content_piece_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ContentComment::class, 'content_piece_id');
    }

    public function bolehPublish(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_SCHEDULED,
            self::STATUS_PUBLISHED,
        ], true);
    }

    public function engagementRate(): float
    {
        return $this->view_count > 0
            ? round($this->engagement_count / $this->view_count * 100, 2)
            : 0.0;
    }

    /**
     * Panjang post mengikut had platform.
     */
    public function melebihiHad(string $platform): bool
    {
        $had = match ($platform) {
            ContentPlatform::FACEBOOK => 63206,
            ContentPlatform::INSTAGRAM => 2200,
            ContentPlatform::TIKTOK => 4000,
            ContentPlatform::LINKEDIN => 3000,
            ContentPlatform::X => 280,
            ContentPlatform::WHATSAPP => 4096,
            default => 5000,
        };

        return mb_strlen((string) $this->body) > $had;
    }
}
