<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-1b — Pembetulan dua pepijat skema ditemui 27 Okt 2026.
 *
 * ─────────────────────────────────────────────────────────────────────
 * PEPIJAT 1 — Nombor dokumen bertindih merentas tenant
 * ─────────────────────────────────────────────────────────────────────
 * Enam model menjana nombor dokumen dengan corak:
 *
 *     self::whereYear('created_at', $year)->orderBy('id','desc')->first()
 *     ↑ `self::` DITAPIS oleh global scope `tenant` → setiap tenant mula dari 1
 *
 * Tetapi index DB ialah UNIQUE **GLOBAL**:
 *
 *     CREATE UNIQUE INDEX invoices_invoice_number_unique ON invoices(invoice_number)
 *
 * Kesannya: tenant A cipta `INV-2026-00001` ✓ → tenant B juga jana
 * `INV-2026-00001` → **UNIQUE violation** → tenant B **tidak boleh menerbitkan
 * invois sama sekali**.
 *
 * Sama seperti pepijat `JournalEntry` (P0-6) tetapi 6 kali ganda.
 *
 * PEMBETULAN: jadikan index `UNIQUE(tenant_id, <kolum_nombor>)`. Ini membenarkan
 * setiap tenant mempunyai siri nombornya sendiri — corak perakaunan Malaysia
 * yang betul (setiap entiti lapor cukai berasingan).
 *
 * Penjana nombor dalam model kemudian mesti DIBUANG `whereYear` rentas-tenant
 * dan sebaliknya menggunakan skop tenant (lihat perubahan model).
 *
 * ─────────────────────────────────────────────────────────────────────
 * PEPIJAT 2 — `SoftDeletes` tanpa kolum `deleted_at`
 * ─────────────────────────────────────────────────────────────────────
 * Empat model menggunakan trait `SoftDeletes` tetapi jadualnya tiada kolum
 * `deleted_at`. Setiap query gagal dengan:
 *
 *     SQLSTATE[HY000]: General error: 1 no such column: quotations.deleted_at
 *
 * Antaranya `quotations` — modul Invoicing tidak boleh query sebut harga
 * sama sekali. Ini pepijat pengeluaran sebenar.
 *
 * PEMBETULAN: tambah kolum `deleted_at` (nullable, berindeks) pada 4 jadual.
 *
 * ⚠️ Idempoten: semak `hasColumn` sebelum tambah, jadi migration ini selamat
 * dijalankan pada DB yang sudah separa betul.
 */
return new class extends Migration
{
    /** Jadual → kolum nombor unik per tenant. */
    private const NOMBOR = [
        'invoices' => 'invoice_number',
        'quotations' => 'quotation_number',
        'sales_orders' => 'order_number',
        'receipts' => 'receipt_number',
        'credit_notes' => 'credit_note_number',
        'debit_notes' => 'debit_note_number',
    ];

    /** Jadual yang model guna SoftDeletes tetapi kolum tiada. */
    private const SOFT_DELETE = [
        'quotations',
        'analytics_report_runs',
        'brainhub_forecasts',
        'brainhub_recommendations',
    ];

    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. deleted_at pada jadual yang memerlukannya
        // ---------------------------------------------------------------
        foreach (self::SOFT_DELETE as $jadual) {
            if (! Schema::hasTable($jadual) || Schema::hasColumn($jadual, 'deleted_at')) {
                continue;
            }

            Schema::table($jadual, function (Blueprint $t) {
                $t->softDeletes();
            });
        }

        // ---------------------------------------------------------------
        // 2. Tukar index nombor → UNIQUE(tenant_id, kolum)
        // ---------------------------------------------------------------
        foreach (self::NOMBOR as $jadual => $kolum) {
            if (! Schema::hasTable($jadual) || ! Schema::hasColumn($jadual, $kolum)) {
                continue;
            }

            // Buang index UNIQUE global lama (nama konvensyen Laravel)
            $global = "{$jadual}_{$kolum}_unique";

            try {
                Schema::table($jadual, function (Blueprint $t) use ($global) {
                    $t->dropUnique($global);
                });
            } catch (\Throwable $e) {
                // Sudah tiada (idempoten) atau MySQL guna nama berbeza — teruskan.
            }

            // Tambah UNIQUE(tenant_id, kolum) — hanya jika belum ada
            $komposit = "{$jadual}_tenant_{$kolum}_unique";

            try {
                Schema::table($jadual, function (Blueprint $t) use ($kolum, $komposit) {
                    $t->unique(['tenant_id', $kolum], $komposit);
                });
            } catch (\Throwable $e) {
                // Sudah wujud (idempoten).
            }
        }
    }

    public function down(): void
    {
        // Kembalikan UNIQUE global & buang deleted_at.
        foreach (self::NOMBOR as $jadual => $kolum) {
            if (! Schema::hasTable($jadual)) {
                continue;
            }

            $komposit = "{$jadual}_tenant_{$kolum}_unique";
            try {
                Schema::table($jadual, fn (Blueprint $t) => $t->dropUnique($komposit));
            } catch (\Throwable $e) {
            }

            $global = "{$jadual}_{$kolum}_unique";
            try {
                Schema::table($jadual, fn (Blueprint $t) => $t->unique($kolum, $global));
            } catch (\Throwable $e) {
            }
        }

        foreach (self::SOFT_DELETE as $jadual) {
            if (Schema::hasTable($jadual) && Schema::hasColumn($jadual, 'deleted_at')) {
                Schema::table($jadual, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }
    }
};
