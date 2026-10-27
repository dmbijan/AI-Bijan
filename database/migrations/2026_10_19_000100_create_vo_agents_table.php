<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000100_create_vo_agents_table` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `vo_agents`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - vo_agents.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - vo_agents.user_id → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "vo_agents" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "user_id" integer, "name" varchar not null, "email" varchar, "type" varchar check ("type" in (\'human\', \'ai\', \'hybrid\')) not null default \'human\', "avatar_url" varchar, "role" varchar, "status" varchar check ("status" in (\'idle\', \'active\', \'busy\', \'offline\')) not null default \'idle\', "metadata" text, "last_active_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "vo_agents_tenant_id_status_index" on "vo_agents" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('vo_agents');
    }
};
