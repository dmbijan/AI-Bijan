<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000051_create_sales_orders_table` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `quotations`, `quotation_lines`, `sales_orders`, `sales_order_items`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - quotations.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - quotations.opportunity_id → opportunities.id (induk dalam migration lain)
 *   - quotations.customer_id → customers.id (induk dalam migration lain)
 *   - quotations.approved_by → users.id (jadual induk tidak wujud)
 *   - quotation_lines.item_id → items.id (jadual induk tidak wujud)
 *   - sales_orders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - sales_orders.customer_id → customers.id (induk dalam migration lain)
 *   - sales_order_items.product_id → items.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "quotations" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "quotation_number" varchar not null, "opportunity_id" integer, "customer_id" integer, "quotation_date" date not null, "valid_until" date not null, "status" varchar check ("status" in (\'draft\', \'sent\', \'accepted\', \'rejected\', \'expired\')) not null default \'draft\', "subtotal" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "discount_percentage" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "shipping_cost" numeric not null default \'0\', "total_amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "payment_terms_days" integer not null default \'30\', "payment_due_date" date, "shipping_address" text, "shipping_method" varchar, "terms_conditions" text, "notes" text, "approved_by" integer, "approved_at" datetime, "order_id" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "customer_name" varchar, "customer_email" varchar, "customer_phone" varchar, "issue_date" date, "expiry_date" date, "validity_days" integer, "tax_total" numeric not null default \'0\', "discount_total" numeric not null default \'0\', "shipping_total" numeric not null default \'0\', "grand_total" numeric not null default \'0\', "exchange_rate" numeric not null default \'0\', "accepted_at" datetime, "rejected_at" datetime, "converted_to_order_id" integer, "converted_at" datetime, "created_by" integer, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "quotation_lines" ("id" integer primary key autoincrement not null, "quotation_id" integer not null, "item_id" integer, "item_code" varchar, "item_name" varchar not null, "description" text, "quantity" integer not null default \'0\', "unit_price" numeric not null default \'0\', "discount_percentage" numeric not null default \'0\', "line_total" numeric not null default \'0\', "uom" varchar not null default \'pcs\', "created_at" datetime, "updated_at" datetime, foreign key("quotation_id") references "quotations"("id") on delete cascade, foreign key("item_id") references "items"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "sales_orders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "order_number" varchar not null, "quotation_id" integer, "customer_id" integer, "customer_name" varchar, "customer_email" varchar, "customer_phone" varchar, "billing_address" text, "shipping_address" text, "order_date" date, "expected_delivery_date" date, "actual_delivery_date" date, "subtotal" numeric not null default \'0\', "tax_total" numeric not null default \'0\', "discount_total" numeric not null default \'0\', "shipping_total" numeric not null default \'0\', "grand_total" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "exchange_rate" numeric not null default \'1\', "notes" text, "terms_conditions" text, "status" varchar not null default \'pending\', "fulfilled_at" datetime, "cancelled_at" datetime, "created_by" integer, "approved_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("quotation_id") references "quotations"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "sales_order_items" ("id" integer primary key autoincrement not null, "sales_order_id" integer not null, "product_id" integer, "product_name" varchar, "product_sku" varchar, "description" text, "quantity" numeric not null default \'1\', "unit_price" numeric not null default \'0\', "discount_percentage" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "tax_percentage" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "line_total" numeric not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("sales_order_id") references "sales_orders"("id") on delete cascade, foreign key("product_id") references "items"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "quotations_quotation_date_index" on "quotations" ("quotation_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "quotations_tenant_id_customer_id_index" on "quotations" ("tenant_id", "customer_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "quotations_tenant_id_status_index" on "quotations" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "quotations_valid_until_index" on "quotations" ("valid_until")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sales_order_items_sales_order_id_index" on "sales_order_items" ("sales_order_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sales_orders_status_index" on "sales_orders" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sales_orders_tenant_id_created_at_index" on "sales_orders" ("tenant_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sales_orders_tenant_id_index" on "sales_orders" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('quotation_lines');
        Schema::dropIfExists('quotations');
    }
};
