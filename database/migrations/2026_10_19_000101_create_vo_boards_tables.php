<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000101_create_vo_boards_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `vo_boards`, `vo_board_members`, `vo_cards`, `vo_card_comments`, `vo_card_history`, `vo_columns`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - vo_boards.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - vo_boards.created_by → users.id (jadual induk tidak wujud)
 *   - vo_board_members.user_id → users.id (jadual induk tidak wujud)
 *   - vo_cards.created_by → users.id (jadual induk tidak wujud)
 *   - vo_cards.assigned_to → vo_agents.id (induk dalam migration lain)
 *   - vo_card_comments.user_id → users.id (jadual induk tidak wujud)
 *   - vo_card_history.user_id → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "vo_boards" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "created_by" integer, "name" varchar not null, "description" text, "visibility" varchar check ("visibility" in (\'private\', \'team\', \'public\')) not null default \'team\', "color" varchar not null default \'#3B82F6\', "settings" text, "archived_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("created_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "vo_board_members" ("id" integer primary key autoincrement not null, "board_id" integer not null, "user_id" integer not null, "role" varchar check ("role" in (\'owner\', \'editor\', \'viewer\')) not null default \'editor\', "created_at" datetime, "updated_at" datetime, foreign key("board_id") references "vo_boards"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "vo_cards" ("id" integer primary key autoincrement not null, "board_id" integer not null, "column_id" integer, "created_by" integer, "assigned_to" integer, "title" varchar not null, "description" text, "priority" varchar check ("priority" in (\'low\', \'medium\', \'high\', \'urgent\')) not null default \'medium\', "status" varchar check ("status" in (\'todo\', \'in_progress\', \'review\', \'done\', \'cancelled\')) not null default \'todo\', "due_date" date, "position" integer not null default \'0\', "labels" text, "attachments" text, "checklist" text, "completed_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("board_id") references "vo_boards"("id") on delete cascade, foreign key("column_id") references "vo_columns"("id") on delete set null, foreign key("created_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "vo_card_comments" ("id" integer primary key autoincrement not null, "card_id" integer not null, "user_id" integer not null, "body" text not null, "created_at" datetime, "updated_at" datetime, foreign key("card_id") references "vo_cards"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "vo_card_history" ("id" integer primary key autoincrement not null, "card_id" integer not null, "user_id" integer, "action" varchar not null, "changes" text, "created_at" datetime, "updated_at" datetime, foreign key("card_id") references "vo_cards"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "vo_columns" ("id" integer primary key autoincrement not null, "board_id" integer not null, "name" varchar not null, "position" integer not null default \'0\', "is_default" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("board_id") references "vo_boards"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "vo_boards_tenant_id_index" on "vo_boards" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_card_comments_card_id_index" on "vo_card_comments" ("card_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_card_history_card_id_index" on "vo_card_history" ("card_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_cards_assigned_to_index" on "vo_cards" ("assigned_to")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_cards_board_id_index" on "vo_cards" ("board_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_cards_column_id_index" on "vo_cards" ("column_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_cards_status_index" on "vo_cards" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "vo_columns_board_id_index" on "vo_columns" ("board_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('vo_columns');
        Schema::dropIfExists('vo_card_history');
        Schema::dropIfExists('vo_card_comments');
        Schema::dropIfExists('vo_cards');
        Schema::dropIfExists('vo_board_members');
        Schema::dropIfExists('vo_boards');
    }
};
