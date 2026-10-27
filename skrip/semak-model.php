<?php

/**
 * semak-model.php — sahkan setiap model Eloquent menunjuk ke jadual yang WUJUD.
 *
 * Menangkap bug kelas: model tiada `$table` padahal nama jadual != plural kelas
 * (cth `Loyalty\Models\Tier` -> `tiers` sedangkan jadual sebenar `loyalty_tiers`).
 *
 * Guna: php skrip/semak-model.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$tiadaJadual = [];
$tiadaTrait = [];
$ralat = [];
$jumlah = 0;

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS)
);

foreach ($it as $f) {
    if (! $f->isFile() || $f->getExtension() !== 'php') {
        continue;
    }

    $laluan = str_replace('\\', '/', $f->getPathname());

    if (! str_contains($laluan, '/Models/')) {
        continue;
    }

    $relatif = str_replace(
        str_replace('\\', '/', app_path()) . '/',
        '',
        $laluan
    );

    $kelas = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relatif);

    if (! class_exists($kelas)) {
        continue;
    }

    try {
        $model = new $kelas;

        if (! method_exists($model, 'getTable')) {
            continue;
        }

        $jumlah++;

        // 1. Jadual wujud?
        if (! Schema::hasTable($model->getTable())) {
            $tiadaJadual[] = class_basename($kelas) . ' -> ' . $model->getTable();
            continue;
        }

        // 2. Ada tenant_id tapi TIADA trait BelongsToTenant?
        $adaTenantId = Schema::hasColumn($model->getTable(), 'tenant_id');
        $gunaTrait = in_array(
            \App\Models\Concerns\BelongsToTenant::class,
            class_uses_recursive($kelas),
            true
        );

        if ($adaTenantId && ! $gunaTrait) {
            $tiadaTrait[] = class_basename($kelas) . ' (' . $model->getTable() . ')';
        }
    } catch (\Throwable $e) {
        $ralat[] = class_basename($kelas) . ' -> ' . substr($e->getMessage(), 0, 70);
    }
}

echo PHP_EOL . str_repeat('=', 64) . PHP_EOL;
echo ' SEMAKAN MODEL — AI BIJAN' . PHP_EOL;
echo str_repeat('=', 64) . PHP_EOL;
echo 'Model diperiksa: ' . $jumlah . PHP_EOL . PHP_EOL;

$gagal = 0;

if ($tiadaJadual !== []) {
    $gagal += count($tiadaJadual);
    echo '❌ JADUAL TIDAK WUJUD (' . count($tiadaJadual) . ')' . PHP_EOL;
    echo '   Tambah protected $table = \'nama_betul\';' . PHP_EOL;
    foreach ($tiadaJadual as $b) {
        echo '   - ' . $b . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($tiadaTrait !== []) {
    $gagal += count($tiadaTrait);
    echo '⚠️  ADA tenant_id TAPI TIADA trait BelongsToTenant (' . count($tiadaTrait) . ')' . PHP_EOL;
    echo '   Risiko: data merentas tenant.' . PHP_EOL;
    foreach ($tiadaTrait as $b) {
        echo '   - ' . $b . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($ralat !== []) {
    $gagal += count($ralat);
    echo '❌ RALAT (' . count($ralat) . ')' . PHP_EOL;
    foreach ($ralat as $b) {
        echo '   - ' . $b . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($gagal === 0) {
    echo '✅ Semua model menunjuk ke jadual yang wujud & bertrait tenant.' . PHP_EOL;
} else {
    echo '❌ ' . $gagal . ' isu ditemui.' . PHP_EOL;
}

exit($gagal === 0 ? 0 : 1);
