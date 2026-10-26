<?php

namespace App\Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunitySpace extends Model
{
    protected $table = 'community_spaces';

    public const TYPE_FORUM = 'forum';
    public const TYPE_GROUP = 'group';
    public const TYPE_ACADEMY = 'academy';
    public const TYPE_SHOWCASE = 'showcase';
    public const TYPE_HELP = 'help';

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_MEMBERS = 'members';
    public const VISIBILITY_PRIVATE = 'private';

    protected $fillable = [
        'tenant_id',
        'slug',
        'name',
        'description',
        'space_type',
        'visibility',
        'owner_user_id',
        'member_count',
        'post_count',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'member_count' => 'integer',
        'post_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'space_type' => self::TYPE_FORUM,
        'visibility' => self::VISIBILITY_PUBLIC,
        'member_count' => 0,
        'post_count' => 0,
        'is_active' => true,
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class, 'community_space_id');
    }

    public function awam(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }
}
