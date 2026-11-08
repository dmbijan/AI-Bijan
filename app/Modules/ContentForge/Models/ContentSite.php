<?php

namespace App\Modules\ContentForge\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Laman WordPress yang disambungkan — untuk auto-post.
 *
 * Berasal daripada SEO Writing AI.
 */
class ContentSite extends Model
{
    use BelongsToTenant;

    protected $table = 'content_sites';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_ERROR = 'error';

    public const STATUS_DISCONNECTED = 'disconnected';

    protected $fillable = [
        'tenant_id',
        'name',
        'url',
        'platform',
        'username',
        'app_password',
        'default_category',
        'default_author_id',
        'auto_publish',
        'status',
        'last_published_at',
        'last_error',
    ];

    protected $casts = [
        'auto_publish' => 'boolean',
        'default_author_id' => 'integer',
        'last_published_at' => 'datetime',
    ];

    protected $hidden = [
        'app_password',
    ];

    protected $attributes = [
        'platform' => 'wordpress',
        'status' => self::STATUS_DISCONNECTED,
        'auto_publish' => false,
    ];

    public function bersambung(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }
}
