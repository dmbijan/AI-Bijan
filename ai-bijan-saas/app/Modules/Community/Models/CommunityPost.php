<?php

namespace App\Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    protected $table = 'community_posts';

    public const TYPE_DISCUSSION = 'discussion';
    public const TYPE_QUESTION = 'question';
    public const TYPE_SHOWCASE = 'showcase';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_IDEA = 'idea';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_REMOVED = 'removed';

    protected $fillable = [
        'tenant_id',
        'community_space_id',
        'author_user_id',
        'post_type',
        'title',
        'body',
        'attachments',
        'tags',
        'status',
        'view_count',
        'like_count',
        'reply_count',
        'is_pinned',
        'is_solved',
        'accepted_reply_id',
        'meta',
    ];

    protected $casts = [
        'attachments' => 'array',
        'tags' => 'array',
        'meta' => 'array',
        'author_user_id' => 'integer',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'reply_count' => 'integer',
        'is_pinned' => 'boolean',
        'is_solved' => 'boolean',
    ];

    protected $attributes = [
        'post_type' => self::TYPE_DISCUSSION,
        'status' => self::STATUS_PUBLISHED,
        'view_count' => 0,
        'like_count' => 0,
        'reply_count' => 0,
        'is_pinned' => false,
        'is_solved' => false,
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(CommunitySpace::class, 'community_space_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CommunityReply::class, 'community_post_id');
    }

    public function tersiar(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
