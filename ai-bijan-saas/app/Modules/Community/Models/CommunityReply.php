<?php

namespace App\Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReply extends Model
{
    protected $table = 'community_replies';

    protected $fillable = [
        'tenant_id',
        'community_post_id',
        'author_user_id',
        'body',
        'like_count',
        'is_accepted',
        'status',
    ];

    protected $casts = [
        'author_user_id' => 'integer',
        'like_count' => 'integer',
        'is_accepted' => 'boolean',
    ];

    protected $attributes = [
        'like_count' => 0,
        'is_accepted' => false,
        'status' => 'published',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }
}
