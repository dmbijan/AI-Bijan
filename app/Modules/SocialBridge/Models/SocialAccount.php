<?php

namespace App\Modules\SocialBridge\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Akaun sosial yang disambungkan — 10 platform.
 *
 * Berasal daripada Post Bridge (post-bridge.com).
 */
class SocialAccount extends Model
{
    use BelongsToTenant;

    protected $table = 'social_accounts';

    public const PLATFORM_X = 'x';

    public const PLATFORM_INSTAGRAM = 'instagram';

    public const PLATFORM_LINKEDIN = 'linkedin';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const PLATFORM_TIKTOK = 'tiktok';

    public const PLATFORM_YOUTUBE = 'youtube';

    public const PLATFORM_BLUESKY = 'bluesky';

    public const PLATFORM_THREADS = 'threads';

    public const PLATFORM_PINTEREST = 'pinterest';

    public const PLATFORM_GOOGLE_BUSINESS = 'google_business';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'tenant_id',
        'platform',
        'account_name',
        'handle',
        'external_id',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'status',
        'is_brand',
        'last_posted_at',
        'settings',
    ];

    protected $casts = [
        'scopes' => 'array',
        'settings' => 'array',
        'token_expires_at' => 'datetime',
        'last_posted_at' => 'datetime',
        'is_brand' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'is_brand' => false,
    ];

    public function aktif(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->token_expires_at === null || $this->token_expires_at->isFuture());
    }

    public static function senaraiPlatform(): array
    {
        return [
            self::PLATFORM_X => 'X (Twitter)',
            self::PLATFORM_INSTAGRAM => 'Instagram',
            self::PLATFORM_LINKEDIN => 'LinkedIn',
            self::PLATFORM_FACEBOOK => 'Facebook',
            self::PLATFORM_TIKTOK => 'TikTok',
            self::PLATFORM_YOUTUBE => 'YouTube',
            self::PLATFORM_BLUESKY => 'Bluesky',
            self::PLATFORM_THREADS => 'Threads',
            self::PLATFORM_PINTEREST => 'Pinterest',
            self::PLATFORM_GOOGLE_BUSINESS => 'Google Business',
        ];
    }
}
