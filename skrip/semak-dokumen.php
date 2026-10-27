<?php

/**
 * semak-dokumen.php — sahkan angka dalam dokumentasi terhadap kod sebenar.
 *
 * Guna: php skrip/semak-dokumen.php
 *
 * Membaca kod SEBENAR lalu membandingkan dengan angka yang didakwa dalam
 * `docs/KATALOG-MODUL-AI-BIJAN.md`. Melaporkan percanggahan.
 *
 * Prinsip: dokumen yang mendakwa lebih daripada kod adalah lebih berbahaya
 * daripada tiada dokumen.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ── 1. Kumpul kebenaran daripada kod ────────────────────────────────────

$modul = count(glob(__DIR__ . '/../app/Modules/*', GLOB_ONLYDIR));

$jadual = count(DB::select(
    "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
));

$resourceFilament = count(glob(__DIR__ . '/../app/Filament/Resources/*', GLOB_ONLYDIR));

$widgetFilament = count(glob(__DIR__ . '/../app/Filament/Widgets/*.php'));

$kebenaran = DB::table('permissions')->count();

$migration = count(glob(__DIR__ . '/../database/migrations/*.php'));

// Laluan API — kira dari router Laravel (bukan shell)
$laluan = 0;
foreach (app('router')->getRoutes() as $route) {
    if (str_starts_with($route->uri(), 'api/v1/')) {
        $laluan++;
    }
}

// Model / service / controller
$kira = static function (string $sub): int {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../app/Modules', FilesystemIterator::SKIP_DOTS)
    );
    $n = 0;
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $dir = str_replace('\\', '/', $f->getPathname());
            if (str_contains($dir, '/' . $sub . '/')) {
                $n++;
            }
        }
    }
    return $n;
};

$model = $kira('Models');
$service = $kira('Services');
$controller = $kira('Http/Controllers');

// ── 2. Baca dakwaan dari katalog + dokumen sistem ────────────────────────

$katalog = __DIR__ . '/../docs/KATALOG-MODUL-AI-BIJAN.md';
$sistem = __DIR__ . '/../docs/SISTEM-AI-BIJAN.md';
$baseline = __DIR__ . '/../docs/AUDIT-BASELINE.md';
$dakwaan = [];

foreach ([$katalog, $sistem, $baseline] as $failDok) {
    if (! is_file($failDok)) {
        continue;
    }

    $teks = file_get_contents($failDok);
    $label = basename($failDok);

    if (preg_match('/\*\*(\d+) modul\*\*/', $teks, $m)) {
        $dakwaan['modul'] = (int) $m[1];
    }
    if (preg_match('/\*\*(\d[\d,]*)\s*jadual\*\*/', $teks, $m)) {
        $dakwaan['jadual'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/\*\*(\d[\d,]*)\s*(?:laluan API|endpoint)\*\*/i', $teks, $m)) {
        $dakwaan['laluan'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/\|\s*Ujian\s*\|\s*\*\*(\d[\d,]*)/', $teks, $m)) {
        $dakwaan['ujian'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/\|\s*Kebenaran\s*\|\s*\*\*(\d+)/', $teks, $m)) {
        $dakwaan['kebenaran'] = (int) $m[1];
    }
    if (preg_match('/\*\*(\d+) resource\*\*/', $teks, $m)) {
        $dakwaan['resource_filament'] = (int) $m[1];
    }
    if (preg_match('/\|\s*Model\s*\|\s*\*\*(\d+)/', $teks, $m)) {
        $dakwaan['model'] = (int) $m[1];
    }
    if (preg_match('/\|\s*Service\s*\|\s*\*\*(\d+)/', $teks, $m)) {
        $dakwaan['service'] = (int) $m[1];
    }
    if (preg_match('/\|\s*Controller \(modul\)\s*\|\s*\*\*(\d+)/', $teks, $m)) {
        $dakwaan['controller'] = (int) $m[1];
    }
    if (preg_match('/\|\s*Migration\s*\|\s*\*\*(\d+)/', $teks, $m)) {
        $dakwaan['migration'] = (int) $m[1];
    }

    // Format jadual AUDIT-BASELINE.md: "| Modul | **59** | ... |"
    // (baris "| METRIK | NILAI | CARA DIUKUR |" adalah pengepala dan dilangkau
    //  kerana tiada nilai angka dalam kurungan kurung)
    foreach ([
        'modul' => 'Modul',
        'jadual' => 'Jadual DB sebenar',
        'laluan' => 'Laluan `api/v1`',
        'resource_filament' => 'Resource Filament',
        'kebenaran' => 'Kebenaran',
        'model' => 'Model',
        'service' => 'Service',
        'controller' => 'Controller \(modul\)',
        'migration' => 'Migration',
    ] as $kunci => $labelBaris) {
        if ($label !== 'AUDIT-BASELINE.md') {
            continue;
        }
        if (preg_match('~\|\s*' . $labelBaris . '\s*\|\s*\*\*([\d,]+)\*\*~', $teks, $m)) {
            $dakwaan[$kunci] = (int) str_replace(',', '', $m[1]);
        }
    }
}

// ── 3. Bandingkan ───────────────────────────────────────────────────────

$sebenar = [
    'modul' => $modul,
    'jadual' => $jadual,
    'laluan' => $laluan,
    'resource_filament' => $resourceFilament,
    'kebenaran' => $kebenaran,
    'model' => $model,
    'service' => $service,
    'controller' => $controller,
    'migration' => $migration,
    'widget' => $widgetFilament,
];

echo PHP_EOL . str_repeat('=', 62) . PHP_EOL;
echo ' SEMAKAN DOKUMEN — AI BIJAN' . PHP_EOL;
echo str_repeat('=', 62) . PHP_EOL . PHP_EOL;

printf("%-22s %10s %10s  %s\n", 'METRIK', 'DAKWAAN', 'SEBENAR', 'STATUS');
echo str_repeat('-', 62) . PHP_EOL;

$percanggahan = 0;

foreach ($sebenar as $kunci => $nilai) {
    $d = $dakwaan[$kunci] ?? null;

    if ($d === null) {
        printf("%-22s %10s %10d  %s\n", $kunci, '—', $nilai, 'ℹ tiada dakwaan');
        continue;
    }

    $sama = $d === $nilai;

    if (! $sama) {
        $percanggahan++;
    }

    printf(
        "%-22s %10d %10d  %s\n",
        $kunci,
        $d,
        $nilai,
        $sama ? '✅' : '❌ PERLU KEMAS KINI'
    );
}

echo str_repeat('-', 62) . PHP_EOL;

// ---------------------------------------------------------------
// SEMAKAN LALUAN BERGanda & WILDCARD (pepijat sebenar 26 Okt 2026)
// ---------------------------------------------------------------
/** @var \Illuminate\Routing\RouteCollection $koleksi */
$koleksi = app('router')->getRoutes();
$berganda = [];
$wildcard = [];

foreach ($koleksi as $laluan) {
    $uri = $laluan->uri();

    // api/v1/{id} — provider modul tiada prefix
    if (preg_match('#^api/v1/\{#', $uri)) {
        $wildcard[] = $uri;
    }

    // api/v1/finance/finance/* — provider DAN routes/api.php kedua-duanya tambah prefix
    if (preg_match('#^api/v1/([a-z][a-z0-9-]*)/\1(/|$)#', $uri)) {
        $berganda[] = $uri;
    }
}

printf("%-22s %10s %10d  %s\n", 'laluan_berganda', '0', count($berganda), count($berganda) === 0 ? '✅' : '❌ PERLU DIBETULKAN');
printf("%-22s %10s %10d  %s\n", 'laluan_wildcard', '0', count($wildcard), count($wildcard) === 0 ? '✅' : '❌ PERLU DIBETULKAN');

if ($berganda !== []) {
    echo PHP_EOL . 'Laluan prefix berganda (buang Route::prefix(...) dalam app/Modules/<X>/routes/api.php):' . PHP_EOL;
    foreach (array_slice($berganda, 0, 10) as $u) {
        echo '  • ' . $u . PHP_EOL;
    }
    if (count($berganda) > 10) {
        echo '  … dan ' . (count($berganda) - 10) . ' lagi' . PHP_EOL;
    }
    $percanggahan++;
}

if ($wildcard !== []) {
    echo PHP_EOL . 'Laluan wildcard api/v1/{id} (tambah ->prefix(...) pada provider):' . PHP_EOL;
    foreach (array_slice($wildcard, 0, 10) as $u) {
        echo '  • ' . $u . PHP_EOL;
    }
    $percanggahan++;
}

echo str_repeat('-', 62) . PHP_EOL;

if ($percanggahan === 0) {
    echo '✅ Semua angka baseline sepadan dengan kod.' . PHP_EOL;
} else {
    echo "❌ {$percanggahan} percanggahan ditemui — jana semula docs/AUDIT-BASELINE.md" . PHP_EOL;
}

echo PHP_EOL . 'Nota: ujian diukur dengan arahan berasingan:' . PHP_EOL;
echo '  php artisan test        # keputusan ujian tidak disimpulkan daripada metrik kod' . PHP_EOL;

exit($percanggahan === 0 ? 0 : 1);
