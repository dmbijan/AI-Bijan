<?php

namespace App\Modules\SocialBridge\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sasaran siaran — satu catatan ke satu akaun/platform.
 *
 * Membolehkan status berbeza setiap platform (contoh: TikTok berjaya,
 * Instagram gagal).
 */
class SocialPostTarget extends Model
{
    use BelongsToTenant;

    protected $table = 'social_post_targets';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'tenant_id',
        'social_post_id',
        'social_account_id',
        'platform',
        'status',
        'external_post_id',
        'external_url',
        'content',
        'attempts',
        'last_error',
        'published_at',
        'metrics',
    ];

    protected $casts = [
        'metrics' => 'array',
        'attempts' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'attempts' => 0,
    ];

    public function catatan(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    public function akaun(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }
}
