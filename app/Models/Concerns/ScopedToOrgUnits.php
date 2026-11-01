<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\DataScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * OAM: tapis rekod ikut skop data ahli (cawangan/jabatan). Model menetapkan:
 *   protected static string $orgScopeColumn = 'register_id';  // lajur yang ditapis
 *   protected static string $orgScopeKind = 'registers';      // units | units_shared | warehouses | registers | employees
 * Untuk lajur org_unit_id, rekod baharu oleh ahli berskop diberi unit utama ahli secara automatik.
 * units_shared: rekod tanpa unit dikira data dikongsi (cth. pelanggan) dan kelihatan kepada semua.
 * Tiada kesan untuk pemilik / ahli berskop seluruh organisasi / kerja sistem.
 */
trait ScopedToOrgUnits
{
    public static function bootScopedToOrgUnits(): void
    {
        static::addGlobalScope('org_unit', function (Builder $q) {
            $m = $q->getModel();
            app(DataScope::class)->apply($q, $m->getTable().'.'.static::$orgScopeColumn, static::$orgScopeKind);
        });

        if (static::$orgScopeColumn === 'org_unit_id') {
            static::creating(function ($m) {
                if ($m->org_unit_id === null) {
                    $m->org_unit_id = app(DataScope::class)->primaryUnit();
                }
            });
        }
    }
}
