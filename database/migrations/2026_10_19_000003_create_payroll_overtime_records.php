<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000003_create_payroll_overtime_records` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `overtime_records`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - overtime_records.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - overtime_records.employee_id → employees.id (induk dalam migration lain)
 *   - overtime_records.approved_by → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "overtime_records" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "employee_id" integer not null, "date" date not null, "day_type" varchar check ("day_type" in (\'regular\', \'rest_day\', \'public_holiday\')) not null default \'regular\', "hours_worked" numeric not null, "hourly_rate" numeric not null, "multiplier" numeric not null, "ot_pay" numeric not null, "status" varchar check ("status" in (\'pending\', \'approved\', \'rejected\')) not null default \'pending\', "approved_by" integer, "approved_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "overtime_records_employee_id_date_index" on "overtime_records" ("employee_id", "date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "overtime_records_tenant_id_date_index" on "overtime_records" ("tenant_id", "date")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
