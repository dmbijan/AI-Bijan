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
// 1b. Kesesuaian model ↔ jadual ($fillable vs kolum sebenar)
// ---------------------------------------------------------------
// Pepijat sebenar 27 Okt 2026: `Quotation::$fillable` ada 16 kolum yang TIADA
// dalam jadual (customer_name, issue_date, grand_total, …) → setiap
// `create()` meletup dengan MassAssignmentException / "no such column".
// `DebitNote` pula guna original_amount/additional_amount sedangkan kolum
// sebenar cuma `amount`.
//
// Semakan ini mencari kolum $fillable yang tidak wujud dalam DB.
$takSefasa = [];   // ['pendek','jadual','kolum'=>[...]]

foreach ($semua as $m) {
    try {
        $contoh = new $m['kelas']();
        $fillable = $contoh->getFillable();
    } catch (Throwable $e) {
        continue;
    }

    if ($fillable === []) {
        continue;   // guna $guarded, bukan $fillable — langkau
    }

    try {
        $kolumDb = array_map(
            fn ($k) => $k->name,
            DB::select('select name from pragma_table_info(?)', [$m['jadual']])
        );
    } catch (Throwable $e) {
        // MySQL
        try {
            $kolumDb = DB::table('information_schema.columns')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $m['jadual'])
                ->pluck('column_name')->all();
        } catch (Throwable $e2) {
            continue;   // jadual tiada — dilaporkan oleh semakan lain
        }
    }

    if ($kolumDb === []) {
        continue;
    }

    $hilang = array_values(array_diff($fillable, $kolumDb));

    if ($hilang !== []) {
        $takSefasa[] = [
            'pendek' => $m['pendek'],
            'jadual' => $m['jadual'],
            'kolum' => $hilang,
        ];
    }
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
// ⚠️ 27 Okt 2026: dahulu `glob('tests/Feature/*.php')` sahaja — ujian dalam
// subfolder (`tests/Feature/Pengasingan/`) TIDAK dikira, jadi audit melaporkan
// "54 modul tiada ujian" walaupun liputan sebenar lebih tinggi. Kini rekursif.
$modul = array_map(
    fn ($d) => basename($d),
    glob(__DIR__ . '/../app/Modules/*', GLOB_ONLYDIR)
);

$failUjian = [];
$itUjian = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../tests', FilesystemIterator::SKIP_DOTS)
);
foreach ($itUjian as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $failUjian[] = $f->getPathname();
    }
}

$korpusUjian = '';
foreach ($failUjian as $f) {
    $korpusUjian .= file_get_contents($f);
}

$adaUjianIsolasi = [];
$tiadaUjianIsolasi = [];
foreach ($modul as $m) {
    // Ujian khusus untuk modul ini:
    //   • `XxxIsolationTest`  — corak asal
    //   • `\App\Modules\<M>\...` diimport dalam fail ujian kelas isolasi
    $corak = '/class\s+' . preg_quote($m, '/') . '\w*IsolationTest/';
    // ⚠️ Dalam single-quoted PHP, setiap `\\` = 1 backslash regex.
    // Perlu 4 (`\\\\`) supaya regex menerima DUA backslash literal iaitu `App\Models\Item`.
    $corakImport = '/use\s+App\\\\Modules\\\\' . preg_quote($m, '/') . '\\\\/';

    $adaDalaman = (bool) preg_match($corak, $korpusUjian);
    $adaImport = false;
    $adaModelTeras = false;

    // Modul tanpa folder Models/ (Helpdesk, Inventory, Manufacturing, SalesCopilot)
    // menggunakan model teras `app/Models`. Ujian pengasingan yang mengimport
    // model tersebut DITERIMA sebagai liputan modul ini.
    $modelTeras = [
        'Helpdesk' => ['Ticket', 'TicketCategory', 'TicketMessage', 'SlaPolicy'],
        'Inventory' => ['Item', 'StockBalance', 'StockLedgerEntry', 'Warehouse', 'StockMovement'],
        'Manufacturing' => ['Bom', 'BomLine', 'WorkOrder', 'WorkOrderMaterial'],
        'SalesCopilot' => ['Prospect', 'BlastMessage'],
    ];

    // Modul yang TIDAK memiliki model sendiri → dibaca/ditulis melalui model
    // modul LAIN. Liputan diterima jika ujian isolasi mengimport model tersebut.
    $modulBergantung = [
        'SalesCopilot' => ['Prospecting' => ['Prospect'], 'Blast' => ['BlastMessage']],
    ];

    foreach ($modulBergantung[$m] ?? [] as $asalModul => $kelasSenarai) {
        foreach ($kelasSenarai as $kelasAsal) {
            $corakAsal = '/use\s+App\\\\Modules\\\\' . preg_quote($asalModul, '/')
                . '\\\\Models\\\\' . preg_quote($kelasAsal, '/') . ';/';

            if (preg_match($corakAsal, $korpusUjian)) {
                $adaModelTeras = true;

                break 2;
            }
        }
    }

    foreach ($modelTeras[$m] ?? [] as $kelasTeras) {
        if (preg_match('/use\s+App\\\\Models\\\\' . preg_quote($kelasTeras, '/') . ';/', $korpusUjian)) {
            $adaModelTeras = true;

            break;
        }
    }

    foreach ($failUjian as $f) {
        $isi = file_get_contents($f);
        if (preg_match('/class\s+\w*IsolationTest/', $isi) && preg_match($corakImport, $isi)) {
            $adaImport = true;

            break;
        }
    }

    if (($adaDalaman || $adaImport || $adaModelTeras) && str_contains($korpusUjian, 'test_')) {
        $adaUjianIsolasi[] = $m;
    } else {
        $tiadaUjianIsolasi[] = $m;
    }
}

// ---------------------------------------------------------------
// 4b. Integriti skema — FK rosak, SoftDeletes tanpa kolum
// ---------------------------------------------------------------
$fkRosak = [];
$softDeletesRosak = [];

if (DB::connection()->getDriverName() === 'sqlite') {
    $jadualSemua = DB::select("select name, sql from sqlite_master where type = 'table'");
    $namaJadual = array_map(fn ($x) => $x->name, $jadualSemua);

    foreach ($jadualSemua as $r) {
        if (! $r->sql) {
            continue;
        }
        if (preg_match_all('/references\s+"([a-zA-Z_]+)"/i', $r->sql, $m)) {
            foreach ($m[1] as $ref) {
                if (! in_array($ref, $namaJadual, true)) {
                    $fkRosak[] = "{$r->name} → {$ref}";
                }
            }
        }
    }
}

foreach ($semua as $m) {
    if (! in_array(Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($m['kelas']), true)) {
        continue;
    }
    try {
        $ada = collect(DB::select('select name from pragma_table_info(?)', [$m['jadual']]))
            ->contains(fn ($k) => $k->name === 'deleted_at');
        if (! $ada) {
            $softDeletesRosak[] = "{$m['pendek']} → {$m['jadual']}";
        }
    } catch (Throwable $e) {
        // MySQL — langkau.
    }
}

// ---------------------------------------------------------------
// 4c. Kaedah tidak wujud — Auth::tenant(), ::query()->tenant()
// ---------------------------------------------------------------
// Pepijat sebenar 27 Okt 2026: 35 panggilan `Auth::tenant()->id` dan
// `Employee::query()->tenant()->...` dalam Payroll + HRMS. Kedua-duanya
// TIDAK WUJUD → setiap endpoint tersebut pulang HTTP 500.
$kaedahHantu = [];

foreach (glob(__DIR__ . '/../app/Modules/*/{Http,Services}/*.php', GLOB_BRACE) as $f) {
    $kod = file_get_contents($f);
    $nama = basename($f);

    if (preg_match_all('/Auth::tenant\(\)|auth\(\)->tenant\(\)|::query\(\)->tenant\(\)|->tenant\(\)->(payrollRuns|payrollDetails)\(\)/', $kod, $m)) {
        $kaedahHantu[] = $nama . ' (' . count($m[0]) . '×) ' . $m[0][0];
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

printf("Integriti skema:\n");
printf("  %s FK rosak .................. %d\n", $fkRosak === [] ? '✅' : '❌', count($fkRosak));
printf("  %s SoftDeletes tanpa kolum ... %d\n", $softDeletesRosak === [] ? '✅' : '❌', count($softDeletesRosak));
printf("  %s \$fillable ≠ kolum DB ...... %d\n", $takSefasa === [] ? '✅' : '❌', count($takSefasa));
printf("  %s kaedah tidak wujud ....... %d\n", $kaedahHantu === [] ? '✅' : '❌', count($kaedahHantu));
echo PHP_EOL;

if ($kaedahHantu !== []) {
    echo "❌ KAEDAH TIDAK WUJUD (endpoint akan pulang HTTP 500):" . PHP_EOL;
    foreach ($kaedahHantu as $r) {
        echo '   • ' . $r . PHP_EOL;
    }
    echo PHP_EOL . '   Ganti: app(\\App\\Support\\Tenancy\\TenantContext::class)->id()' . PHP_EOL;
    echo '   atau query model terus (PayrollRun::query()).' . PHP_EOL . PHP_EOL;
}

if ($takSefasa !== []) {
    echo "❌ MODEL TIDAK SEFASA DENGAN JADUAL (create() akan gagal):" . PHP_EOL;
    foreach (array_slice($takSefasa, 0, 20) as $r) {
        printf(
            "   • %-28s %-30s hilang: %s\n",
            $r['pendek'],
            $r['jadual'],
            implode(', ', array_slice($r['kolum'], 0, 6)) . (count($r['kolum']) > 6 ? ' …' : '')
        );
    }
    if (count($takSefasa) > 20) {
        echo '   … dan ' . (count($takSefasa) - 20) . ' lagi' . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($fkRosak !== []) {
    echo "❌ FK MERUJUK JADUAL TIDAK WUJUD (INSERT akan gagal):" . PHP_EOL;
    foreach (array_slice($fkRosak, 0, 15) as $r) {
        echo '   • ' . $r . PHP_EOL;
    }
    if (count($fkRosak) > 15) {
        echo '   … dan ' . (count($fkRosak) - 15) . ' lagi' . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($softDeletesRosak !== []) {
    echo "❌ MODEL GUA SoftDeletes TETAPI TIADA KOLUM deleted_at:" . PHP_EOL;
    foreach ($softDeletesRosak as $r) {
        echo '   • ' . $r . PHP_EOL;
    }
    echo PHP_EOL;
}

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

// ⚠️ 27 Okt 2026 (P1-1d): liputan kini 59/59 — keputusan audit §7.2
// ("setiap modul MESTI ada ujian pengasingan → CI merah") dikuatkuasakan.
// Modul baharu TANPA ujian pengasingan akan menggagalkan audit (dan CI).
$gagal = count($bocor) + count($tanpaKolom) + count($fkRosak) + count($softDeletesRosak)
    + count($takSefasa) + count($kaedahHantu) + count($tiadaUjianIsolasi);

echo str_repeat('-', 66) . PHP_EOL;
if ($gagal === 0) {
    echo '✅ Tiada isu model/skema. Liputan ujian pengasingan: '
        . count($adaUjianIsolasi) . '/' . count($modul) . ' modul.' . PHP_EOL;
} else {
    if ($tiadaUjianIsolasi !== []) {
        echo '❌ ' . count($tiadaUjianIsolasi) . ' modul tiada ujian pengasingan'
            . ' (tambah tests/Feature/Pengasingan/<Modul>*IsolationTest.php).' . PHP_EOL;
    }
    $isuLain = $gagal - count($tiadaUjianIsolasi);
    if ($isuLain > 0) {
        echo "❌ {$isuLain} isu pengasingan/skema mesti dibetulkan." . PHP_EOL;
    }
}
echo str_repeat('-', 66) . PHP_EOL;

exit($gagal === 0 ? 0 : 1);
