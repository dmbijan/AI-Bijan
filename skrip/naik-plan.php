<?php

/**
 * Naik taraf tenant ujian ke plan `enterprise` (semua modul) untuk
 * pengesahan visual UI. HANYA untuk DB pembangunan.
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Support\Tenancy\TenantContext;

$tenantId = 1;
$enterprise = Plan::query()->where('code', 'enterprise')->firstOrFail();

// ⚠️ `BelongsToTenant` menolak `create()` tanpa konteks — tetapkan dahulu.
$ctx = app(TenantContext::class);
$ctx->set(
    Tenant::find($tenantId),
    TenantUser::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->first()
);

$svc = app(EntitlementService::class);
$svc->tukarPlan($tenantId, $enterprise->id);

$tenant = Tenant::find($tenantId);

echo 'Tenant: '.$tenant->name.PHP_EOL;
echo 'Plan: '.Plan::find($tenant->plan_id)->code.PHP_EOL;
echo 'Modul aktif: '.count($svc->modulAktif($tenantId)).PHP_EOL;

$ctx->clear();
