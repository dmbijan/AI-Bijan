<?php

/** Semak kebenaran peranan tenant_owner vs namespace resource. */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Support\Tenancy\TenantContext;

$ctx = app(TenantContext::class);
$ctx->set(Tenant::find(1), TenantUser::withoutGlobalScope('tenant')->where('tenant_id', 1)->first());

$ada = $ctx->permissions();

echo 'Jumlah kebenaran: '.count($ada).PHP_EOL.PHP_EOL;

foreach (['scm', 'media_studio', 'vision', 'cod', 'hrms', 'assets', 'projects', 'helpdesk', 'marketing'] as $ns) {
    $view = in_array($ns.'.view', $ada, true);
    $manage = in_array($ns.'.manage', $ada, true);
    echo str_pad($ns, 16).' view='.($view ? 'YA ' : 'TIDAK').'  manage='.($manage ? 'YA' : 'TIDAK').PHP_EOL;
}

echo PHP_EOL.'=== SEMUA PREFIX ==='.PHP_EOL;
$prefix = [];
foreach ($ada as $k) {
    $prefix[explode('.', $k)[0]] = true;
}
echo implode(', ', array_keys($prefix)).PHP_EOL;

$ctx->clear();
