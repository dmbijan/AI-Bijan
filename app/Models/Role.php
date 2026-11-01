<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use BelongsToTenant;

    protected $fillable = ['key', 'name', 'is_system', 'description', 'parent_id', 'status'];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /** Kebenaran yang DITOLAK secara jelas: menang ke atas apa jua kebenaran yang diberi atau diwarisi. */
    public function denies(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission_denies');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(TenantUser::class, 'role_tenant_user');
    }

    public function limits()
    {
        return $this->hasMany(RoleLimit::class);
    }
}
