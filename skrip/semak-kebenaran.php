<?php

/**
 * semak-kebenaran.php — pagar `route ↔ permission` (P0-03 audit induk, T-105).
 *
 * Sebelum 28 Okt 2026 fail ini hanya menyenaraikan kebenaran **tenant #1** (ID
 * dikod keras) — bukan audit. Kini ia membandingkan setiap middleware `perm:`
 * pada laluan `api/v1` dengan katalog `permissions`.
 *
 * Guna:
 *   php skrip/semak-kebenaran.php              # ringkasan + exit 1 jika gagal
 *   php skrip/semak-kebenaran.php --terperinci # senarai penuh
 *
 * Empat semakan (1, 3, 4 mesti lulus untuk exit 0):
 *
 *   1. DIGUNAKAN ≠ DIDAFAR — `perm:<nama>` tetapi tiada dalam katalog →
 *      `RequirePermission` abort 403 untuk SEMUA pengguna, termasuk pemilik.
 *      Kelas pepijat `permission:` vs `perm:` (B-03, 4 modul, 27 Okt 2026).
 *   2. DIDAFAR ≠ DIGUNAKAN — kebenaran mati (peringatan, tidak menyekat).
 *   3. LALUAN TANPA PAGAR — laluan berautentikasi tanpa `perm:`/`platform`.
 *      Senarai putih: `config/semak-kebenaran.php`.
 *   4. ALIAS SALAH — `permission:` tidak didaftarkan → 500
 *      `Target class [permission] does not exist`.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use Illuminate\Support\Str;

$terperinci = in_array('--terperinci', $argv ?? [], true);
$config = require __DIR__ . '/../config/semak-kebenaran.php';

// ── 1. Katalog kebenaran ────────────────────────────────────────────────
$dariDb = [];
try {
    $dariDb = Permission::query()->pluck('key')->all();
} catch (\Throwable $e) {
    $dariDb = [];
}

$dariConfig = [];
foreach ((array) config('aibijan.permissions', []) as $kunci => $meta) {
    $dariConfig[] = is_string($kunci) ? $kunci : (string) $meta;
}

$katalog = array_values(array_unique(array_filter(array_merge($dariDb, $dariConfig))));
$sumberKatalog = $dariDb === [] ? 'config/aibijan.php (DB tiada)' : 'jadual permissions';
$katalogSet = array_fill_keys($katalog, true);

// ── 2. Kumpul laluan api/v1 ────────────────────────────────────────────
$laluan = [];
foreach ($app->make('router')->getRoutes() as $r) {
    $uri = $r->uri();
    if (! str_starts_with($uri, 'api/v1')) {
        continue;
    }

    $perms = [];
    $auth = false;
    $platform = false;
    $tenant = false;
    $aliasSalah = [];

    foreach ($r->gatherMiddleware() as $m) {
        if (! is_string($m)) {
            continue;
        } elseif ($m === 'auth:sanctum' || $m === 'auth') {
            $auth = true;
        } elseif (preg_match('/Tenant$/', $m) === 1) {
            $tenant = true;
        } elseif (str_starts_with($m, 'perm:')) {
            foreach (explode(',', substr($m, 5)) as $p) {
                if (($p = trim($p)) !== '') {
                    $perms[] = $p;
                }
            }
        } elseif (str_starts_with($m, 'permission:')) {
            $aliasSalah[] = $m;
        } elseif (str_starts_with($m, 'platform')) {
            $platform = true;
        }
    }

    $laluan[] = [
        'uri' => $uri,
        'aksi' => $r->getActionName(),
        'kaedah' => implode('|', array_diff($r->methods(), ['HEAD'])),
        'auth' => $auth,
        'platform' => $platform,
        'tenant' => $tenant,
        'perm' => array_values(array_unique($perms)),
        'alias_salah' => $aliasSalah,
    ];
}

// ── 3. Semakan ─────────────────────────────────────────────────────────

$digunaTanpaDaftar = [];
$digunaSet = [];
$tanpaPagar = [];
$tanpaTenant = [];
$aliasSalah = [];

$awam = array_map(fn ($u) => trim($u, '/'), $config['laluan_awam'] ?? []);
$dibenar = array_map(fn ($u) => trim($u, '/'), $config['laluan_tanpa_perm'] ?? []);

$padan = static function (string $uri, array $senarai): bool {
    foreach ($senarai as $pola) {
        if ($pola === $uri || Str::is($pola, $uri)) {
            return true;
        }
    }

    return false;
};

foreach ($laluan as $l) {
    foreach ($l['alias_salah'] as $a) {
        $aliasSalah[] = $l['uri'] . ' → ' . $a;
    }

    foreach ($l['perm'] as $p) {
        $digunaSet[$p] = true;
        if (! isset($katalogSet[$p])) {
            $digunaTanpaDaftar[$p][] = $l['kaedah'] . ' /' . $l['uri'];
        }
    }

    if (! $l['auth'] || $l['platform'] || $l['perm'] !== []) {
        continue;
    }

    if ($padan($l['uri'], $awam)) {
        continue;
    }

    // Laluan yang SENGAJA tiada `perm:` (data platform) — dibenarkan, tetapi
    // ia tetap dikira dalam laporan `tanpaPagar` untuk audit (`--terperinci`).
    if ($padan($l['uri'], $dibenar)) {
        continue;
    }

    $tanpaPagar[] = $l['kaedah'] . ' /' . $l['uri'];

    // Laluan `api/v1` berautentikasi yang data-nya diasingkan per tenant MESTI
    // melalui middleware `tenant` — jika tidak `TenantContext` kosong dan
    // controller jatuh ke `tenant_id` eksplisit/first().
    if (! $l['tenant']) {
        $tanpaTenant[] = $l['kaedah'] . ' /' . $l['uri'];
    }
}

$tidakDigunakan = array_values(array_diff($katalog, array_keys($digunaSet)));

// ── 3b. `laluan_tanpa_perm` mesti benar-benar bergantung pada pagar pengawal ──
// Senarai itu ialah janji "kebenaran diperiksa dalam controller". Janji yang
// tidak ditepati = laluan terdedah. Semak nama kaedah pengawal terhadap corak
// pemeriksaan kebenaran; jika tiada, senarai putih itu menipu.
$corakPagar = [
    'can(', '->can(', 'authorize(', 'Gate::', 'abort_unless(', 'abort_if(',
    'permissions()', '->hasRole(', 'isPlatformAdmin',
];
$pagarDiPengawal = [];
foreach ($dibenar as $pola) {
    foreach ($laluan as $l) {
        if (! $padan($l['uri'], [$pola])) {
            continue;
        }
        $aksi = $l['aksi'] ?? '';
        if ($aksi === '' || ! str_contains($aksi, '@')) {
            continue;   // Closure / invokable — jalankan semakan manual
        }
        [$kelas, $kaedah] = explode('@', $aksi, 2);
        try {
            $ref = new ReflectionMethod($kelas, $kaedah);
        } catch (\Throwable $e) {
            $pagarDiPengawal[] = $l['uri'] . ' → kelas/kaedah tidak dijumpai: ' . $aksi;

            continue;
        }
        $sumber = '';
        if (is_file((string) $ref->getFileName())) {
            $sumber = implode('', array_slice(
                file((string) $ref->getFileName()),
                $ref->getStartLine() - 1,
                max(1, $ref->getEndLine() - $ref->getStartLine() + 1)
            ));
        }
        $ada = false;
        foreach ($corakPagar as $c) {
            if (str_contains($sumber, $c)) {
                $ada = true;
                break;
            }
        }
        // `bolehAkses()`/`bolehKeluar()` ialah pembantu projekt ini.
        if (! $ada && (str_contains($sumber, 'bolehAkses(') || str_contains($sumber, 'bolehKeluar('))) {
            $ada = true;
        }
        if (! $ada) {
            $pagarDiPengawal[] = $l['uri'] . ' → ' . $aksi . ' tiada pemeriksaan kebenaran';
        }
    }
}

// ── 4. Laporan ─────────────────────────────────────────────────────────

echo str_repeat('=', 66) . "\n";
echo " PAGAR route ↔ PERMISSION — AI BIJAN (T-105)\n";
echo str_repeat('=', 66) . "\n\n";

echo 'Katalog kebenaran .............. ' . count($katalog) . '  (' . $sumberKatalog . ")\n";
echo 'Laluan api/v1 disemak ......... ' . count($laluan) . "\n";
echo 'Laluan dengan `perm:` ......... ' . count(array_filter($laluan, fn ($l) => $l['perm'] !== [])) . "\n";
echo 'Kebenaran digunakan ........... ' . count($digunaSet) . "\n\n";

$tajuk = static function (string $t, int $n): void {
    echo ($n === 0 ? '✅ ' : '❌ ') . $t . ': ' . $n . "\n";
};

$tajuk('perm: digunakan tetapi tiada dalam katalog', count($digunaTanpaDaftar));
$tajuk('kebenaran dikatalogkan tetapi tiada laluan', count($tidakDigunakan));
$tajuk('laluan berautentikasi tanpa pagar perm', count($tanpaPagar));
$tajuk('laluan berautentikasi tanpa middleware tenant', count($tanpaTenant));
$tajuk('alias middleware salah (permission:)', count($aliasSalah));
$tajuk('senarai putih tanpa pagar dalam pengawal', count($pagarDiPengawal));

if ($terperinci) {
    echo "\n" . str_repeat('-', 66) . "\n";

    foreach ($digunaTanpaDaftar as $p => $contoh) {
        echo "  {$p} (" . count($contoh) . " laluan)\n";
        foreach (array_slice($contoh, 0, 5) as $c) {
            echo "    - {$c}\n";
        }
    }

    $senarai = [
        'Kebenaran tidak digunakan' => $tidakDigunakan,
        'Laluan tanpa pagar' => $tanpaPagar,
        'Laluan tanpa middleware tenant' => $tanpaTenant,
        'Alias salah' => $aliasSalah,
        'Laluan dibenarkan tetapi pengawal tiada pemeriksaan' => $pagarDiPengawal,
    ];
    foreach ($senarai as $label => $isi) {
        if ($isi === []) {
            continue;
        }
        echo "\n{$label}:\n";
        foreach ($isi as $x) {
            echo '  - ' . $x . "\n";
        }
    }

    echo "\nLaluan berautentikasi TANPA `perm:` yang dibenarkan (config):\n";
    foreach ($dibenar as $p) {
        echo "  - {$p}\n";
    }
    echo "\n";

    echo str_repeat('-', 66) . "\n";
}

if ($digunaTanpaDaftar !== [] || $tanpaPagar !== [] || $aliasSalah !== [] || $pagarDiPengawal !== []) {
    echo "\n" . str_repeat('-', 66) . "\n";
    foreach ($digunaTanpaDaftar as $p => $contoh) {
        echo "  ❌ `perm:{$p}` tiada dalam katalog — " . count($contoh) . " laluan → 403 untuk SEMUA pengguna.\n";
    }
    if ($aliasSalah !== []) {
        echo "  ❌ alias `permission:` digunakan — alias betul ialah `perm:`.\n";
    }
    if ($tanpaPagar !== []) {
        echo '  ❌ ' . count($tanpaPagar) . " laluan berautentikasi tanpa pagar (lihat --terperinci).\n";
    }
    if ($pagarDiPengawal !== []) {
        echo '  ❌ ' . count($pagarDiPengawal) . " laluan dalam `laluan_tanpa_perm` tetapi pengawal TIDAK memeriksa kebenaran.\n";
    }
    echo str_repeat('-', 66) . "\n";
    exit(1);
}

echo "\n" . str_repeat('-', 66) . "\n";
echo "✅ Semua `perm:` wujud dalam katalog, tiada alias salah, tiada laluan terdedah.\n";
if ($tidakDigunakan !== []) {
    echo '⚠️  ' . count($tidakDigunakan) . " kebenaran tidak digunakan (peringatan, tidak menyekat).\n";
}
echo str_repeat('-', 66) . "\n";

exit(0);
