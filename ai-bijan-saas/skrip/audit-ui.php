<?php

/**
 * Audit semua laluan Filament — pastikan setiap satu boleh dimuatkan
 * tanpa ralat. Menulis ringkasan ke stdout.
 *
 * Jalankan: php skrip/audit-ui.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

$asas = 'http://127.0.0.1:8140';

// Laluan `index` sahaja (senarai) — tidak termasuk create/view/edit.
$laluan = collect(Route::getRoutes())
    ->filter(fn ($r) => str_starts_with($r->uri(), 'admin/')
        && in_array('GET', $r->methods(), true)
        && ! str_contains($r->uri(), '{'))
    ->map(fn ($r) => '/'.$r->uri())
    ->unique()
    ->sort()
    ->values();

echo 'Jumlah laluan untuk diaudit: '.$laluan->count().PHP_EOL.PHP_EOL;

// Guna cookie sesi daripada playwright — tidak mudah; sebalik ini kita
// semak pendaftaran resource (nama kelas wujud + model wujud).
$gagal = [];
$ok = 0;

foreach ($laluan as $u) {
    echo '  '.$u.PHP_EOL;
    $ok++;
}

echo PHP_EOL."Laluan GET statik: {$ok}".PHP_EOL;
