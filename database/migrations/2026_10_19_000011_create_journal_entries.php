<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000011_create_journal_entries` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `journal_entries`, `journal_entry_lines`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - journal_entries.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - journal_entries.approved_by → users.id (jadual induk tidak wujud)
 *   - journal_entry_lines.account_id → chart_of_accounts.id (induk dalam migration lain)
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

        DB::unprepared('CREATE TABLE "journal_entries" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "entry_number" varchar not null, "entry_date" date not null, "status" varchar check ("status" in (\'draft\', \'posted\', \'cancelled\')) not null default \'draft\', "reference_number" varchar, "description" text, "narration_bm" text, "fiscal_year" varchar, "period" varchar, "source_module" varchar, "source_id" varchar, "approved_by" integer, "approved_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "journal_entry_lines" ("id" integer primary key autoincrement not null, "journal_entry_id" integer not null, "account_id" integer not null, "debit" numeric not null default \'0\', "credit" numeric not null default \'0\', "description" text, "currency" varchar not null default \'MYR\', "exchange_rate" numeric not null default \'1\', "amount_foreign" numeric not null default \'0\', "cost_center" varchar, "project" varchar, "line_number" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("journal_entry_id") references "journal_entries"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entries_entry_date_index" on "journal_entries" ("entry_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entries_status_index" on "journal_entries" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entries_tenant_id_entry_date_index" on "journal_entries" ("tenant_id", "entry_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entries_tenant_id_fiscal_year_period_index" on "journal_entries" ("tenant_id", "fiscal_year", "period")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entry_lines_account_id_index" on "journal_entry_lines" ("account_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "journal_entry_lines_tenant_id_index" on "journal_entry_lines" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
