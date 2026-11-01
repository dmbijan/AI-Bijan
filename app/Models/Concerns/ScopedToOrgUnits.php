<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\DataScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * OAM: tapis rekod ikut skop data ahli (cawangan/jabatan). Model menetapkan:
 *   protected static string $orgScopeColumn = 'register_id';  // lajur yang ditapis
 *   protected static string $orgScopeKind = 'registers';      // units | warehouses | registers | employees
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
    }
}
