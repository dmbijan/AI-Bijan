<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000004_create_payroll_runs` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `payroll_runs`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - payroll_runs.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - payroll_runs.created_by → users.id (jadual induk tidak wujud)
 *   - payroll_runs.approved_by → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "payroll_runs" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "period_start" date not null, "period_end" date not null, "pay_date" date, "status" varchar check ("status" in (\'draft\', \'pending_approval\', \'approved\', \'processed\', \'cancelled\')) not null default \'draft\', "created_by" integer, "approved_by" integer, "approved_at" datetime, "total_gross" numeric not null default \'0\', "total_epf_ee" numeric not null default \'0\', "total_socso_ee" numeric not null default \'0\', "total_eis_ee" numeric not null default \'0\', "total_pcb" numeric not null default \'0\', "total_other_deductions" numeric not null default \'0\', "total_net_pay" numeric not null default \'0\', "total_epf_er" numeric not null default \'0\', "total_socso_er" numeric not null default \'0\', "total_eis_er" numeric not null default \'0\', "total_hrdf" numeric not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("created_by") references "users"("id") on delete set null, foreign key("approved_by") references "users"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "payroll_runs_tenant_id_status_index" on "payroll_runs" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
