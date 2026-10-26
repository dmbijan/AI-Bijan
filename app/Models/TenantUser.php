<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenantUser extends Model
{
    use BelongsToTenant;

    protected $fillable = ['user_id', 'status', 'invited_by', 'invite_token_hash', 'invite_expires_at', 'joined_at'];

    protected $hidden = ['invite_token_hash'];

    protected $casts = ['invite_expires_at' => 'datetime', 'joined_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_tenant_user');
    }
}
