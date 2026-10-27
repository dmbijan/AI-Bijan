<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000012_create_accounts_payable_receivable` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `accounts_payable`, `accounts_receivable`, `payments`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - accounts_payable.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - accounts_payable.supplier_id → suppliers.id (induk dalam migration lain)
 *   - accounts_payable.journal_entry_id → journal_entries.id (induk dalam migration lain)
 *   - accounts_receivable.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - accounts_receivable.customer_id → customers.id (induk dalam migration lain)
 *   - accounts_receivable.journal_entry_id → journal_entries.id (induk dalam migration lain)
 *   - payments.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - payments.journal_entry_id → journal_entries.id (induk dalam migration lain)
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

        DB::unprepared('CREATE TABLE "accounts_payable" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "supplier_id" integer, "invoice_number" varchar not null, "invoice_date" date not null, "due_date" date not null, "original_amount" numeric not null default \'0\', "outstanding_amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "paid_amount" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "withholding_tax" numeric not null default \'0\', "status" varchar check ("status" in (\'pending\', \'partial_paid\', \'fully_paid\', \'overdue\', \'disputed\', \'cancelled\')) not null default \'pending\', "journal_entry_id" integer, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "accounts_receivable" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "customer_id" integer, "invoice_number" varchar not null, "invoice_date" date not null, "due_date" date not null, "original_amount" numeric not null default \'0\', "outstanding_amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "received_amount" numeric not null default \'0\', "discount_given" numeric not null default \'0\', "status" varchar check ("status" in (\'pending\', \'partial_paid\', \'fully_paid\', \'overdue\', \'bad_debt\', \'cancelled\')) not null default \'pending\', "days_overdue" integer not null default \'0\', "aging_bucket" varchar check ("aging_bucket" in (\'current\', \'1_30\', \'31_60\', \'61_90\', \'91_180\', \'181_plus\')) not null default \'current\', "journal_entry_id" integer, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "payments" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "payment_type" varchar check ("payment_type" in (\'payable\', \'receivable\')) not null, "payable_type" varchar not null, "payable_id" integer not null, "payment_reference" varchar not null, "payment_date" date not null, "amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "method" varchar check ("method" in (\'cheque\', \'wire_transfer\', \'ewallet\', \'cash\', \'credit_card\', \'other\')) not null default \'wire_transfer\', "reference_number" varchar, "bank_name" varchar, "bank_account" varchar, "journal_entry_id" integer, "is_reconciled" tinyint(1) not null default \'0\', "reconciled_at" date, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_payable_due_date_index" on "accounts_payable" ("due_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_payable_invoice_date_index" on "accounts_payable" ("invoice_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_payable_tenant_id_due_date_index" on "accounts_payable" ("tenant_id", "due_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_payable_tenant_id_status_index" on "accounts_payable" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_payable_tenant_id_supplier_id_index" on "accounts_payable" ("tenant_id", "supplier_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_receivable_due_date_index" on "accounts_receivable" ("due_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_receivable_invoice_date_index" on "accounts_receivable" ("invoice_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_receivable_tenant_id_customer_id_index" on "accounts_receivable" ("tenant_id", "customer_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_receivable_tenant_id_due_date_index" on "accounts_receivable" ("tenant_id", "due_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "accounts_receivable_tenant_id_status_index" on "accounts_receivable" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "payments_payable_type_payable_id_index" on "payments" ("payable_type", "payable_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "payments_payment_date_index" on "payments" ("payment_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "payments_payment_type_index" on "payments" ("payment_type")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "payments_tenant_id_method_index" on "payments" ("tenant_id", "method")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "payments_tenant_id_payment_date_index" on "payments" ("tenant_id", "payment_date")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('accounts_payable');
    }
};
