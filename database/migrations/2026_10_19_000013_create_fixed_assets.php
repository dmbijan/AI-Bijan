<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000013_create_fixed_assets` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `fixed_assets`, `depreciation_schedule`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - fixed_assets.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - fixed_assets.acquisition_journal_id → journal_entries.id (induk dalam migration lain)
 *   - fixed_assets.depreciation_journal_id → journal_entries.id (induk dalam migration lain)
 *   - depreciation_schedule.journal_entry_id → journal_entries.id (induk dalam migration lain)
 *
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

        DB::unprepared('CREATE TABLE "fixed_assets" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "asset_number" varchar not null, "name" varchar not null, "description" text, "category" varchar check ("category" in (\'land\', \'building\', \'machinery\', \'equipment\', \'vehicles\', \'furniture_fittings\', \'computer_equipment\', \'software\', \'leasehold_improvements\', \'other\')) not null default \'other\', "purchase_date" date not null, "cost" numeric not null default \'0\', "installation_cost" numeric not null default \'0\', "total_cost" numeric not null default \'0\', "depreciation_method" varchar check ("depreciation_method" in (\'straight_line\', \'reducing_balance\', \'units_of_production\')) not null default \'straight_line\', "annual_depreciation_rate" numeric not null default \'0\', "useful_life_years" integer not null default \'5\', "accumulated_depreciation" numeric not null default \'0\', "net_book_value" numeric not null default \'0\', "residual_value" numeric not null default \'0\', "status" varchar check ("status" in (\'active\', \'fully_depreciated\', \'disposed\', \'maintenance\', \'sold\')) not null default \'active\', "location" varchar, "department" varchar, "custodian" varchar, "is_qualifying_asset" tinyint(1) not null default \'0\', "initial_allowance_amount" numeric not null default \'0\', "tax_accumulated_depreciation" numeric not null default \'0\', "disposal_date" date, "disposal_proceeds" numeric not null default \'0\', "disposal_gain_loss" numeric not null default \'0\', "acquisition_journal_id" integer, "depreciation_journal_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "depreciation_schedule" ("id" integer primary key autoincrement not null, "asset_id" integer not null, "year" integer not null, "period_start" date not null, "period_end" date not null, "opening_nbv" numeric not null, "depreciation_amount" numeric not null, "closing_nbv" numeric not null, "cumulative_depreciation" numeric not null, "is_posted" tinyint(1) not null default \'0\', "journal_entry_id" integer, "created_at" datetime, "updated_at" datetime, "tenant_id" integer, "period" varchar, "opening_value" numeric, "closing_value" numeric, "calculated_at" datetime, foreign key("asset_id") references "fixed_assets"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "depreciation_schedule_period_end_index" on "depreciation_schedule" ("period_end")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "depreciation_schedule_period_start_index" on "depreciation_schedule" ("period_start")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "fixed_assets_purchase_date_index" on "fixed_assets" ("purchase_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "fixed_assets_tenant_id_category_index" on "fixed_assets" ("tenant_id", "category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "fixed_assets_tenant_id_status_index" on "fixed_assets" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedule');
        Schema::dropIfExists('fixed_assets');
    }
};
