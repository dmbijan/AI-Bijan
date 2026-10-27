<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000054_create_recurring_billings_table` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `recurring_billings`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - recurring_billings.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - recurring_billings.customer_id → customers.id (induk dalam migration lain)
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

        DB::unprepared('CREATE TABLE "recurring_billings" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "billing_type" varchar not null default \'subscription\', "frequency" varchar not null default \'monthly\', "interval" integer not null default \'1\', "start_date" date, "end_date" date, "next_billing_date" date, "amount" numeric not null default \'0\', "tax_percentage" numeric not null default \'0\', "grand_total" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "auto_send_invoice" tinyint(1) not null default \'0\', "auto_send_reminder" tinyint(1) not null default \'0\', "reminder_days_before" integer not null default \'7\', "status" varchar not null default \'active\', "total_invoices_generated" integer not null default \'0\', "last_invoice_date" date, "last_billing_date" date, "notes" text, "created_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "recurring_billings_next_billing_date_index" on "recurring_billings" ("next_billing_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "recurring_billings_status_index" on "recurring_billings" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "recurring_billings_tenant_id_created_at_index" on "recurring_billings" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "recurring_billings_tenant_id_index" on "recurring_billings" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_billings');
    }
};
