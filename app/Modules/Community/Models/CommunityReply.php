<?php

namespace App\Modules\Community\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Balasan siaran komuniti.
 *
 * ⚠️ 27 Okt 2026: kolum `tenant_id` ada tetapi trait TIDAK dipasang → tiada
 * skop global. Trait dipasang di sini.
 */
class CommunityReply extends Model
{
    use BelongsToTenant;
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
