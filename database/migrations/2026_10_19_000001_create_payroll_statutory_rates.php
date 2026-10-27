<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000001_create_payroll_statutory_rates` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `statutory_rates`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - statutory_rates.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "statutory_rates" ("id" integer primary key autoincrement not null, "tenant_id" integer, "year" varchar not null, "type" varchar, "epf_employee_rate" numeric not null default \'11\', "epf_employer_base_rate" numeric not null default \'13\', "epf_employer_reduced_rate" numeric not null default \'12\', "epf_salary_threshold" integer not null default \'5000\', "socso_max_annual_ee" numeric not null default \'0\', "socso_max_annual_er" numeric not null default \'0\', "eis_rate" numeric not null default \'0.2\', "eis_max_annual" numeric not null default \'100\', "hrdf_rate" numeric not null default \'1\', "pcb_use_mptg_table" tinyint(1) not null default \'1\', "effective_from" date, "effective_to" date, "is_active" tinyint(1) not null default \'0\', "source_reference" varchar, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "statutory_rates_type_index" on "statutory_rates" ("type")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "statutory_rates_year_index" on "statutory_rates" ("year")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('statutory_rates');
    }
};
