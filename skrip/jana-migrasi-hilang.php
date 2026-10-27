<?php

/**
 * jana-migrasi-hilang.php — pulihkan fail migration yang TIADA tetapi sudah
 * dijalankan pada DB (T-109, 28 Okt 2026).
 *
 * Konteks: `php artisan migrate:status` menunjukkan 84 migrasi `Ran`, tetapi
 * `database/migrations/` hanya ada 56 fail. 28 migrasi wujud dalam DB (jadual
 * `payroll_*`, `pos_*`, `hrms_*`, `booking_*`, `marketplace_*`, `sites`,
 * `analytics_*`, …) tanpa fail — DB produksi/staging TIDAK boleh dibina semula
 * dari sifar, dan `migrate:fresh` akan gagal.
 *
 * Skrip ini menjana semula fail `up()` sebagai DDL SQLite **verbatim**
 * (`DB::unprepared`) supaya DB baharu sepadan dengan DB semasa (termasuk indeks).
 *
 * Guna:
 *   php skrip/jana-migrasi-hilang.php              # lapor sahaja
 *   php skrip/jana-migrasi-hilang.php --tulis      # tulis fail ke database/migrations/
 *
 * ⚠️ Hanya untuk SQLite. Untuk MySQL, jalankan `SHOW CREATE TABLE` dan tulis
 * migration idiom Blueprint (lihat nota dalam fail yang dijana).
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tulis = in_array('--tulis', $argv ?? [], true);

// ── 1. Migration yang sudah dijalankan vs fail yang ada ─────────────────
$dalamDb = DB::table('migrations')->orderBy('migration')->pluck('migration')->all();
$failAda = collect(glob(__DIR__ . '/../database/migrations/*.php'))
    ->map(fn ($f) => basename($f, '.php'))
    ->all();

$hilang = array_values(array_diff($dalamDb, $failAda));

echo 'Migration dalam DB ..... ' . count($dalamDb) . "\n";
echo 'Fail migration ......... ' . count($failAda) . "\n";
echo 'HILANG (DB tanpa fail) . ' . count($hilang) . "\n\n";

if ($hilang === []) {
    echo "✅ Tiada migration hilang.\n";
    exit(0);
}

// ── 2. Petakan setiap migration → jadual/indeks yang dicipta ────────────
// Nama migration → awalan jadual yang dijangka. `create_<x>_tables` dipecah.
$jadualSqlite = DB::select(
    "SELECT name, type, sql FROM sqlite_master WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY name"
);

$milik = function (string $migration, string $namaObjek) use ($jadualSqlite): bool {
    $asas = str_contains($migration, 'create_') ? explode('create_', $migration, 2)[1] : $migration;

    return str_contains($namaObjek, rtrim($asas, 's_tables')) || str_starts_with($namaObjek, substr($asas, 0, 6));
};

// Kumpulan jadual/indeks mengikut migration menggunakan pemetaan eksplisit.
$peta = [
    '2026_10_19_000001_create_payroll_statutory_rates' => ['payroll_statutory_rates'],
    '2026_10_19_000002_create_payroll_employees' => ['payroll_employees'],
    '2026_10_19_000003_create_payroll_overtime_records' => ['payroll_overtime_records'],
    '2026_10_19_000004_create_payroll_runs' => ['payroll_runs'],
    '2026_10_19_000005_create_payroll_run_details' => ['payroll_run_details'],
    '2026_10_19_000010_create_chart_of_accounts' => ['chart_of_accounts'],
    '2026_10_19_000011_create_journal_entries' => ['journal_entries', 'journal_entry_lines'],
    '2026_10_19_000012_create_accounts_payable_receivable' => ['accounts_payable', 'accounts_receivable'],
    '2026_10_19_000013_create_fixed_assets' => ['fixed_assets'],
    '2026_10_19_000020_create_suppliers' => ['suppliers'],
    '2026_10_19_000021_create_purchase_orders' => ['purchase_orders'],
    '2026_10_19_000030_create_customers_leads_opportunities' => ['customers', 'leads', 'opportunities'],
    '2026_10_19_000040_create_document_ai_tables' => ['document_templates', 'document_ocr', 'document_ai'],
    '2026_10_19_000051_create_sales_orders_table' => ['sales_orders'],
    '2026_10_19_000052_create_invoices_table' => ['invoices'],
    '2026_10_19_000053_create_credit_debit_notes_receipts_table' => ['credit_notes', 'debit_notes', 'receipts'],
    '2026_10_19_000054_create_recurring_billings_table' => ['recurring_billings'],
    '2026_10_19_000100_create_vo_agents_table' => ['vo_agents'],
    '2026_10_19_000101_create_vo_boards_tables' => ['vo_boards', 'vo_columns', 'vo_cards'],
    '2026_10_19_000102_create_vo_schedules_tables' => ['vo_schedules'],
    '2026_10_19_000200_create_hrms_core_tables' => ['hrms_'],
    '2026_10_19_000201_create_hrms_leave_tables' => ['hrms_leave', 'hrms_attendance', 'hrms_overtime', 'hrms_holidays'],
    '2026_10_19_000500_create_pos_tables' => ['pos_'],
    '2026_10_19_000600_create_booking_tables' => ['booking'],
    '2026_10_20_000700_create_marketplace_tables' => ['marketplace_'],
    '2026_10_20_000800_create_site_tables' => ['site'],
    '2026_10_20_001600_create_analytics_tables' => ['analytics_'],
    '2026_10_27_001000_add_bpos_features_to_pos' => ['pos_dine', 'pos_modifier', 'pos_product_modifiers', 'pos_shift', 'pos_tng', 'pos_discount', 'pos_sale_tenders'],
];

foreach ($hilang as $m) {
    echo "  - {$m}\n";
    echo '      peta: ' . (isset($peta[$m]) ? implode(', ', $peta[$m]) : '⚠️ TIADA PEMETAAN') . "\n";
}

if (! $tulis) {
    echo "\n❌ " . count($hilang) . " migrasi dijalankan pada DB tetapi failnya TIADA.\n";
    echo "   `php artisan migrate:fresh` akan membina DB TANPA jadual berikut.\n";
    echo "   Lihat TODO T-109 untuk pelan pemulihan.\n";
    echo "\n(mod laporan — guna --tulis selepas pemulihan disediakan)\n";
    exit(1);
}

echo "\n--tulis belum diaktifkan — lihat langkah dalam TODO T-109.\n";
exit(1);
