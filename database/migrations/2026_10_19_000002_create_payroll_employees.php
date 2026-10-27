<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000002_create_payroll_employees` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `employees`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - employees.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "employees" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "ic_no" varchar not null, "epf_no" varchar, "socso_no" varchar, "eis_no" varchar, "tax_no" varchar, "position" varchar not null, "department" varchar, "start_date" date, "end_date" date, "employment_type" varchar check ("employment_type" in (\'permanent\', \'contract\', \'part_time\', \'casual\')) not null default \'permanent\', "basic_salary" numeric not null default \'0\', "fixed_allowance" numeric not null default \'0\', "salary_currency" varchar check ("salary_currency" in (\'MYR\', \'SGD\', \'USD\')) not null default \'MYR\', "bank_name" varchar, "bank_account_no" varchar, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "employees_tenant_id_is_active_index" on "employees" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
