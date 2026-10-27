<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000052_create_invoices_table` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `invoices`, `invoice_items`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - invoices.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - invoices.customer_id → customers.id (induk dalam migration lain)
 *   - invoices.sales_order_id → sales_orders.id (induk dalam migration lain)
 *   - invoices.quotation_id → quotations.id (induk dalam migration lain)
 *   - invoice_items.product_id → items.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "invoices" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "invoice_number" varchar not null, "sales_order_id" integer, "quotation_id" integer, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "customer_tax_id" varchar, "billing_address" text, "issue_date" date, "due_date" date, "subtotal" numeric not null default \'0\', "tax_total" numeric not null default \'0\', "discount_total" numeric not null default \'0\', "shipping_total" numeric not null default \'0\', "grand_total" numeric not null default \'0\', "amount_paid" numeric not null default \'0\', "amount_outstanding" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "exchange_rate" numeric not null default \'1\', "notes" text, "terms_conditions" text, "status" varchar not null default \'draft\', "paid_at" datetime, "overdue_notified_at" datetime, "einvois_submission_status" varchar not null default \'pending\', "einvois_reference_no" varchar, "einvois_submission_date" datetime, "einvois_rejection_reason" text, "einvois_cancelled_at" datetime, "created_by" integer, "approved_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "invoice_items" ("id" integer primary key autoincrement not null, "invoice_id" integer not null, "product_id" integer, "product_name" varchar, "product_sku" varchar, "description" text, "quantity" numeric not null default \'1\', "unit_price" numeric not null default \'0\', "discount_percentage" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "tax_percentage" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "line_total" numeric not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("invoice_id") references "invoices"("id") on delete cascade, foreign key("product_id") references "items"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "invoice_items_invoice_id_index" on "invoice_items" ("invoice_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "invoices_due_date_index" on "invoices" ("due_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "invoices_einvois_submission_status_index" on "invoices" ("einvois_submission_status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "invoices_status_index" on "invoices" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "invoices_tenant_id_created_at_index" on "invoices" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "invoices_tenant_id_index" on "invoices" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
