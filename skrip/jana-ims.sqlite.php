<?php

/**
 * jana-ims.sqlite.php — pulihkan fail migration yang HILANG daripada DDL sebenar
 * DB SQLite semasa (T-109, 28 Okt 2026).
 *
 * Konteks: `migrate:status` melaporkan 84 migrasi `Ran`, tetapi
 * `database/migrations/` hanya ada 56 fail. 28 migrasi (payroll_*, pos_*,
 * hrms_*, booking_*, marketplace_*, sites, analytics_*, chart_of_accounts,
 * journal_*, vo_*, suppliers, purchase_orders, customers/leads/opportunities,
 * document_ai, sales_orders, invoices, credit/debit notes, recurring_billing)
 * wujud dalam DB tanpa fail → `migrate:fresh` membina DB TIDAK LENGKAP.
 *
 * Skrip ini menjana semula `up()`/`down()` berdasarkan DDL **verbatim** daripada
 * `sqlite_master`, dengan token FK ditulis semula (`__FK__:<jadual>:<kolum>`) dan
 * diselesaikan melalui parameter jenis `Schema\Blueprint` semasa migrate —
 * ini mengelakkan masalah urutan pembinaan semula SQLite yang mengosongkan
 * rujukan jadual yang belum wujud.
 *
 * Guna:
 *   php skrip/jana-ims.sqlite.php                 # laporan sahaja
 *   php skrip/jana-ims.sqlite.php --tulis         # tulis 28 fail
 *   php skrip/jana-ims.sqlite.php --sahkan <db>   # banding skema DB <db> dengan DB semasa
 *
 * ⚠️ SQLite sahaja. Untuk MySQL/PostgreSQL, tulis migration dengan Blueprint
 * biasa (lihat TODO T-109 langkah 3).
 */

require __DIR__ . '/../vendor/autoload.php';

$arg = $argv ?? [];
$tulis = in_array('--tulis', $arg, true);
$sah = null;
$sumber = null;
foreach ($arg as $a) {
    if (str_starts_with($a, '--sahkan=')) {
        $sah = substr($a, strlen('--sahkan='));
    }
    if (str_starts_with($a, '--db=')) {
        $sumber = substr($a, strlen('--db='));
    }
}

// ⚠️ DB SUMBER boleh dinyatakan (`--db=`) — JANGAN bergantung pada `.env`/DB dev
// yang boleh diubah oleh proses lain. Untuk penjanaan semula, gunakan salinan
// skema yang sudah disahkan (`storage/app/skema-rujukan/…`).
if ($sumber !== null) {
    if (! is_file($sumber)) {
        fwrite(STDERR, "FAIL: DB sumber tidak dijumpai: {$sumber}\n");
        exit(1);
    }
    // Laravel membaca `.env` dahulu; pemboleh ubah proses TIDAK mencukupi.
    // `skrip/jana-ims.sqlite.php` mengubah suai DB, jadi sentiasa beri DB eksplisit.
    $_SERVER['DB_CONNECTION'] = $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_DATABASE'] = $_ENV['DB_DATABASE'] = $sumber;
    putenv("DB_CONNECTION=sqlite");
    putenv("DB_DATABASE={$sumber}");
}

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (DB::getDriverName() !== 'sqlite') {
    fwrite(STDERR, "FAIL: skrip ini hanya untuk SQLite (driver semasa: " . DB::getDriverName() . ").\n");
    exit(1);
}

// ── Pemetaan migration → jadual (dalam URUTAN pembinaan) ───────────────────
$peta = [
    '2026_10_19_000001_create_payroll_statutory_rates' => ['statutory_rates'],
    '2026_10_19_000002_create_payroll_employees' => ['employees'],
    '2026_10_19_000003_create_payroll_overtime_records' => ['overtime_records'],
    '2026_10_19_000004_create_payroll_runs' => ['payroll_runs'],
    '2026_10_19_000005_create_payroll_run_details' => ['payroll_run_details'],
    '2026_10_19_000010_create_chart_of_accounts' => ['chart_of_accounts'],
    '2026_10_19_000011_create_journal_entries' => ['journal_entries', 'journal_entry_lines'],
    '2026_10_19_000012_create_accounts_payable_receivable' => ['accounts_payable', 'accounts_receivable', 'payments'],
    '2026_10_19_000013_create_fixed_assets' => ['fixed_assets', 'depreciation_schedule'],
    '2026_10_19_000020_create_suppliers' => ['suppliers', 'supplier_contacts'],
    '2026_10_19_000021_create_purchase_orders' => ['purchase_orders', 'purchase_order_lines', 'purchase_order_approvals', 'goods_received_notes', 'goods_received_note_lines'],
    '2026_10_19_000030_create_customers_leads_opportunities' => ['customers', 'leads', 'opportunities', 'customer_contacts'],
    // Jadual sudah wujud dalam `2026_10_27_000100_create_missing_model_tables.php`
    // (dokumen AI) — migration asal sehingga tidak menambah jadual baharu.
    '2026_10_19_000040_create_document_ai_tables' => [],
    '2026_10_19_000051_create_sales_orders_table' => ['sales_orders', 'sales_order_items', 'quotations', 'quotation_lines'],
    '2026_10_19_000052_create_invoices_table' => ['invoices', 'invoice_items'],
    '2026_10_19_000053_create_credit_debit_notes_receipts_table' => ['credit_notes', 'debit_notes', 'receipts'],
    '2026_10_19_000054_create_recurring_billings_table' => ['recurring_billings'],
    '2026_10_19_000100_create_vo_agents_table' => ['vo_agents'],
    '2026_10_19_000101_create_vo_boards_tables' => ['vo_boards', 'vo_columns', 'vo_cards', 'vo_board_members', 'vo_card_comments', 'vo_card_history'],
    '2026_10_19_000102_create_vo_schedules_tables' => ['vo_schedules', 'vo_schedule_attendees'],
    '2026_10_19_000200_create_hrms_core_tables' => ['hrms_'],
    '2026_10_19_000201_create_hrms_leave_tables' => ['hrms_leave', 'hrms_attendance', 'hrms_overtime', 'hrms_holidays'],
    '2026_10_19_000500_create_pos_tables' => ['pos_', 'cash_shift'],
    '2026_10_19_000600_create_booking_tables' => ['booking'],
    '2026_10_20_000700_create_marketplace_tables' => ['marketplace_'],
    '2026_10_20_000800_create_site_tables' => ['site', 'sites'],
    '2026_10_20_001600_create_analytics_tables' => ['analytics_', 'dashboard_widgets'],
    '2026_10_27_001000_add_bpos_features_to_pos' => ['pos_'],
];

// ── Sahkan mod ────────────────────────────────────────────────────────────
if ($sah !== null) {
    // ⚠️ PDO terus untuk kedua-dua fail. DB DIUJI = sambungan Laravel semasa
    // (`DB_DATABASE` proses), DB RUJUKAN = `--sahkan`. Jangan bergantung pada
    // pemboleh ubah proses untuk menukar DB — ia dibaca SEBELUM bootstrap.
    $pdoUji = new PDO('sqlite:' . DB::getDatabaseName());
    $pdoRuj = new PDO('sqlite:' . $sah);
    $pdoRuj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "select type, name, sql from sqlite_master where sql is not null and name not like 'sqlite_%' order by type, name";
    $rujukan = [];
    foreach ($pdoRuj->query($sql)->fetchAll(PDO::FETCH_OBJ) as $r) {
        $rujukan[$r->type . '|' . $r->name] = $r->sql;
    }
    $diuji = [];
    foreach ($pdoUji->query($sql)->fetchAll(PDO::FETCH_OBJ) as $r) {
        $diuji[$r->type . '|' . $r->name] = $r->sql;
    }

    echo 'DB diuji ....... ' . DB::getDatabaseName() . "\n";
    echo 'DB rujukan ..... ' . $sah . "\n";

    $hilang = array_diff_key($rujukan, $diuji);   // ada dalam rujukan, tiada dalam diuji
    $lebih = array_diff_key($diuji, $rujukan);    // ada dalam diuji, tiada dalam rujukan
    $beza = [];
    foreach ($rujukan as $k => $v) {
        if (isset($diuji[$k]) && $diuji[$k] !== $v) {
            $beza[$k] = [$v, $diuji[$k]];
        }
    }

    echo 'Objek rujukan ... ' . count($rujukan) . "\n";
    echo 'Objek diuji ..... ' . count($diuji) . "\n";
    echo 'Hilang ......... ' . count($hilang) . "\n";
    echo 'Berlebihan ..... ' . count($lebih) . "\n";
    echo 'SQL berbeza .... ' . count($beza) . "\n";

    foreach (array_slice(array_keys($hilang), 0, 10) as $k) {
        echo "  ❌ hilang: {$k}\n";
    }
    foreach (array_slice(array_keys($lebih), 0, 10) as $k) {
        echo "  ⚠️ berlebihan: {$k}\n";
    }
    foreach (array_slice(array_keys($beza), 0, 5) as $k) {
        echo "  ≠ {$k}\n    jangkaan: " . substr($beza[$k][0], 0, 160) . "\n    diperoleh: " . substr($beza[$k][1], 0, 160) . "\n";
    }

    exit($hilang === [] && $beza === [] ? 0 : 1);
}

// ── Penjanaan ─────────────────────────────────────────────────────────────

$master = [];
foreach (DB::select("select type, name, sql from sqlite_master where sql is not null and name not like 'sqlite_%' order by name") as $o) {
    $master[$o->name] = $o;
}

// Jadual yang sudah dimiliki migration SEDIA ADA — jangan disentuh.
// ⚠️ `$dimiliki` HANYA diisi daripada migration asal; migration yang dijana oleh
// skrip ini SENGAJA dikecualikan (jika tidak, jana semula akan melangkau semuanya).
$dimiliki = [];
foreach (glob(__DIR__ . '/../database/migrations/*.php') as $f) {
    $namaFail = basename($f, '.php');
    $dijanaSebelumIni = false;
    foreach (array_keys($peta) as $migrasiPeta) {
        if ($namaFail === $migrasiPeta) {
            $dijanaSebelumIni = true;
            break;
        }
    }
    if ($dijanaSebelumIni) {
        continue;
    }

    $isi = (string) file_get_contents($f);
    if (preg_match_all("/Schema::create\(\s*'([a-z_0-9]+)'/i", $isi, $mm) > 0) {
        foreach ($mm[1] as $nama) {
            $dimiliki[$nama] = true;
        }
    }
    // Jadual sebenar dalam migration ini: nama + jadual dalaman SQLite + nama FK.
    if (preg_match_all('/CREATE\s+TABLE\s+"?([a-z_0-9]+)"?/i', $isi, $mm) > 0) {
        foreach ($mm[1] as $nama) {
            $dimiliki[$nama] = true;
        }
    }
    if (preg_match_all('/references\s+"([a-z_0-9]+)"/i', $isi, $mm) > 0) {
        foreach ($mm[1] as $nama) {
            $dimiliki[$nama] = true;
        }
    }
    if (preg_match_all('/ALTER\s+TABLE\s+"?([a-z_0-9]+)"?/i', $isi, $mm) > 0) {
        foreach ($mm[1] as $nama) {
            $dimiliki[$nama] = true;
        }
    }
}

$padan = static function (string $migrasi, string $nama) use ($peta): bool {
    $pola = $peta[$migrasi] ?? [];

    // Jadual tunggal yang TIDAK berkongsi awalan dengan pola (kes sebenar:
    // `bookings_appointments` untuk `booking`, `cash_shifts` untuk `cash_shift`).
    $tunggal = [
        'booking' => ['bookings_appointments'],
        'cash_shift' => ['cash_shifts'],
    ];
    if (in_array($nama, $tunggal[$migrasi] ?? [], true)) {
        return true;
    }

    // 1) Padanan TEPAT — diutamakan supaya `employees` tidak "menelan" jadual lain.
    if (in_array($nama, $pola, true)) {
        return true;
    }

    foreach ($pola as $p) {
        // 2) Pola berakhir `_` = awalan tulen (`hrms_`, `pos_`).
        if (str_ends_with($p, '_') && str_starts_with($nama, $p)) {
            return true;
        }
        // 3) Pola bukan jamak → awalan berpangkah (`booking` → `booking_slots`,
        //    `cash_shift` → `cash_shifts`) TETAPI bukan `bookings_*`.
        if (! str_ends_with($p, '_') && str_starts_with($nama, $p . '_')) {
            return true;
        }
        // 4) Pola jamak → awalan tunggal berpangkah (`sites` → `site_pages`).
        if (! str_ends_with($p, '_') && str_ends_with($p, 's') && ! str_ends_with($p, 'ss') && str_starts_with($nama, substr($p, 0, -1) . '_')) {
            return true;
        }
    }

    return false;
};

if (getenv('IMS_DEBUG') === '1') {
    echo 'DEBUG master=' . count($master) . ' dimiliki=' . count($dimiliki) . "\n";
    echo 'DEBUG dimiliki sample: ' . implode(',', array_slice(array_keys($dimiliki), 0, 8)) . "\n";
    echo 'DEBUG chart_of_accounts: type=' . ($master['chart_of_accounts']->type ?? 'TIADA')
        . ' dimiliki=' . (isset($dimiliki['chart_of_accounts']) ? 'YA' : 'TIDAK') . "\n";
}

/**
 * `true` jika migration LAIN mempunyai padanan TEPAT untuk jadual ini.
 * Padanan tepat menang atas padanan awalan (`payroll_run_details` dimiliki
 * migration 000005, bukan `payroll_runs` 000004 yang menyamai `payroll_run_*`).
 */
$padanLain = static function (string $migrasi, string $nama) use ($peta): bool {
    foreach ($peta as $m => $pola) {
        if ($m === $migrasi) {
            continue;
        }
        if (in_array($nama, $pola, true)) {
            return true;
        }
    }

    return false;
};

$dijana = [];
$dituntut = [];   // jadual yang sudah diambil migration lebih awal dalam peta
foreach ($peta as $migrasi => $pola) {
    $jadual = [];
    foreach ($master as $nama => $o) {
        if ($o->type === 'table' && ! isset($dimiliki[$nama]) && ! isset($dituntut[$nama]) && ! $padanLain($migrasi, $nama) && $padan($migrasi, $nama)) {
            $jadual[] = $nama;
        }
    }
    sort($jadual);
    // Tanda TEPAT selepas pengiraan (sebelum ini `$dijana[$migrasi]` ditulis di
    // hujung gelung, jadi jadual yang dituntut tidak pernah direkod → jadual
    // payroll_* tercipta dua kali dan `migrate` gagal "already exists").
    foreach ($jadual as $j) {
        $dituntut[$j] = true;
    }
    $indeks = [];
    foreach ($master as $nama => $o) {
        if ($o->type !== 'index' || ! str_starts_with((string) $o->sql, 'CREATE INDEX')) {
            continue;
        }
        if (preg_match('/\bon\s+"?([a-z_0-9]+)"?\s*\(/i', (string) $o->sql, $mm) === 1
            && in_array($mm[1], $jadual, true)) {
            $indeks[] = $nama;
        }
    }
    sort($indeks);

    $dijana[$migrasi] = ['jadual' => $jadual, 'indeks' => $indeks];
}

$kosong = [];
foreach ($dijana as $migrasi => $isi) {
    printf("%-62s jadual=%2d indeks=%2d\n", $migrasi, count($isi['jadual']), count($isi['indeks']));
    if ($isi['jadual'] === [] && $isi['indeks'] === [] && ($peta[$migrasi] ?? []) !== []) {
        $kosong[] = $migrasi;
    }
}

// Jelas: jadual ini sudah dipegang migration lain dalam peta (jangan tulis semula).
if ($kosong !== []) {
    echo "\nℹ️  Tiada jadual baharu (sudah dipegang migration lain):\n  - " . implode("\n  - ", $kosong) . "\n";
    $kosong = [];   // bukan ralat — jadualnya ditulis oleh migration lain
}

if ($kosong !== [] && ! $tulis) {
    // Bantu pemetaan: senaraikan jadual yang BELUM dituntut oleh mana-mana peta.
    foreach ($dijana as $isi) {
        foreach ($isi['jadual'] as $j) {
            $dituntut[$j] = true;
        }
    }
    echo "\nJadual tanpa migration (calon untuk pemetaan):\n";
    $n = 0;
    foreach ($master as $nama => $o) {
        if ($o->type === 'table' && ! isset($dimiliki[$nama]) && ! isset($dituntut[$nama])) {
            echo '  - ' . $nama . "\n";
            $n++;
        }
    }
    echo "  (jumlah {$n})\n";
}

echo "\nJadual dijana ....... " . array_sum(array_map(fn ($i) => count($i['jadual']), $dijana)) . "\n";
echo 'Indeks dijana ....... ' . array_sum(array_map(fn ($i) => count($i['indeks']), $dijana)) . "\n";

if ($kosong !== []) {
    echo "\n⚠️ Pemetaan perlu disemak (tiada jadual):\n  - " . implode("\n  - ", $kosong) . "\n";
}

if (! $tulis) {
    echo "\n(laporan sahaja — guna --tulis untuk menulis fail)\n";
    exit($kosong === [] ? 0 : 1);
}

// ── Penulis fail ──────────────────────────────────────────────────────────

$semuaJadual = [];
foreach ($dijana as $isi) {
    foreach ($isi['jadual'] as $j) {
        $semuaJadual[$j] = true;
    }
}

$fkUntuk = static function (string $sql, string $jadual): array {
    $senarai = [];
    if (preg_match_all('/foreign key\(\s*"([^"]+)"\s*\)\s*references\s+"([^"]+)"\s*\(\s*"([^"]+)"\s*\)(\s+on delete\s+\w+(\s+\w+)?)?/i', $sql, $mm, PREG_SET_ORDER) > 0) {
        foreach ($mm as $m) {
            $senarai[] = [
                'kolum' => $m[1],
                'jadual' => $m[2],
                'rujukan' => $m[3],
                'on_delete' => trim((string) ($m[4] ?? '')),
            ];
        }
    }

    return $senarai;
};

$tulisSatu = static function (string $migrasi, array $jadual, array $indeks) use ($master, $semuaJadual, $dimiliki, $fkUntuk): string {
    $sqlJadual = [];
    foreach ($jadual as $j) {
        $sqlJadual[$j] = (string) $master[$j]->sql;
    }
    $sqlIndeks = [];
    foreach ($indeks as $i) {
        $sqlIndeks[$i] = (string) $master[$i]->sql;
    }

    // Urutkan jadual mengikut kebergantungan DALAM migration ini: induk dahulu,
    // anak kemudian (SQLite enggan rujuk jadual yang belum wujud).
    $indukDalam = static function (string $jadual, string $sqlJadualan) use ($sqlJadual): array {
        $induk = [];
        if (preg_match_all('/foreign key\(\s*"[^"]+"\s*\)\s*references\s+"([^"]+)"/i', $sqlJadualan, $mm) > 0) {
            foreach ($mm[1] as $r) {
                if ($r !== $jadual && isset($sqlJadual[$r])) {
                    $induk[$r] = true;
                }
            }
        }

        return array_keys($induk);
    };

    $susunan = array_keys($sqlJadual);
    usort($susunan, static function (string $a, string $b) use ($sqlJadual, $indukDalam): int {
        $ia = in_array($b, $indukDalam($a, $sqlJadual[$a]), true);   // a perlukan b
        $ib = in_array($a, $indukDalam($b, $sqlJadual[$b]), true);   // b perlukan a
        if ($ia && ! $ib) {
            return 1;    // a selepas b
        }
        if ($ib && ! $ia) {
            return -1;   // a dahulu
        }

        return 0;
    });
    $sqlJadual = array_merge(array_flip($susunan), $sqlJadual);
    $sqlJadual = [];
    foreach ($susunan as $j) {
        $sqlJadual[$j] = (string) $master[$j]->sql;
    }

    $tanpaFk = static function (string $sql): string {
        return (string) preg_replace('/,\s*foreign key\([^)]*\)\s*references\s+"[^"]+"\s*\(\s*"[^"]+"\s*\)(\s+on delete\s+\w+(\s+\w+)?)?/i', '', $sql);
    };
    $susun = '';
    $pasca = [];   // jadual → DDL lengkap dengan FK (dibina semula selepas fasa 1)

    // Jadual induk yang BOLEH dirujuk: dalam migration ini ATAU sudah dimiliki
    // migration terdahulu. Rujukan kepada jadual migration berikutnya akan gagal
    // (SQLite enggan FK kepada jadual yang belum wujud). Semua FK kemudian
    // dipulihkan oleh migration `2026_10_28_000900_pulih_foreign_key_t109.php`.
    $bolehRujuk = static function (string $induk) use ($sqlJadual, $dimiliki): bool {
        return isset($sqlJadual[$induk]) || isset($dimiliki[$induk]);
    };

    foreach ($sqlJadual as $j => $sql) {
        $fk = $fkUntuk($sql, $j);
        $dalaman = array_values(array_filter($fk, fn ($k) => isset($sqlJadual[$k['jadual']]) || $bolehRujuk($k['jadual'])));

        if ($dalaman === []) {
            $susun .= '        DB::unprepared(' . var_export($sql, true) . ");\n";

            continue;
        }

        // Kekalkan URUTAN FK asal (penting: `sqlite_master` menyimpan teks DDL
        // dan perbandingan skema sensitif kepada susunan).
        $lengkap = $sql;
        $dibuang = [];
        foreach ($fk as $k) {
            if (! in_array($k, $dalaman, true)) {
                $dibuang[] = $k;
            }
        }
        foreach ($dibuang as $k) {
            // Buang HANYA FK yang induknya belum wujud, gunakan corak tepat.
            $pola = '/,\s*foreign key\("' . preg_quote($k['kolum'], '/') . '"\)\s*references\s+"'
                . preg_quote($k['jadual'], '/') . '"\s*\(\s*"' . preg_quote($k['rujukan'], '/')
                . '"\s*\)(\s+on delete\s+\w+(\s+\w+)?)?/i';
            $lengkap = (string) preg_replace($pola, '', $lengkap);
        }

        // Fasa 1: cipta tanpa FK yang belum boleh diselesaikan.
        $susun .= '        DB::unprepared(' . var_export($lengkap, true) . ");\n";
        if ($dibuang !== []) {
            $pasca[$j] = $sql;   // DDL asal penuh — dibina semula oleh migration pemulih FK.
        }
    }

    // Fasa 2 dipindahkan ke `2026_10_28_000900_pulih_foreign_key_t109.php`:
    // FK silang-migration hanya boleh dipulihkan SELEPAS semua jadual wujud.
    $binaIndeks = '';
    foreach ($sqlIndeks as $sql) {
        // `IF NOT EXISTS`: indeks yang sudah dicipta oleh `Schema::create`
        // (kekangan `unique()`/`index()` Blueprint) tidak boleh dicipta dua kali.
        $sqlAman = preg_replace('/^CREATE\s+(UNIQUE\s+)?INDEX\s+/i', 'CREATE $1INDEX IF NOT EXISTS ', $sql);
        $binaIndeks .= '        DB::statement(' . var_export($sqlAman, true) . ");   // best-effort: mungkin sudah wujud\n";
    }

    $turun = '';
    foreach (array_reverse(array_keys($sqlJadual)) as $j) {
        $turun .= "        Schema::dropIfExists('{$j}');\n";
    }

    $nota = '';
    foreach ($sqlJadual as $j => $sql) {
        foreach ($fkUntuk($sql, $j) as $k) {
            if (! isset($sqlJadual[$k['jadual']]) && ! isset($semuaJadual[$k['jadual']])) {
                $nota .= " *   - {$j}.{$k['kolum']} → {$k['jadual']}.{$k['rujukan']} (jadual induk tidak wujud)\n";
            } elseif (! isset($sqlJadual[$k['jadual']])) {
                $nota .= " *   - {$j}.{$k['kolum']} → {$k['jadual']}.{$k['rujukan']} (induk dalam migration lain)\n";
            }
        }
    }

    $senaraiJ = '`' . implode('`, `', array_keys($sqlJadual)) . '`';

    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `{$migrasi}` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: {$senaraiJ}.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
{$nota} *
 * JANGAN sunting DDL di bawah secara manual — tambah migration baharu untuk
 * sebarang perubahan skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Jadual anak dibina semula SEKALI dengan FK dalaman: bina tanpa FK dahulu
        // hanya jika induk dalam migration yang sama, kemudian DROP + CREATE semula
        // (SQLite tidak menyokong ADD CONSTRAINT).

{$susun}
{$binaIndeks}    }

    public function down(): void
    {
{$turun}    }
};

PHP;
};

$bil = 0;
$pascaSemua = [];
foreach ($dijana as $migrasi => $isi) {
    if ($isi['jadual'] === [] && $isi['indeks'] === []) {
        continue;
    }

    $fail = __DIR__ . '/../database/migrations/' . $migrasi . '.php';
    if (is_file($fail)) {
        echo "⚠️  sudah wujud, dilangkau: " . basename($fail) . "\n";

        continue;
    }

    $lama = $pasca ?? [];
    file_put_contents($fail, $tulisSatu($migrasi, $isi['jadual'], $isi['indeks']));
    $bil++;
}

echo "\nDitulis: {$bil} fail migration.\n";
echo "Seterusnya: `php artisan migrate:fresh --seed` pada DB ujian, kemudian\n";
echo "`php skrip/jana-ims.sqlite.php --sahkan=<db-ujian>` (mesti 0 hilang, 0 beza).\n";
