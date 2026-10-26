<?php

namespace App\Modules\Entitlement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Grant modul manual (add-on / trial / override platform admin).
 *
 * Berbeza daripada `tenant_modules` (yang cuma flag bool), grant ini
 * membawa sumber, tarikh luput dan feature flags.
 */
class EntitlementGrant extends Model
{
    protected $table = 'entitlement_grants';

    protected $fillable = [
        'tenant_id',
        'module',
        'source',
        'enabled',
        'enabled_at',
        'disabled_at',
        'expires_at',
        'feature_flags',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
        'expires_at' => 'datetime',
        'feature_flags' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    /**
     * Adakah grant ini masih aktif?
     */
    public function aktif(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
