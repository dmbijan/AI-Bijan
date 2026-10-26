<?php

/**
 * Segerakkan permission baharu daripada `config/aibijan.php` ke DB,
 * kemudian berikan SEMUA kebenaran kepada peranan `tenant_owner` setiap tenant.
 *
 * ⚠️ Hanya untuk DB pembangunan / migrasi. `updateOrCreate` = idempoten.
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\TenantProvisioner;
use App\Support\Tenancy\TenantContext;

// 1. Segerakkan permission baharu
TenantProvisioner::syncPermissions();

$jumlah = Permission::withoutGlobalScopes()->count();
echo "Permission dalam DB: {$jumlah}".PHP_EOL;

// 2. Beri semua kebenaran kepada tenant_owner setiap tenant
$semuaId = Permission::withoutGlobalScopes()->pluck('id');

foreach (Tenant::withoutGlobalScopes()->get() as $tenant) {
    $ctx = app(TenantContext::class);
    $ctx->set($tenant, TenantUser::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->first());

    $owner = Role::query()->where('key', 'tenant_owner')->first();

    if ($owner) {
        $owner->permissions()->sync($semuaId);
        echo "Tenant {$tenant->id} ({$tenant->name}) — tenant_owner kini ada {$owner->permissions()->count()} kebenaran".PHP_EOL;
    } else {
        echo "Tenant {$tenant->id} — ⚠️ tiada peranan tenant_owner".PHP_EOL;
    }

    $ctx->clear();
    // Buang cache entitlement supaya perubahan serta-merta.
    app(\App\Modules\Entitlement\Services\EntitlementService::class)->clearCache();
}

echo 'SIAP'.PHP_EOL;
