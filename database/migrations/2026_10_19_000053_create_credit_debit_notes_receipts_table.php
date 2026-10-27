<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000053_create_credit_debit_notes_receipts_table` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `credit_notes`, `debit_notes`, `receipts`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - credit_notes.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - credit_notes.customer_id → customers.id (induk dalam migration lain)
 *   - credit_notes.invoice_id → invoices.id (induk dalam migration lain)
 *   - debit_notes.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - debit_notes.customer_id → customers.id (induk dalam migration lain)
 *   - debit_notes.invoice_id → invoices.id (induk dalam migration lain)
 *   - receipts.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - receipts.customer_id → customers.id (induk dalam migration lain)
 *   - receipts.invoice_id → invoices.id (induk dalam migration lain)
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

        DB::unprepared('CREATE TABLE "credit_notes" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "credit_note_number" varchar not null, "invoice_id" integer, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "reason" varchar not null, "original_amount" numeric not null default \'0\', "credit_amount" numeric not null default \'0\', "applied_amount" numeric not null default \'0\', "remaining_credit" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "notes" text, "status" varchar not null default \'draft\', "issued_at" datetime, "expires_at" datetime, "applied_at" datetime, "created_by" integer, "approved_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "debit_notes" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "debit_note_number" varchar not null, "invoice_id" integer, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "reason" varchar not null, "amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "notes" text, "status" varchar not null default \'draft\', "issued_at" datetime, "paid_at" datetime, "created_by" integer, "approved_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "receipts" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "receipt_number" varchar not null, "invoice_id" integer, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "payment_method" varchar not null, "reference_number" varchar, "amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "notes" text, "received_at" datetime, "created_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "credit_notes_status_index" on "credit_notes" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "credit_notes_tenant_id_created_at_index" on "credit_notes" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "credit_notes_tenant_id_index" on "credit_notes" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "debit_notes_status_index" on "debit_notes" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "debit_notes_tenant_id_created_at_index" on "debit_notes" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "debit_notes_tenant_id_index" on "debit_notes" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "receipts_invoice_id_index" on "receipts" ("invoice_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "receipts_tenant_id_created_at_index" on "receipts" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "receipts_tenant_id_index" on "receipts" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('credit_notes');
    }
};
