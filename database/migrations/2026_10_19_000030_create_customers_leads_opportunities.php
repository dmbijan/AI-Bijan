<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000030_create_customers_leads_opportunities` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `customers`, `customer_contacts`, `opportunities`, `leads`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - customers.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - customers.salesperson_id → users.id (jadual induk tidak wujud)
 *   - opportunities.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - opportunities.owner_id → users.id (jadual induk tidak wujud)
 *   - leads.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - leads.assigned_to → users.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "customers" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "customer_code" varchar not null, "customer_type" varchar check ("customer_type" in (\'individual\', \'business\')) not null default \'business\', "name" varchar not null, "name_bm" varchar, "ssm_number" varchar, "sst_number" varchar, "tax_identification" varchar, "contact_person" varchar, "contact_email" varchar, "contact_phone" varchar, "address" text, "city" varchar, "state" varchar, "postcode" varchar, "category" varchar check ("category" in (\'retail\', \'wholesale\', \'distributor\', \'enterprise\', \'government\')) not null default \'retail\', "status" varchar check ("status" in (\'active\', \'inactive\', \'blocked\')) not null default \'active\', "credit_limit" numeric not null default \'0\', "payment_terms_days" integer not null default \'30\', "currency" varchar not null default \'MYR\', "salesperson_id" integer, "region" varchar, "bank_name" varchar, "bank_account_name" varchar, "bank_account_number" varchar, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("salesperson_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "customer_contacts" ("id" integer primary key autoincrement not null, "customer_id" integer not null, "name" varchar not null, "position" varchar, "email" varchar, "phone" varchar, "is_primary" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("customer_id") references "customers"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "opportunities" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "opportunity_number" varchar not null, "customer_id" integer, "title" varchar not null, "description" text, "estimated_value" numeric not null default \'0\', "probability" numeric not null default \'0\', "stage" varchar check ("stage" in (\'prospecting\', \'qualification\', \'needs_analysis\', \'proposal\', \'negotiation\', \'closed_won\', \'closed_lost\')) not null default \'prospecting\', "expected_close_date" date, "actual_close_date" date, "forecast_category" varchar check ("forecast_category" in (\'best_case\', \'commit\', \'opportunity\', \'pipeline\')) not null default \'pipeline\', "competitors" varchar, "win_strategy" text, "loss_reason" text, "next_action" text, "next_action_date" date, "owner_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("customer_id") references "customers"("id") on delete set null, foreign key("owner_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "leads" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "lead_number" varchar not null, "source" varchar not null, "name" varchar not null, "email" varchar, "phone" varchar, "company" varchar, "position" varchar, "requirements" text, "estimated_value" numeric not null default \'0\', "expected_close_date" date, "status" varchar check ("status" in (\'new\', \'contacted\', \'qualified\', \'proposal\', \'negotiation\', \'won\', \'lost\')) not null default \'new\', "priority" varchar check ("priority" in (\'low\', \'medium\', \'high\', \'urgent\')) not null default \'medium\', "assigned_to" integer, "opportunity_id" integer, "converted_at" datetime, "loss_reason" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("assigned_to") references "users"("id") on delete set null, foreign key("opportunity_id") references "opportunities"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "customers_tenant_id_category_index" on "customers" ("tenant_id", "category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "customers_tenant_id_status_index" on "customers" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "leads_tenant_id_assigned_to_index" on "leads" ("tenant_id", "assigned_to")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "leads_tenant_id_status_index" on "leads" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "opportunities_tenant_id_owner_id_index" on "opportunities" ("tenant_id", "owner_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "opportunities_tenant_id_stage_index" on "opportunities" ("tenant_id", "stage")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customers');
    }
};
