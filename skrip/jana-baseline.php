<?php

/**
 * jana-baseline.php — jana `docs/AUDIT-BASELINE.md` daripada kod sebenar.
 *
 * Guna:  php skrip/jana-baseline.php [--tulis] [--tolak=path]
 *
 * Tanpa `--tulis`, skrip hanya **mencetak** jadual metrik dan tidak menyentuh
 * fail. Inisafety: CI boleh memanggilnya tanpa mengubah repositori.
 *
 * `--tulis` menulis semula bahagian "Baseline disahkan" dalam
 * `docs/AUDIT-BASELINE.md`. `--tolak=<path>` menulis ke fail lain
 * (digunakan CI untuk menghasilkan artefak tanpa mengubah working tree).
 *
 * Prinsip: angka tidak pernah ditulis manual. Fail ini adalah penjana.
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ── Argument ────────────────────────────────────────────────────────────

$argv = $_SERVER['argv'] ?? [];
$tulis = in_array('--tulis', $argv, true);
$sasar = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--tolak=')) {
        $sasar = substr($a, strlen('--tolak='));
    }
}

// ── 1. Kumpul kebenaran ─────────────────────────────────────────────────

// Akar repositori — MESTI ditakrifkan SEBELUM `$kira` (closure `static` tidak
// mewarisi skop induk; pernah menyebabkan "Undefined variable $root").
$root = dirname(__DIR__);

$modul = count(glob($root . '/app/Modules/*', GLOB_ONLYDIR));

$migration = count(glob($root . '/database/migrations/*.php'));

$resourceFilament = count(glob($root . '/app/Filament/Resources/*', GLOB_ONLYDIR));

$widgetFilament = count(glob($root . '/app/Filament/Widgets/*.php'));

// Kira fail mengikut subfolder modul (recursive — modul boleh nest)
$kira = static function (string $sub) use ($root): int {
    $n = 0;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root . '/app/Modules', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (! $f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $dir = str_replace('\\', '/', $f->getPathname());
        if (str_contains($dir, '/' . $sub . '/')) {
            $n++;
        }
    }
    return $n;
};

$model = $kira('Models');
$service = $kira('Services');
$controller = $kira('Http/Controllers');

// Routes api/v1 — baca daripada router Laravel
$laluanApi = 0;
foreach (app('router')->getRoutes() as $route) {
    if (str_starts_with($route->uri(), 'api/v1/')) {
        $laluanApi++;
    }
}

// Kaedah ujian — kira corak PHPUnit dalam semua fail ujian
$failUjian = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/tests', FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $failUjian[] = $f->getPathname();
    }
}
$kaedahUjian = 0;
foreach ($failUjian as $f) {
    $src = file_get_contents($f);
    // Kaedah test_ / #[Test] / /** @test */
    $kaedahUjian += preg_match_all(
        '/(?:public\s+)?function\s+test\w*\s*\(|#\[\s*Test\s*\]|@test\b/',
        $src
    );
}

// Jadual sebenar — hanya jika pangkalan data tersedia
$jadual = null;
$kebenaran = null;
try {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $jadual = DB::select(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        );
        $jadual = count($jadual);
    } else {
        $jadual = count(DB::select('SHOW TABLES'));
    }
    $kebenaran = DB::table('permissions')->count();
} catch (\Throwable $e) {
    $jadual = null;
    $kebenaran = null;
}

// ── 2. Bentuk baris metrik ─────────────────────────────────────────────

$baris = [
    ['Modul', $modul, 'direktori di `app/Modules/`'],
    ['Migration', $migration, 'fail di `database/migrations/`'],
    ['Model', $model, '`app/Modules/**/Models/*.php`'],
    ['Service', $service, '`app/Modules/**/Services/*.php`'],
    ['Controller (modul)', $controller, '`app/Modules/**/Http/Controllers/*.php`'],
    ['Resource Filament', $resourceFilament, 'direktori di `app/Filament/Resources/`'],
    ['Widget dashboard', $widgetFilament, '`app/Filament/Widgets/*.php`'],
    ['Kaedah ujian', $kaedahUjian, '`test*` + `#[Test]` + `/** @test */` di `tests/`'],
    ['Laluan `api/v1`', $laluanApi, 'router Laravel (`php artisan route:list`)'],
];

if ($jadual !== null) {
    $baris[] = ['Jadual DB sebenar', $jadual, '`sqlite_master` / `SHOW TABLES`'];
}
if ($kebenaran !== null) {
    $baris[] = ['Kebenaran', $kebenaran, 'jadual `permissions`'];
}

// ── 3. Cetak ───────────────────────────────────────────────────────────

$md = sprintf(
    "| %s | **%s** | %s |\n",
    'METRIK',
    'NILAI',
    'CARA DIUKUR'
);
$md .= '|---|---|---:|' . "\n";
foreach ($baris as [$nama, $nilai, $cara]) {
    $md .= sprintf('| %s | **%s** | %s |' . "\n", $nama, number_format($nilai), $cara);
}

echo "\n=== BASELINE AI BIJAN ===\n\n";
echo $md;

if ($jadual === null) {
    echo "⚠️  Pangkalan data tidak tersedia — jadual & kebenaran dikecualikan.\n";
    echo "    Jalankan selepas `php artisan migrate`.\n";
}

// ── 4. Tulis (pilihan) ─────────────────────────────────────────────────

if (! $tulis && $sasar === null) {
    echo "\n(mod cetakan sahaja — guna --tulis untuk kemas kini fail)\n";
    exit(0);
}

$failBaseline = $root . '/docs/AUDIT-BASELINE.md';

if ($sasar !== null) {
    // Artefak CI — tulis fail penuh
    $kandungan = "# AUDIT-BASELINE (dijana automatik)\n\n"
        . "> Dijana oleh `php skrip/jana-baseline.php --tolak=...` pada "
        . date('Y-m-d H:i:s') . " UTC.\n\n"
        . $md;
    file_put_contents($sasar, $kandungan);
    echo "\nDitulis: {$sasar}\n";
    exit(0);
}

// Tulis ke dalam fail baseline — gantikan seksyen §1
if (! is_file($failBaseline)) {
    fwrite(STDERR, "FAIL: {$failBaseline} tidak dijumpai\n");
    exit(1);
}

$c = file_get_contents($failBaseline);

$penanda = '## 1. Baseline disahkan';
$mula = strpos($c, $penanda);
if ($mula === false) {
    fwrite(STDERR, "FAIL: penanda '{$penanda}' tidak dijumpai\n");
    exit(1);
}

$selepas = strpos($c, '### 1.1 Angka tambahan');
if ($selepas === false) {
    $selepas = strpos($c, '### 1.1 Angka yang TIDAK disahkan');
}
if ($selepas === false) {
    fwrite(STDERR, "FAIL: penanda '### 1.1 Angka tambahan' tidak dijumpai\n");
    exit(1);
}

$seksyen = $penanda . "\n\n"
    . "> **Dijana automatik** oleh `php skrip/jana-baseline.php --tulis`.\n"
    . "> Tarikh penjanaan: " . date('Y-m-d') . "\n"
    . "> Jangan tulis angka di sini secara manual — jalankan skrip.\n\n"
    . $md . "\n";

$c = substr($c, 0, $mula) . $seksyen . substr($c, $selepas);

file_put_contents($failBaseline, $c);

echo "\nDitulis: docs/AUDIT-BASELINE.md\n";
echo "Jalankan `git diff docs/AUDIT-BASELINE.md` untuk semak perubahan.\n";
