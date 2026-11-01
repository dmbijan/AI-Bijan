<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenantUser extends Model
{
    use BelongsToTenant;

    protected $fillable = ['user_id', 'status', 'invited_by', 'invite_token_hash', 'invite_expires_at', 'joined_at',
        'expires_at', 'suspended_at', 'revoked_at', 'status_reason', 'employee_no'];

    protected $hidden = ['invite_token_hash'];

    protected $casts = ['invite_expires_at' => 'datetime', 'joined_at' => 'datetime', 'expires_at' => 'datetime', 'suspended_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accessScope()
    {
        return $this->hasOne(MembershipScope::class);
    }

    /** Status untuk paparan: invited, active, suspended, revoked, expired. */
    public function getStateAttribute(): string
    {
        if ($this->status === 'active' && $this->expires_at?->isPast()) {
            return 'expired';
        }
        if ($this->status === 'disabled') {
            return $this->revoked_at ? 'revoked' : 'suspended';
        }

        return $this->status;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_tenant_user');
    }
}
