<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P1-1b — Pembetulan 15 FOREIGN KEY merujuk jadual yang TIDAK WUJUD.
 *
 * Ditemui 27 Okt 2026 semasa ujian pengasingan tenant Invoicing:
 *
 *     SQLSTATE[HY000]: General error: 1 no such table: main.products
 *
 * `config/database.php` menetapkan `foreign_key_constraints => true`, jadi
 * SQLite MEMANG menguatkuasakan FK. `ALTER TABLE` SQLite **tidak boleh**
 * membuang constraint → jadual mesti dibina semula.
 *
 * PETA PEMBETULAN (jadual lama → sebenar):
 *
 *     products            → items               (modul Inventory)
 *     inventory_products  → items
 *     crm_clients         → customers
 *     crm_contacts        → customer_contacts
 *     contacts            → customers
 *     crm_deals           → marketing_deals
 *     orders              → unified_orders
 *
 * ⚠️ Jika jadual sasaran tidak wujud juga, FK dibuang (kolum kekal nullable)
 * supaya INSERT berfungsi. Integriti dijaga di lapisan aplikasi — corak sama
 * seperti jadual anak `journal_entry_lines`.
 */
return new class extends Migration
{
    /** jadual → [[kolum_fk, jadual_lama], ...] */
    private const ROSAK = [
        'sales_order_items' => [['product_id', 'products']],
        'invoice_items' => [['product_id', 'products']],
        'pos_sales' => [['client_id', 'crm_clients']],
        'pos_sale_items' => [['product_id', 'inventory_products']],
        'bookings_appointments' => [['client_id', 'crm_clients']],
        'marketing_contacts' => [['contact_id', 'crm_contacts']],
        'marketing_voucher_usage' => [['order_id', 'orders']],
        'marketing_deals' => [['contact_id', 'crm_contacts'], ['crm_deal_id', 'crm_deals']],
        'loyalty_members' => [['contact_id', 'crm_contacts']],
        'affiliate_referrals' => [['contact_id', 'contacts']],
        'affiliate_commissions' => [['contact_id', 'contacts']],
        'logistics_stock_moves' => [['product_id', 'products']],
        'cogs_cost_components' => [['product_id', 'products']],
        'cogs_product_costs' => [['product_id', 'products']],
        'cogs_transactions' => [['product_id', 'products']],
    ];

    private const PETA = [
        'products' => 'items',
        'inventory_products' => 'items',
        'crm_clients' => 'customers',
        'crm_contacts' => 'customer_contacts',
        'contacts' => 'customers',
        'crm_deals' => 'marketing_deals',
        'orders' => 'unified_orders',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->betulkanMysql();

            return;
        }

        foreach (self::ROSAK as $jadual => $pasangan) {
            if (! Schema::hasTable($jadual)) {
                continue;
            }

            $this->binaSemulaSqlite($jadual, $pasangan);
        }
    }

    /**
     * SQLite: bina semula jadual tanpa FK rosak.
     */
    private function binaSemulaSqlite(string $jadual, array $pasangan): void
    {
        $ddl = DB::selectOne(
            'select sql from sqlite_master where type = ? and name = ?',
            ['table', $jadual]
        );

        if (! $ddl || ! $ddl->sql) {
            return;
        }

        $sql = $ddl->sql;
        $berubah = false;

        foreach ($pasangan as [$kolum, $lama]) {
            $sasaran = self::PETA[$lama] ?? null;
            $wujud = $sasaran !== null && Schema::hasTable($sasaran);

            if ($wujud) {
                // Tukar rujukan ke jadual sebenar
                $baharu = str_replace(
                    'references "' . $lama . '"',
                    'references "' . $sasaran . '"',
                    $sql
                );
                if ($baharu !== $sql) {
                    $sql = $baharu;
                    $berubah = true;
                }

                continue;
            }

            // Tiada sasaran — buang klausa FK sepenuhnya.
            $corak = '/,\s*foreign key\s*\(\s*"' . preg_quote($kolum, '/') . '"\s*\)\s*references\s+"'
                . preg_quote($lama, '/') . '"\s*\([^)]*\)(\s+on\s+delete\s+\w+)?(\s+on\s+update\s+\w+)?/i';

            $baharu = preg_replace($corak, '', $sql);
            if ($baharu !== null && $baharu !== $sql) {
                $sql = $baharu;
                $berubah = true;
            }
        }

        if (! $berubah) {
            return;
        }

        // Kumpul index sedia ada
        $indeks = DB::select(
            "select name, sql from sqlite_master where type = 'index' and tbl_name = ? and sql is not null",
            [$jadual]
        );

        $sementara = $jadual . '__tmp_fk';

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            DB::transaction(function () use ($jadual, $sementara, $sql, $indeks) {
                $ddlBaharu = preg_replace(
                    '/^CREATE TABLE\s+"?' . preg_quote($jadual, '/') . '"?/i',
                    'CREATE TABLE "' . $sementara . '"',
                    $sql,
                    1
                );

                DB::statement($ddlBaharu);

                $kolum = array_map(
                    fn ($k) => '"' . $k->name . '"',
                    DB::select('select name from pragma_table_info(?)', [$jadual])
                );

                $senarai = implode(', ', $kolum);
                DB::statement(
                    'insert into "' . $sementara . '" (' . $senarai . ') '
                    . 'select ' . $senarai . ' from "' . $jadual . '"'
                );

                DB::statement('DROP TABLE "' . $jadual . '"');
                DB::statement('ALTER TABLE "' . $sementara . '" RENAME TO "' . $jadual . '"');

                foreach ($indeks as $i) {
                    try {
                        DB::statement($i->sql);
                    } catch (\Throwable $e) {
                        // Index sudah dicipta bersama DDL.
                    }
                }
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * MySQL/PostgreSQL: boleh ALTER sebenar.
     */
    private function betulkanMysql(): void
    {
        foreach (self::ROSAK as $jadual => $pasangan) {
            if (! Schema::hasTable($jadual)) {
                continue;
            }

            foreach ($pasangan as [$kolum, $lama]) {
                if (! Schema::hasColumn($jadual, $kolum)) {
                    continue;
                }

                try {
                    Schema::table($jadual, fn (Blueprint $t) => $t->dropForeign([$kolum]));
                } catch (\Throwable $e) {
                    // Tiada FK atau nama berbeza.
                }

                $sasaran = self::PETA[$lama] ?? null;
                if ($sasaran === null || ! Schema::hasTable($sasaran)) {
                    continue;
                }

                try {
                    Schema::table($jadual, function (Blueprint $t) use ($kolum, $sasaran) {
                        $t->foreign($kolum)->references('id')->on($sasaran)->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // Data sedia ada melanggar FK baharu.
                }
            }
        }
    }

    public function down(): void
    {
        // Tidak dibalikkan: memulihkan FK ke jadual tiada akan merosakkan sistem.
    }
};
