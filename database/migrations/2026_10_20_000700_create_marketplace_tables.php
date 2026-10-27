<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_20_000700_create_marketplace_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `marketplace_carts`, `marketplace_orders`, `marketplace_products`, `marketplace_order_items`, `marketplace_reviews`, `marketplace_vendors`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - marketplace_carts.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_carts.user_id → users.id (jadual induk tidak wujud)
 *   - marketplace_orders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_orders.customer_id → users.id (jadual induk tidak wujud)
 *   - marketplace_products.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_order_items.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_reviews.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_reviews.user_id → users.id (jadual induk tidak wujud)
 *   - marketplace_vendors.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - marketplace_vendors.user_id → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "marketplace_carts" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "user_id" integer, "session_id" varchar, "vendor_id" integer, "product_id" integer, "quantity" integer not null, "unit_price" numeric not null, "subtotal" numeric not null, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade, foreign key("vendor_id") references "marketplace_vendors"("id") on delete set null, foreign key("product_id") references "marketplace_products"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "marketplace_orders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "order_no" varchar not null, "vendor_id" integer, "customer_id" integer, "status" varchar not null default \'pending\', "subtotal" numeric not null, "tax_amount" numeric not null default \'0\', "shipping_cost" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "total_amount" numeric not null, "currency" varchar not null default \'MYR\', "payment_method" varchar, "payment_status" varchar not null default \'unpaid\', "shipping_method" varchar, "shipping_address" text, "billing_address" text, "notes" text, "internal_notes" text, "tracking_number" varchar, "shipped_at" datetime, "delivered_at" datetime, "cancelled_at" datetime, "cancellation_reason" varchar, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("vendor_id") references "marketplace_vendors"("id") on delete set null, foreign key("customer_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "marketplace_products" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "vendor_id" integer not null, "sku" varchar not null, "name" varchar not null, "slug" varchar not null, "description" text, "short_description" text, "category" varchar not null, "subcategory" varchar, "price" numeric not null, "compare_at_price" numeric, "cost" numeric, "stock_quantity" integer not null default \'0\', "low_stock_threshold" integer not null default \'5\', "is_active" tinyint(1) not null default \'1\', "is_featured" tinyint(1) not null default \'0\', "is_digital" tinyint(1) not null default \'0\', "weight" numeric, "length" numeric, "width" numeric, "height" numeric, "images" text, "specifications" text, "tags" text, "seo_title" varchar, "seo_description" text, "views_count" integer not null default \'0\', "sales_count" integer not null default \'0\', "rating" numeric not null default \'0\', "review_count" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("vendor_id") references "marketplace_vendors"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "marketplace_order_items" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "order_id" integer not null, "product_id" integer, "vendor_id" integer, "product_name" varchar not null, "product_sku" varchar, "quantity" integer not null, "unit_price" numeric not null, "subtotal" numeric not null, "tax_amount" numeric not null default \'0\', "discount_amount" numeric not null default \'0\', "total" numeric not null, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("order_id") references "marketplace_orders"("id") on delete cascade, foreign key("product_id") references "marketplace_products"("id") on delete set null, foreign key("vendor_id") references "marketplace_vendors"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "marketplace_reviews" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "product_id" integer not null, "user_id" integer not null, "order_id" integer, "rating" integer not null, "title" varchar, "comment" text, "images" text, "is_verified_purchase" tinyint(1) not null default \'0\', "is_approved" tinyint(1) not null default \'1\', "helpful_count" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("product_id") references "marketplace_products"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade, foreign key("order_id") references "marketplace_orders"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "marketplace_vendors" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "user_id" integer, "name" varchar not null, "slug" varchar not null, "description" text, "logo_url" varchar, "banner_url" varchar, "contact_email" varchar not null, "contact_phone" varchar, "address" text, "city" varchar, "state" varchar, "postal_code" varchar, "country" varchar, "is_active" tinyint(1) not null default \'1\', "is_verified" tinyint(1) not null default \'0\', "commission_rate" numeric not null default \'0.05\', "rating" numeric not null default \'0\', "total_sales" integer not null default \'0\', "total_products" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_carts_session_id_index" on "marketplace_carts" ("session_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_carts_session_id_product_id_index" on "marketplace_carts" ("session_id", "product_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_carts_tenant_id_index" on "marketplace_carts" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_carts_user_id_product_id_index" on "marketplace_carts" ("user_id", "product_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_order_items_order_id_index" on "marketplace_order_items" ("order_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_order_items_product_id_index" on "marketplace_order_items" ("product_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_order_items_tenant_id_index" on "marketplace_order_items" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_customer_id_index" on "marketplace_orders" ("customer_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_order_no_index" on "marketplace_orders" ("order_no")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_payment_status_index" on "marketplace_orders" ("payment_status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_status_index" on "marketplace_orders" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_tenant_id_index" on "marketplace_orders" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_orders_vendor_id_index" on "marketplace_orders" ("vendor_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_products_category_index" on "marketplace_products" ("category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_products_is_active_is_featured_index" on "marketplace_products" ("is_active", "is_featured")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_products_slug_index" on "marketplace_products" ("slug")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_products_tenant_id_index" on "marketplace_products" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_products_vendor_id_index" on "marketplace_products" ("vendor_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_reviews_is_approved_created_at_index" on "marketplace_reviews" ("is_approved", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_reviews_product_id_index" on "marketplace_reviews" ("product_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_reviews_tenant_id_index" on "marketplace_reviews" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_reviews_user_id_index" on "marketplace_reviews" ("user_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_vendors_is_active_is_verified_index" on "marketplace_vendors" ("is_active", "is_verified")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_vendors_slug_index" on "marketplace_vendors" ("slug")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "marketplace_vendors_tenant_id_index" on "marketplace_vendors" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_vendors');
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('marketplace_order_items');
        Schema::dropIfExists('marketplace_products');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_carts');
    }
};
