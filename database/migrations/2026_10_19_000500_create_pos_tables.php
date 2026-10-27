<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000500_create_pos_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `pos_dine_areas`, `pos_dine_orders`, `pos_dine_order_items`, `pos_dine_tables`, `pos_discount_approvals`, `pos_modifier_groups`, `pos_modifier_options`, `pos_product_modifiers`, `pos_receipt_templates`, `pos_registers`, `pos_sales`, `pos_sale_discounts`, `pos_sale_items`, `pos_sale_payments`, `pos_sale_returns`, `pos_sale_tenders`, `pos_shift_cash_movements`, `pos_tng_notifications`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - pos_dine_areas.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_dine_orders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_dine_order_items.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_dine_tables.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_discount_approvals.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_modifier_groups.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_modifier_options.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_product_modifiers.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_product_modifiers.item_id → items.id (jadual induk tidak wujud)
 *   - pos_receipt_templates.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_registers.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_registers.created_by → users.id (jadual induk tidak wujud)
 *   - pos_sale_discounts.approved_by → users.id (jadual induk tidak wujud)
 *   - pos_sale_items.product_id → items.id (jadual induk tidak wujud)
 *   - pos_sale_returns.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_sale_returns.returned_by → users.id (jadual induk tidak wujud)
 *   - pos_sale_returns.approved_by → users.id (jadual induk tidak wujud)
 *   - pos_sale_tenders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_shift_cash_movements.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - pos_shift_cash_movements.cash_shift_id → cash_shifts.id (jadual induk tidak wujud)
 *   - pos_tng_notifications.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "pos_dine_areas" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_dine_orders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "dine_table_id" integer not null, "sale_id" integer, "customer_id" integer, "cashier_id" integer, "order_no" varchar not null, "status" varchar not null default \'submitted\', "payment_option" varchar not null default \'pay_at_counter\', "access_token_hash" varchar, "access_expires_at" datetime, "subtotal" numeric not null default \'0\', "item_count" integer not null default \'0\', "notes" text, "rejection_reason" text, "submitted_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("dine_table_id") references "pos_dine_tables"("id") on delete cascade, foreign key("sale_id") references "pos_sales"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_dine_order_items" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "dine_order_id" integer not null, "item_id" integer, "item_name" varchar not null, "unit_price" numeric not null, "quantity" integer not null, "modifiers" text, "modifier_total" numeric not null default \'0\', "line_total" numeric not null, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("dine_order_id") references "pos_dine_orders"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_dine_tables" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "dine_area_id" integer not null, "name" varchar not null, "code" varchar, "seats" integer not null default \'4\', "status" varchar not null default \'free\', "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("dine_area_id") references "pos_dine_areas"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_discount_approvals" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "sale_id" integer, "requested_by" integer, "approved_by" integer, "discount_percent" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "status" varchar not null default \'pending\', "reason" text, "decided_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("sale_id") references "pos_sales"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_modifier_groups" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "min_select" integer not null default \'0\', "max_select" integer not null default \'1\', "sort_order" integer not null default \'0\', "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_modifier_options" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "modifier_group_id" integer not null, "name" varchar not null, "price" numeric not null default \'0\', "sort_order" integer not null default \'0\', "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("modifier_group_id") references "pos_modifier_groups"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_product_modifiers" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "item_id" integer not null, "modifier_group_id" integer not null, "sort_order" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("item_id") references "items"("id") on delete cascade, foreign key("modifier_group_id") references "pos_modifier_groups"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_receipt_templates" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "header" text, "footer" text, "print_logo" tinyint(1) not null default \'0\', "logo_path" varchar, "is_default" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_registers" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar not null, "description" text, "location" varchar, "is_active" tinyint(1) not null default \'1\', "created_by" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("created_by") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_sales" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "register_id" integer not null, "cash_shift_id" integer, "customer_id" integer, "user_id" integer not null, "sale_no" varchar not null, "status" varchar not null default (\'draft\'), "subtotal" numeric not null, "tax_amount" numeric not null default (\'0\'), "discount_amount" numeric not null default (\'0\'), "total_amount" numeric not null, "amount_paid" numeric not null default (\'0\'), "change_amount" numeric not null default (\'0\'), "notes" text, "sale_date" date not null default (\'2026-10-26 19:57:29\'), "completed_at" datetime, "created_at" datetime, "updated_at" datetime, "service_charge_amount" numeric not null default (\'0\'), "service_charge_rate" numeric, "payment_method" varchar, "payment_status" varchar not null default (\'unpaid\'), "client_uuid" varchar, "sync_fingerprint" varchar, "discount_approved_by" integer, "discount_approved_at" datetime, "order_type" varchar not null default \'takeaway\', "kitchen_status" varchar, "kitchen_queued_at" datetime, "kitchen_started_at" datetime, "kitchen_ready_at" datetime, "kitchen_served_at" datetime, "dine_table_id" integer, foreign key("user_id") references users("id") on delete cascade on update no action, foreign key("customer_id") references customers("id") on delete set null on update no action, foreign key("cash_shift_id") references cash_shifts("id") on delete set null on update no action, foreign key("register_id") references pos_registers("id") on delete cascade on update no action, foreign key("tenant_id") references tenants("id") on delete cascade on update no action, foreign key("dine_table_id") references "pos_dine_tables"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_sale_discounts" ("id" integer primary key autoincrement not null, "sale_id" integer not null, "type" varchar check ("type" in (\'percentage\', \'fixed\', \'loyalty\', \'voucher\', \'manual\')) not null, "code" varchar, "value" numeric not null, "amount" numeric not null, "approved_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("sale_id") references "pos_sales"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_sale_items" ("id" integer primary key autoincrement not null, "sale_id" integer not null, "product_id" integer, "product_name" varchar not null, "product_sku" varchar, "quantity" integer not null, "unit_price" numeric not null, "discount_percentage" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "line_total" numeric not null, "created_at" datetime, "updated_at" datetime, "modifiers" text, "modifier_total" numeric not null default \'0\', foreign key("sale_id") references "pos_sales"("id") on delete cascade, foreign key("product_id") references "items"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_sale_payments" ("id" integer primary key autoincrement not null, "sale_id" integer not null, "payment_method" varchar check ("payment_method" in (\'cash\', \'card\', \'ewallet\', \'credit\', \'voucher\', \'split\')) not null, "amount" numeric not null, "reference_number" varchar, "payment_details" text, "created_at" datetime, "updated_at" datetime, foreign key("sale_id") references "pos_sales"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_sale_returns" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "sale_id" integer not null, "returned_by" integer not null, "approved_by" integer, "return_no" varchar not null, "status" varchar check ("status" in (\'pending\', \'approved\', \'rejected\', \'completed\')) not null default \'pending\', "return_amount" numeric not null, "reason" text not null, "approved_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("sale_id") references "pos_sales"("id") on delete cascade, foreign key("returned_by") references "users"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "pos_sale_tenders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "sale_id" integer not null, "method" varchar not null, "amount" numeric not null, "cash_received" numeric, "change_amount" numeric not null default \'0\', "payment_status" varchar not null default \'paid\', "reference" varchar, "paid_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("sale_id") references "pos_sales"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_shift_cash_movements" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "cash_shift_id" integer not null, "type" varchar not null, "amount" numeric not null, "reason" varchar, "created_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "pos_tng_notifications" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "amount" numeric not null, "reference" varchar, "raw_message" varchar, "status" varchar not null default \'unclaimed\', "sale_id" integer, "matched_by" integer, "received_at" datetime, "matched_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "pos_dine_areas_tenant_id_is_active_index" on "pos_dine_areas" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_dine_order_items_tenant_id_dine_order_id_index" on "pos_dine_order_items" ("tenant_id", "dine_order_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_dine_orders_tenant_id_dine_table_id_index" on "pos_dine_orders" ("tenant_id", "dine_table_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_dine_orders_tenant_id_status_index" on "pos_dine_orders" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_dine_tables_tenant_id_dine_area_id_index" on "pos_dine_tables" ("tenant_id", "dine_area_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_discount_approvals_tenant_id_status_index" on "pos_discount_approvals" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_modifier_groups_tenant_id_is_active_index" on "pos_modifier_groups" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_modifier_options_tenant_id_modifier_group_id_index" on "pos_modifier_options" ("tenant_id", "modifier_group_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_product_modifiers_tenant_id_index" on "pos_product_modifiers" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_receipt_templates_tenant_id_index" on "pos_receipt_templates" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_registers_is_active_index" on "pos_registers" ("is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_registers_tenant_id_index" on "pos_registers" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_discounts_sale_id_index" on "pos_sale_discounts" ("sale_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_items_sale_id_index" on "pos_sale_items" ("sale_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_payments_sale_id_index" on "pos_sale_payments" ("sale_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_returns_sale_id_index" on "pos_sale_returns" ("sale_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_returns_status_index" on "pos_sale_returns" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_returns_tenant_id_index" on "pos_sale_returns" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sale_tenders_tenant_id_sale_id_index" on "pos_sale_tenders" ("tenant_id", "sale_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_cash_shift_id_index" on "pos_sales" ("cash_shift_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_register_id_index" on "pos_sales" ("register_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_sale_date_index" on "pos_sales" ("sale_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_status_index" on "pos_sales" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_tenant_id_client_uuid_index" on "pos_sales" ("tenant_id", "client_uuid")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_tenant_id_index" on "pos_sales" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_tenant_id_kitchen_status_index" on "pos_sales" ("tenant_id", "kitchen_status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_tenant_id_payment_status_index" on "pos_sales" ("tenant_id", "payment_status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_sales_tenant_id_sync_fingerprint_index" on "pos_sales" ("tenant_id", "sync_fingerprint")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_shift_cash_movements_tenant_id_cash_shift_id_index" on "pos_shift_cash_movements" ("tenant_id", "cash_shift_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "pos_tng_notifications_tenant_id_status_index" on "pos_tng_notifications" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_tng_notifications');
        Schema::dropIfExists('pos_shift_cash_movements');
        Schema::dropIfExists('pos_sale_tenders');
        Schema::dropIfExists('pos_sale_returns');
        Schema::dropIfExists('pos_sale_payments');
        Schema::dropIfExists('pos_sale_items');
        Schema::dropIfExists('pos_sale_discounts');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('pos_registers');
        Schema::dropIfExists('pos_receipt_templates');
        Schema::dropIfExists('pos_product_modifiers');
        Schema::dropIfExists('pos_modifier_options');
        Schema::dropIfExists('pos_modifier_groups');
        Schema::dropIfExists('pos_discount_approvals');
        Schema::dropIfExists('pos_dine_tables');
        Schema::dropIfExists('pos_dine_order_items');
        Schema::dropIfExists('pos_dine_orders');
        Schema::dropIfExists('pos_dine_areas');
    }
};
