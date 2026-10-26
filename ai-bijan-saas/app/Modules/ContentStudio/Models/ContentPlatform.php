<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPlatform extends Model
{
    use BelongsToTenant;

    protected $table = 'content_platforms';

    public const FACEBOOK = 'facebook';
    public const INSTAGRAM = 'instagram';
    public const TIKTOK = 'tiktok';
    public const LINKEDIN = 'linkedin';
    public const X = 'x';
    public const WHATSAPP = 'whatsapp';
    public const YOUTUBE = 'youtube';
    public const TELEGRAM = 'telegram';
    public const GOOGLE_BUSINESS = 'google_business';

    protected $fillable = [
        'tenant_id',
        'platform',
        'display_name',
        'account_id',
        'account_name',
        'credentials',
        'capabilities',
        'is_active',
        'auto_publish',
        'auto_reply',
        'max_daily_posts',
        'last_posted_at',
        'last_synced_at',
        'meta',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'capabilities' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'auto_publish' => 'boolean',
        'auto_reply' => 'boolean',
        'max_daily_posts' => 'integer',
        'last_posted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'auto_publish' => false,
        'auto_reply' => false,
        'max_daily_posts' => 10,
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(ContentComment::class, 'content_platform_id');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(ContentChat::class, 'content_platform_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(ContentPublication::class, 'content_platform_id');
    }

    public function boleh(string $keupayaan): bool
    {
        return in_array($keupayaan, $this->capabilities ?? [], true);
    }
}
