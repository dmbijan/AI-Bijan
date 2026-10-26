<?php

/**
 * Cipta pengguna ujian pembangunan + tenant SDN + keahlian pemilik.
 * HANYA untuk DB pembangunan. Idempoten.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Services\TenantProvisioner;
use App\Support\Tenancy\TenantContext;

// 1. Pengguna
$u = User::where('email', 'ujian@bijan.test')->first();

if ($u === null) {
    $u = User::create([
        'name' => 'Ujian ADE',
        'email' => 'ujian@bijan.test',
        'password' => bcrypt('Password12345'),
    ]);
    echo "USER CREATED: {$u->id}" . PHP_EOL;
} else {
    $u->password = bcrypt('Password12345');
    $u->save();
    echo "USER OK: {$u->id}" . PHP_EOL;
}

// 2. Tenant — guna TenantProvisioner (peranan + langganan + pemilik)
$t = Tenant::where('slug', 'syarikat-ujian-ade')->first();

if ($t === null) {
    $t = app(TenantProvisioner::class)->create('Syarikat Ujian ADE', $u, 'enterprise');
    echo "TENANT CREATED: {$t->id}" . PHP_EOL;
} else {
    echo "TENANT OK: {$t->id}" . PHP_EOL;
}

// 3. Naik taraf ke enterprise (semua modul) untuk pengesahan UI
$ctx = app(TenantContext::class);
$ctx->set($t);

$ent = Plan::where('code', 'enterprise')->firstOrFail();
$svc = app(EntitlementService::class);
$svc->tukarPlan($t->id, $ent->id);

echo 'MODUL AKTIF: ' . count($svc->modulAktif($t->id)) . PHP_EOL;
echo 'ADE DIBENARKAN: ' . ($svc->bolehModul($t->id, 'ade') ? 'YA' : 'TIDAK') . PHP_EOL;
echo 'LOGIN: ujian@bijan.test / Password12345' . PHP_EOL;

$ctx->clear();
