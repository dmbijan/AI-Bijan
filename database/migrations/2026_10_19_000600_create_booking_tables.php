<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000600_create_booking_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `booking_reminders`, `booking_service_providers`, `booking_services`, `booking_slots`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - booking_reminders.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - booking_reminders.appointment_id → bookings_appointments.id (jadual induk tidak wujud)
 *   - booking_service_providers.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - booking_service_providers.user_id → users.id (jadual induk tidak wujud)
 *   - booking_services.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - booking_slots.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "booking_reminders" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "appointment_id" integer not null, "channel" varchar check ("channel" in (\'sms\', \'whatsapp\', \'email\', \'push\')) not null default \'whatsapp\', "status" varchar check ("status" in (\'pending\', \'sent\', \'failed\')) not null default \'pending\', "scheduled_at" datetime not null, "sent_at" datetime, "template" varchar, "payload" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "booking_service_providers" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "user_id" integer, "name" varchar not null, "slug" varchar not null, "specialization" varchar, "bio" text, "phone" varchar, "email" varchar, "avatar_url" varchar, "is_active" tinyint(1) not null default \'1\', "max_concurrent_appointments" integer not null default \'1\', "break_time_minutes" integer not null default \'15\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "booking_services" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "slug" varchar not null, "category" varchar, "description" text, "duration_minutes" integer not null default \'30\', "price" numeric not null default \'0\', "cost" numeric not null default \'0\', "provider_id" integer, "is_active" tinyint(1) not null default \'1\', "image_url" varchar, "max_capacity" integer not null default \'1\', "requires_prep" tinyint(1) not null default \'0\', "prep_time_minutes" integer not null default \'0\', "cancellation_policy" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("provider_id") references "booking_service_providers"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "booking_slots" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "service_id" integer, "provider_id" integer, "slot_date" date not null, "start_time" time not null, "end_time" time not null, "status" varchar check ("status" in (\'available\', \'booked\', \'blocked\', \'maintenance\')) not null default \'available\', "capacity" integer not null default \'1\', "booked_count" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("service_id") references "booking_services"("id") on delete set null, foreign key("provider_id") references "booking_service_providers"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "booking_reminders_appointment_id_index" on "booking_reminders" ("appointment_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_reminders_status_scheduled_at_index" on "booking_reminders" ("status", "scheduled_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_service_providers_tenant_id_index" on "booking_service_providers" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_service_providers_tenant_id_is_active_index" on "booking_service_providers" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_services_tenant_id_category_index" on "booking_services" ("tenant_id", "category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_services_tenant_id_is_active_index" on "booking_services" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_slots_provider_id_slot_date_index" on "booking_slots" ("provider_id", "slot_date")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "booking_slots_tenant_id_slot_date_index" on "booking_slots" ("tenant_id", "slot_date")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('booking_service_providers');
        Schema::dropIfExists('booking_reminders');
    }
};
