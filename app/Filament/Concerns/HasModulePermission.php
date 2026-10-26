<?php

namespace App\Filament\Concerns;

use App\Support\Tenancy\TenantContext;

/**
 * Kebenaran Filament berdasarkan permission `perm:` sedia ada.
 *
 * ⚠️ Prinsip keselamatan: menu tersembunyi BUKAN kawalan keselamatan.
 * Filament `can*()` mengawal UI; API tetap dikawal `perm:` + `module:`.
 * Kedua-duanya mesti selari.
 *
 * Cara guna — resource MESTI implement `permissionNamespace()`:
 *
 *   class ExpenseResource extends Resource
 *   {
 *       use HasModulePermission;
 *
 *       protected static function permissionNamespace(): string
 *       {
 *           return 'expense'; // → expense.view / expense.manage
 *       }
 *   }
 *
 * Trait menyediakan:
 *   canViewAny / canView   → `<ns>.view`
 *   canCreate / canEdit / canDelete / canDeleteAny → `<ns>.manage`
 *   shouldRegisterNavigation → sama seperti canViewAny
 */
trait HasModulePermission
{
    /** Namespace permission — contoh 'expense', 'orders', 'crm'. */
    abstract protected static function permissionNamespace(): string;

    protected static function bolehLihat(): bool
    {
        return static::semakKebenaran(static::permissionNamespace().'.view');
    }

    protected static function bolehUrus(): bool
    {
        // ⚠️ Sesetengah namespace hanya mempunyai `.view` (cth. `brainhub`,
        // `analytics`). Jika `.manage` tiada dalam sistem, `.view` menjadi
        // asas — jika tidak, borang cipta sentiasa 403 walaupun pemilik
        // mempunyai akses penuh kepada modul tersebut.
        if (static::permissionWujud(static::permissionNamespace().'.manage')) {
            return static::semakKebenaran(static::permissionNamespace().'.manage');
        }

        return static::bolehLihat();
    }

    /** Adakah permission ini wujud dalam jadual `permissions`? */
    protected static function permissionWujud(string $permission): bool
    {
        static $cache = [];

        return $cache[$permission] ??= \App\Models\Permission::withoutGlobalScopes()
            ->where('key', $permission)
            ->exists();
    }

    public static function canViewAny(): bool
    {
        return static::bolehLihat();
    }

    public static function canView($record): bool
    {
        return static::bolehLihat();
    }

    public static function canCreate(): bool
    {
        return static::bolehUrus();
    }

    public static function canEdit($record): bool
    {
        return static::bolehUrus();
    }

    public static function canDelete($record): bool
    {
        return static::bolehUrus();
    }

    public static function canDeleteAny(): bool
    {
        return static::bolehUrus();
    }

    /** Sembunyikan dari navigasi jika tiada kebenaran lihat. */
    public static function shouldRegisterNavigation(): bool
    {
        return static::bolehLihat();
    }

    /**
     * Semak permission dalam TenantContext + entitlemen modul.
     *
     * ⚠️ Menu tersembunyi BUKAN kawalan keselamatan, tetapi menu yang
     * membawa kepada 403 ialah UX buruk. Kami semak KEDUA-DUA:
     *   1. RBAC — adakah pengguna mempunyai `<ns>.view`?
     *   2. Entitlemen — adakah plan tenant menyertakan modul ini?
     *
     * Tiada konteks tenant → tolak (deny-by-default).
     */
    protected static function semakKebenaran(string $permission): bool
    {
        $ctx = app(TenantContext::class);

        if (! $ctx->has()) {
            return false;
        }

        if (! $ctx->can($permission)) {
            return false;
        }

        // Gate modul: plan tenant mesti menyertakan modul ini.
        try {
            $modul = \App\Modules\Entitlement\Services\ModuleGate::moduleKey(
                \Illuminate\Support\Str::before($permission, '.')
            );

            return app(\App\Modules\Entitlement\Services\EntitlementService::class)
                ->bolehModul((int) $ctx->id(), $modul);
        } catch (\Throwable $e) {
            // Jika servis entitlemen tidak tersedia, jangan sekat akses RBAC.
            return true;
        }
    }
}
