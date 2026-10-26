<?php

/**
 * Audit pengasingan tenant — model vs trait vs kolum DB.
 *
 * Membandingkan:
 *   1. Model yang GUA `BelongsToTenant`
 *   2. Model yang TIDAK guna trait TETAPI jadualnya ada kolum `tenant_id`
 *   3. Model yang guna trait TETAPI jadualnya TIADA kolum `tenant_id`
 *   4. Ujian pengasingan per modul
 *
 * Guna:
 *   php skrip/audit-tenant.php              # ringkasan
 *   php skrip/audit-tenant.php --terperinci  # senarai penuh
 *
 * Exit 1 jika ada model berisiko yang belum dikecualikan secara eksplisit.
 *
 * Dikecualikan (senarai putih) dalam config/tenant-global-models.php.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$terperinci = in_array('--terperinci', $argv, true);

// ---------------------------------------------------------------
// 1. Kumpul semua model
// ---------------------------------------------------------------
$fail = [];
foreach ([
    __DIR__ . '/../app/Models',
    ...glob(__DIR__ . '/../app/Modules/*/Models', GLOB_ONLYDIR),
] as $dir) {
    foreach (glob($dir . '/*.php') as $f) {
        $fail[] = $f;
    }
}

$semua = [];
foreach ($fail as $f) {
    $kod = file_get_contents($f);
    if (! preg_match('/^namespace\s+([^;]+);/m', $kod, $ns)) {
        continue;
    }
    if (! preg_match('/^class\s+(\w+)/m', $kod, $kl)) {
        continue;
    }

    $kelas = $ns[1] . '\\' . $kl[1];
    if (! class_exists($kelas)) {
        continue;
    }
    if (! is_subclass_of($kelas, Illuminate\Database\Eloquent\Model::class)) {
        continue;
    }

    // Abaikan kelas abstrak & Pivot
    $ref = new ReflectionClass($kelas);
    if ($ref->isAbstract()) {
        continue;
    }
    if ($ref->isSubclassOf(Illuminate\Database\Eloquent\Relations\Pivot::class)) {
        continue;
    }

    $gunaTrait = in_array(
        App\Models\Concerns\BelongsToTenant::class,
        class_uses_recursive($kelas),
        true
    );

    // Nama jadual sebenar
    try {
        $jadual = (new $kelas())->getTable();
    } catch (Throwable $e) {
        continue;
    }

    // Ada kolum tenant_id?
    $adaKolom = false;
    try {
        $kolum = DB::select("select name from pragma_table_info(?)", [$jadual]);
        foreach ($kolum as $k) {
            if ($k->name === 'tenant_id') {
                $adaKolom = true;
                break;
            }
        }
    } catch (Throwable $e) {
        // MySQL: guna information_schema
        try {
            $adaKolom = DB::table('information_schema.columns')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $jadual)
                ->where('column_name', 'tenant_id')
                ->exists();
        } catch (Throwable $e2) {
            continue;   // jadual tiada
        }
    }

    $semua[] = [
        'kelas' => $kelas,
        'pendek' => $kl[1],
        'jadual' => $jadual,
        'trait' => $gunaTrait,
        'kolom' => $adaKolom,
    ];
}

// ---------------------------------------------------------------
// 2. Senarai putih (sengaja tanpa trait)
// ---------------------------------------------------------------
$putihFail = __DIR__ . '/../config/tenant-global-models.php';
$putih = is_file($putihFail) ? require $putihFail : [];
$putih = (array) ($putih['models'] ?? []);

// ---------------------------------------------------------------
// 3. Klasifikasi
// ---------------------------------------------------------------
$bocor = [];        // ada kolum, tiada trait, TIADA dalam senarai putih
$dikecualikan = []; // ada kolum, tiada trait, ADA dalam senarai putih
$tanpaKolom = [];   // guna trait, tiada kolum → auto-fill akan gagal
$selamat = 0;

foreach ($semua as $m) {
    if ($m['trait'] && $m['kolom']) {
        $selamat++;
    } elseif ($m['trait'] && ! $m['kolom']) {
        $tanpaKolom[] = $m;
    } elseif (! $m['trait'] && $m['kolom']) {
        $dalamPutih = false;
        foreach ($putih as $kelasPutih) {
            if (is_a($m['kelas'], $kelasPutih, true)) {
                $dalamPutih = true;
                break;
            }
        }
        if ($dalamPutih) {
            $dikecualikan[] = $m;
        } else {
            $bocor[] = $m;
        }
    }
    // !trait && !kolom → jadual anak, betul
}

// ---------------------------------------------------------------
// 4. Liputan ujian pengasingan per modul
// ---------------------------------------------------------------
$modul = array_map(
    fn ($d) => basename($d),
    glob(__DIR__ . '/../app/Modules/*', GLOB_ONLYDIR)
);

$failUjian = glob(__DIR__ . '/../tests/Feature/*.php');
$korpusUjian = '';
foreach ($failUjian as $f) {
    $korpusUjian .= file_get_contents($f);
}

$adaUjianIsolasi = [];
$tiadaUjianIsolasi = [];
foreach ($modul as $m) {
    // Ujian khusus "Isolation" untuk modul ini
    $corak = '/class\s+' . preg_quote($m, '/') . '\w*IsolationTest/';
    if (preg_match($corak, $korpusUjian) && str_contains($korpusUjian, 'test_')) {
        $adaUjianIsolasi[] = $m;
    } else {
        $tiadaUjianIsolasi[] = $m;
    }
}

// ---------------------------------------------------------------
// 5. Laporkan
// ---------------------------------------------------------------
echo str_repeat('=', 66) . PHP_EOL;
echo ' AUDIT PENGASINGAN TENANT — AI BIJAN' . PHP_EOL;
echo str_repeat('=', 66) . PHP_EOL . PHP_EOL;

printf("Model disemak .................. %d\n", count($semua));
printf("  ✅ trait + kolum tenant_id ... %d\n", $selamat);
printf("  ✅ jadual anak (tiada kolum) .. %d\n", count($semua) - $selamat - count($tanpaKolom) - count($bocor) - count($dikecualikan));
printf("  ⚠️  dikecualikan (senarai putih) %d\n", count($dikecualikan));
printf("  ❌ BOCOR (kolum tanpa trait) .. %d\n", count($bocor));
printf("  ❌ trait tanpa kolum ......... %d\n", count($tanpaKolom));
echo PHP_EOL;

printf("Modul ......................... %d\n", count($modul));
printf("  ✅ ada ujian pengasingan ..... %d\n", count($adaUjianIsolasi));
printf("  ❌ tiada ujian pengasingan ... %d\n", count($tiadaUjianIsolasi));
echo PHP_EOL;

if ($bocor !== []) {
    echo "❌ MODEL BOCOR — ada kolum tenant_id tetapi TIADA BelongsToTenant:" . PHP_EOL;
    foreach ($bocor as $m) {
        printf("   • %-32s → %s\n", $m['pendek'], $m['jadual']);
    }
    echo PHP_EOL . '   Betulkan: tambah `use BelongsToTenant;` ATAU daftar dalam' . PHP_EOL;
    echo '   config/tenant-global-models.php dengan justifikasi.' . PHP_EOL . PHP_EOL;
}

if ($tanpaKolom !== []) {
    echo "❌ TRAIT TANPA KOLUM — create() akan gagal:" . PHP_EOL;
    foreach ($tanpaKolom as $m) {
        printf("   • %-32s → %s\n", $m['pendek'], $m['jadual']);
    }
    echo PHP_EOL;
}

if ($terperinci && $dikecualikan !== []) {
    echo "⚠️  DIKECUALIKAN (disahkan sengaja):" . PHP_EOL;
    foreach ($dikecualikan as $m) {
        printf("   • %-32s → %s\n", $m['pendek'], $m['jadual']);
    }
    echo PHP_EOL;
}

if ($terperinci && $tiadaUjianIsolasi !== []) {
    echo "❌ MODUL TIADA UJIAN PENGASINGAN:" . PHP_EOL;
    foreach (array_chunk($tiadaUjianIsolasi, 5) as $baris) {
        echo '   ' . implode(' · ', $baris) . PHP_EOL;
    }
    echo PHP_EOL;
}

$gagal = count($bocor) + count($tanpaKolom);

echo str_repeat('-', 66) . PHP_EOL;
if ($gagal === 0) {
    echo '✅ Tiada model berisiko. ' . count($tiadaUjianIsolasi) . ' modul masih perlu ujian pengasingan.' . PHP_EOL;
    echo '   (Ujian pengasingan TIDAK menyekat exit — tambah berperingkat.)' . PHP_EOL;
} else {
    echo "❌ {$gagal} isu model pengasingan mesti dibetulkan." . PHP_EOL;
}
echo str_repeat('-', 66) . PHP_EOL;

exit($gagal === 0 ? 0 : 1);
