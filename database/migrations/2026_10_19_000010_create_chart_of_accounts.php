<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000010_create_chart_of_accounts` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `chart_of_accounts`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - chart_of_accounts.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "chart_of_accounts" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "code" varchar not null, "name" varchar not null, "name_bm" varchar, "type" varchar check ("type" in (\'asset\', \'liability\', \'equity\', \'revenue\', \'expense\', \'cost_of_goods\')) not null, "subtype" varchar check ("subtype" in (\'current_asset\', \'non_current_asset\', \'cash\', \'bank\', \'accounts_receivable\', \'inventory\', \'current_liability\', \'non_current_liability\', \'accounts_payable\', \'equity\', \'retained_earnings\', \'operating_revenue\', \'other_income\', \'cost_direct\', \'cost_indirect\', \'operating_expense\', \'depreciation\', \'tax_expense\')), "is_active" tinyint(1) not null default \'1\', "is_system" tinyint(1) not null default \'0\', "allow_posting" tinyint(1) not null default \'1\', "normal_balance" varchar check ("normal_balance" in (\'debit\', \'credit\')) not null default \'debit\', "parent_id" integer, "is_summary" tinyint(1) not null default \'0\', "description" varchar, "sort_order" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("parent_id") references "chart_of_accounts"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "chart_of_accounts_tenant_id_type_is_active_index" on "chart_of_accounts" ("tenant_id", "type", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "chart_of_accounts_type_index" on "chart_of_accounts" ("type")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
