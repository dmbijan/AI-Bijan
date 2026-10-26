<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

$semua = Permission::withoutGlobalScopes()->pluck('key')->all();

echo 'Jumlah permission dalam DB: '.count($semua).PHP_EOL.PHP_EOL;

foreach (['scm', 'media_studio', 'vision', 'cod', 'hrms', 'assets'] as $ns) {
    $padan = array_values(array_filter($semua, fn ($k) => str_starts_with($k, $ns.'.')));
    echo str_pad($ns, 16).' → '.count($padan).' kebenaran'.($padan ? ' (cth: '.$padan[0].')' : ' ⚠️ TIADA').PHP_EOL;
}

echo PHP_EOL.'=== SEMUA PREFIX DALAM DB ==='.PHP_EOL;
$prefix = [];
foreach ($semua as $k) {
    $prefix[explode('.', $k)[0]] = true;
}
echo implode(', ', array_keys($prefix)).PHP_EOL;
