<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000021_create_purchase_orders` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `goods_received_notes`, `goods_received_note_lines`, `purchase_orders`, `purchase_order_approvals`, `purchase_order_lines`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - goods_received_notes.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - goods_received_notes.received_by → users.id (jadual induk tidak wujud)
 *   - purchase_orders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - purchase_orders.supplier_id → suppliers.id (induk dalam migration lain)
 *   - purchase_orders.requested_by → users.id (jadual induk tidak wujud)
 *   - purchase_orders.approved_by → users.id (jadual induk tidak wujud)
 *   - purchase_orders.journal_entry_id → journal_entries.id (induk dalam migration lain)
 *   - purchase_order_approvals.approver_id → users.id (jadual induk tidak wujud)
 *   - purchase_order_lines.item_id → items.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "goods_received_notes" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "purchase_order_id" integer, "grn_number" varchar not null, "received_date" date not null, "notes" text, "condition" varchar check ("condition" in (\'good\', \'damaged\', \'short_shipped\')) not null default \'good\', "received_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("purchase_order_id") references "purchase_orders"("id") on delete set null, foreign key("received_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "goods_received_note_lines" ("id" integer primary key autoincrement not null, "goods_received_note_id" integer not null, "purchase_order_line_id" integer, "item_code" varchar, "item_name" varchar not null, "ordered_quantity" integer not null default \'0\', "received_quantity" integer not null default \'0\', "rejected_quantity" integer not null default \'0\', "uom" varchar not null default \'pcs\', "created_at" datetime, "updated_at" datetime, foreign key("goods_received_note_id") references "goods_received_notes"("id") on delete cascade, foreign key("purchase_order_line_id") references "purchase_order_lines"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "purchase_orders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "po_number" varchar not null, "supplier_id" integer, "order_date" date not null, "expected_delivery_date" date, "actual_delivery_date" date, "status" varchar check ("status" in (\'draft\', \'pending_approval\', \'approved\', \'sent\', \'partially_received\', \'fully_received\', \'cancelled\')) not null default \'draft\', "subtotal" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "shipping_cost" numeric not null default \'0\', "total_amount" numeric not null default \'0\', "currency" varchar not null default \'MYR\', "payment_terms_days" integer not null default \'30\', "payment_due_date" date, "shipping_method" varchar, "shipping_address" text, "tracking_number" varchar, "internal_notes" text, "terms_conditions" text, "requested_by" integer, "approved_by" integer, "approved_at" datetime, "journal_entry_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("requested_by") references "users"("id") on delete set null, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "purchase_order_approvals" ("id" integer primary key autoincrement not null, "purchase_order_id" integer not null, "approver_id" integer not null, "status" varchar check ("status" in (\'pending\', \'approved\', \'rejected\')) not null default \'pending\', "comments" text, "decided_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("purchase_order_id") references "purchase_orders"("id") on delete cascade, foreign key("approver_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "purchase_order_lines" ("id" integer primary key autoincrement not null, "purchase_order_id" integer not null, "item_id" integer, "item_code" varchar, "item_name" varchar not null, "description" text, "quantity_ordered" integer not null default \'0\', "unit_price" numeric not null default \'0\', "uom" varchar not null default \'pcs\', "quantity_received" numeric not null default \'0\', "line_total" numeric not null default \'0\', "is_taxable" tinyint(1) not null default \'0\', "tax_rate" numeric not null default \'0\', "tax_amount" numeric not null default \'0\', "delivery_status" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("purchase_order_id") references "purchase_orders"("id") on delete cascade, foreign key("item_id") references "items"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "goods_received_notes_received_date_index" on "goods_received_notes" ("received_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "purchase_orders_order_date_index" on "purchase_orders" ("order_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "purchase_orders_status_index" on "purchase_orders" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "purchase_orders_tenant_id_expected_delivery_date_index" on "purchase_orders" ("tenant_id", "expected_delivery_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "purchase_orders_tenant_id_status_index" on "purchase_orders" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "purchase_orders_tenant_id_supplier_id_index" on "purchase_orders" ("tenant_id", "supplier_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_order_approvals');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('goods_received_note_lines');
        Schema::dropIfExists('goods_received_notes');
    }
};
