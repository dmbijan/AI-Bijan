<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000102_create_vo_schedules_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `vo_schedules`, `vo_schedule_attendees`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - vo_schedules.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - vo_schedules.agent_id → vo_agents.id (induk dalam migration lain)
 *   - vo_schedule_attendees.user_id → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "vo_schedules" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "agent_id" integer, "title" varchar not null, "description" text, "start_time" datetime not null, "end_time" datetime not null, "type" varchar check ("type" in (\'meeting\', \'standup\', \'review\', \'planning\', \'social\', \'other\')) not null default \'meeting\', "is_recurring" tinyint(1) not null default \'0\', "recurrence_pattern" text, "location" varchar, "meeting_link" varchar, "status" varchar check ("status" in (\'scheduled\', \'in_progress\', \'completed\', \'cancelled\', \'no_show\')) not null default \'scheduled\', "attendees" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "vo_schedule_attendees" ("id" integer primary key autoincrement not null, "schedule_id" integer not null, "user_id" integer not null, "response" varchar check ("response" in (\'pending\', \'accepted\', \'declined\', \'tentative\')) not null default \'pending\', "responded_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("schedule_id") references "vo_schedules"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "vo_schedules_agent_id_index" on "vo_schedules" ("agent_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_schedules_start_time_end_time_index" on "vo_schedules" ("start_time", "end_time")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_schedules_tenant_id_index" on "vo_schedules" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('vo_schedule_attendees');
        Schema::dropIfExists('vo_schedules');
    }
};
