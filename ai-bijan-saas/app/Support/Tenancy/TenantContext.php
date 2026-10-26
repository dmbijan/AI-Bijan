<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use App\Models\TenantUser;
use RuntimeException;

/** Tenant semasa untuk permintaan ini. Ditetapkan HANYA oleh middleware selepas keahlian disahkan. */
class TenantContext
{
    private ?Tenant $tenant = null;

    private ?TenantUser $membership = null;

    private ?array $permissions = null;

    public function set(Tenant $tenant, ?TenantUser $membership = null, ?array $permissions = null): void
    {
        $this->tenant = $tenant;
        $this->membership = $membership;
        $this->permissions = $permissions;
    }

    /** Akses sokongan Super Admin (tiada keahlian): kebenaran baca terhad sahaja. */
    public function isSupportAccess(): bool
    {
        return $this->tenant !== null && $this->membership === null && $this->permissions !== null;
    }

    public function clear(): void
    {
        $this->tenant = $this->membership = null;
        $this->permissions = null;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): Tenant
    {
        return $this->tenant ?? throw new RuntimeException('Tiada tenant aktif.');
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function membership(): ?TenantUser
    {
        return $this->membership;
    }

    /** Kunci kebenaran pengguna dalam tenant ini. */
    public function permissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }
        if (! $this->membership || $this->membership->status !== 'active') {
            return $this->permissions = [];
        }

        return $this->permissions = $this->membership->roles()->with('permissions:id,key')->get()
            ->flatMap(fn ($r) => $r->permissions->pluck('key'))->unique()->values()->all();
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
