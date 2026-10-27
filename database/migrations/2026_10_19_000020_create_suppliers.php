<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000020_create_suppliers` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `suppliers`, `supplier_contacts`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - suppliers.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - suppliers.approved_by → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "suppliers" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "supplier_code" varchar not null, "company_name" varchar not null, "company_name_bm" varchar, "ssm_number" varchar, "gst_number" varchar, "sst_number" varchar, "tax_identification" varchar, "contact_person" varchar, "contact_email" varchar, "contact_phone" varchar, "address" text, "city" varchar, "state" varchar, "postcode" varchar, "category" varchar check ("category" in (\'raw_materials\', \'services\', \'equipment\', \'office_supplies\', \'logistics\', \'other\')) not null default \'other\', "status" varchar check ("status" in (\'active\', \'inactive\', \'blacklisted\')) not null default \'active\', "payment_terms_days" integer not null default \'30\', "currency" varchar not null default \'MYR\', "bank_name" varchar, "bank_account_name" varchar, "bank_account_number" varchar, "withholding_tax_rate" numeric not null default \'0\', "rating" numeric not null default \'0\', "on_time_delivery_count" integer not null default \'0\', "late_delivery_count" integer not null default \'0\', "quality_issues_count" integer not null default \'0\', "is_approved" tinyint(1) not null default \'0\', "approved_by" integer, "approved_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "supplier_contacts" ("id" integer primary key autoincrement not null, "supplier_id" integer not null, "name" varchar not null, "position" varchar, "email" varchar, "phone" varchar, "is_primary" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("supplier_id") references "suppliers"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "suppliers_tenant_id_category_index" on "suppliers" ("tenant_id", "category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "suppliers_tenant_id_status_index" on "suppliers" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('suppliers');
    }
};
