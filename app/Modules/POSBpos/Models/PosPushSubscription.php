<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Langganan push notification — untuk amaran pesanan sedia kepada PWA.
 *
 * Berasal daripada B-POS ciri `push_subscriptions`.
 */
class PosPushSubscription extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_push_subscriptions';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'device_key',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'user_agent',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'content_encoding' => 'aesgcm',
    ];

    protected $hidden = [
        'auth_token',
        'public_key',
    ];
}
