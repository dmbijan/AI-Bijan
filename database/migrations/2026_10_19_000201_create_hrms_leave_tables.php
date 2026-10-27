<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000201_create_hrms_leave_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `hrms_attendance`, `hrms_holidays`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - hrms_attendance.employee_id → hrms_employees.id (induk dalam migration lain)
 *   - hrms_holidays.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "hrms_attendance" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "date" date not null, "check_in_time" time, "check_out_time" time, "total_hours" numeric, "status" varchar check ("status" in (\'present\', \'absent\', \'late\', \'half_day\', \'on_leave\', \'holiday\')) not null default \'present\', "minutes_late" integer not null default \'0\', "overtime_hours" numeric, "check_in_method" varchar, "check_in_location" varchar, "check_out_location" varchar, "check_in_photo" varchar, "check_out_photo" varchar, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "hrms_holidays" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "date" date not null, "type" varchar check ("type" in (\'public\', \'company\', \'observance\')) not null default \'public\', "state_applicable" varchar, "is_recurring" tinyint(1) not null default \'1\', "description" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_attendance_date_index" on "hrms_attendance" ("date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_holidays_date_index" on "hrms_holidays" ("date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_holidays_tenant_id_index" on "hrms_holidays" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('hrms_holidays');
        Schema::dropIfExists('hrms_attendance');
    }
};
