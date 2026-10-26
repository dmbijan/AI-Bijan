<?php

namespace App\Modules\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $table = 'asset_transfers';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'from_user',
        'to_user',
        'from_location',
        'to_location',
        'reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the asset being transferred.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user transferring from.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'from_user');
    }

    /**
     * Get the user transferring to.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'to_user');
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope approved transfers only.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }
}
