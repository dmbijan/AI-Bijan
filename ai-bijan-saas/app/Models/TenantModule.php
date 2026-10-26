<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'module', 'enabled', 'source', 'enabled_at', 'disabled_at', 'expires_at'];

    protected $casts = [
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
