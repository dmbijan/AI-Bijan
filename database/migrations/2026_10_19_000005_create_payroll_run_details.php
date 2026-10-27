<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000005_create_payroll_run_details` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `payroll_run_details`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - payroll_run_details.payroll_run_id → payroll_runs.id (induk dalam migration lain)
 *   - payroll_run_details.employee_id → employees.id (induk dalam migration lain)
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

        DB::unprepared('CREATE TABLE "payroll_run_details" ("id" integer primary key autoincrement not null, "payroll_run_id" integer not null, "employee_id" integer not null, "basic_salary" numeric not null, "fixed_allowance" numeric not null default \'0\', "overtime_pay" numeric not null default \'0\', "other_earnings" numeric not null default \'0\', "gross_pay" numeric not null, "epf_ee" numeric not null default \'0\', "socso_ee" numeric not null default \'0\', "eis_ee" numeric not null default \'0\', "pcb" numeric not null default \'0\', "other_deductions" numeric not null default \'0\', "net_pay" numeric not null, "epf_er" numeric not null default \'0\', "socso_er" numeric not null default \'0\', "eis_er" numeric not null default \'0\', "hrdf" numeric not null default \'0\', "statutory_year" varchar, "calculation_notes" text, "created_at" datetime, "updated_at" datetime, foreign key("payroll_run_id") references "payroll_runs"("id") on delete cascade, foreign key("employee_id") references "employees"("id") on delete cascade)');

    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_details');
    }
};
