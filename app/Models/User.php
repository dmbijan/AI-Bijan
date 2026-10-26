<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone', 'locale'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'is_platform_admin'];

    /**
     * Akses panel Filament.
     *
     * Hanya pengguna aktif yang mempunyai sekurang-kurangnya satu
     * keahlian tenant aktif dibenarkan masuk.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->memberships()
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Tenant aktif daripada TenantContext, supaya $user->tenant_id berfungsi
     * dalam controller modul yang bergantung pada konteks tenant semasa.
     */
    public function getTenantIdAttribute(): ?int
    {
        return app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_platform_admin' => 'boolean',
            'is_active' => 'boolean',
            'must_set_password' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function memberships(): HasMany
    {
        // Keahlian merentas tenant: sengaja tanpa skop tenant (untuk pilih tenant semasa log masuk).
        return $this->hasMany(TenantUser::class)->withoutGlobalScope('tenant');
    }
}
