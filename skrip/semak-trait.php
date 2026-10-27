<?php

/**
 * semak-trait.php — senarai model yang ada `tenant_id` tetapi TIADA trait
 * `BelongsToTenant`, berserta justifikasi sama ada ia global (sah) atau pepijat.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

/**
 * Model yang SAH tanpa trait — baris global (tenant_id NULL) atau platform.
 */
$sahGlobal = [
    'AdeSkill'            => 'Skill global marketplace (tenant_id NULL)',
    'EntitlementGrant'    => 'Grant platform, tenant_id eksplisit',
    'UsageCounter'        => 'Kiraan platform, tenant_id eksplisit',
    'StatutoryRate'       => 'Kadar statutori global (tenant_id NULL = global)',
    'SupportAccessGrant'  => 'Akses sokongan Super Admin (platform-level)',
    'MediaTemplate'       => 'Templat global (tenant_id NULL) + tenant',
    'WorkflowTemplate'    => 'Templat global (tenant_id NULL) + tenant',
];

$perlu = [];
$sah = [];

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

    $relatif = str_replace(str_replace('\\', '/', app_path()) . '/', '', $laluan);
    $kelas = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relatif);

    if (! class_exists($kelas)) {
        continue;
    }

    try {
        $model = new $kelas;

        if (! method_exists($model, 'getTable') || ! Schema::hasTable($model->getTable())) {
            continue;
        }

        if (! Schema::hasColumn($model->getTable(), 'tenant_id')) {
            continue;
        }

        $gunaTrait = in_array(
            \App\Models\Concerns\BelongsToTenant::class,
            class_uses_recursive($kelas),
            true
        );

        if ($gunaTrait) {
            continue;
        }

        $nama = class_basename($kelas);

        if (isset($sahGlobal[$nama])) {
            $sah[$nama] = $sahGlobal[$nama];
        } else {
            $perlu[] = $nama . ' (' . $model->getTable() . ')';
        }
    } catch (\Throwable $e) {
        // abai
    }
}

echo PHP_EOL . str_repeat('=', 66) . PHP_EOL;
echo ' TRAIT TENANT — SEMAKAN' . PHP_EOL;
echo str_repeat('=', 66) . PHP_EOL . PHP_EOL;

echo '✅ SAH TANPA TRAIT (global / platform) — ' . count($sah) . PHP_EOL;
foreach ($sah as $n => $sebab) {
    echo '   - ' . str_pad($n, 22) . ' ' . $sebab . PHP_EOL;
}

echo PHP_EOL . '❌ PERLU TRAIT (pepijat) — ' . count($perlu) . PHP_EOL;
foreach ($perlu as $p) {
    echo '   - ' . $p . PHP_EOL;
}

echo PHP_EOL;
exit($perlu === [] ? 0 : 1);
