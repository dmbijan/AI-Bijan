<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_19_000200_create_hrms_core_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `hrms_departments`, `hrms_designations`, `hrms_employees`, `hrms_employee_documents`, `hrms_employee_shifts`, `hrms_leave_allocations`, `hrms_leave_requests`, `hrms_leave_request_history`, `hrms_leave_types`, `hrms_overtime_requests`, `hrms_shifts`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - hrms_departments.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - hrms_departments.manager_id → users.id (jadual induk tidak wujud)
 *   - hrms_designations.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - hrms_employees.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - hrms_employees.user_id → users.id (jadual induk tidak wujud)
 *   - hrms_leave_requests.approved_by → users.id (jadual induk tidak wujud)
 *   - hrms_leave_request_history.user_id → users.id (jadual induk tidak wujud)
 *   - hrms_leave_types.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - hrms_overtime_requests.approved_by → users.id (jadual induk tidak wujud)
 *   - hrms_shifts.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "hrms_departments" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar, "parent_id" integer, "manager_id" integer, "description" text, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("parent_id") references "hrms_departments"("id") on delete set null, foreign key("manager_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_designations" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar, "department_id" integer, "level" integer not null default \'1\', "description" text, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("department_id") references "hrms_departments"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_employees" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "user_id" integer, "employee_no" varchar not null, "ic_number" varchar, "passport_number" varchar, "first_name" varchar not null, "last_name" varchar, "email" varchar not null, "phone" varchar, "date_of_birth" date, "gender" varchar check ("gender" in (\'male\', \'female\')), "marital_status" varchar check ("marital_status" in (\'single\', \'married\', \'divorced\', \'widowed\')), "nationality" varchar not null default \'Malaysian\', "address_line1" varchar, "address_line2" varchar, "city" varchar, "state" varchar, "postcode" varchar, "country" varchar not null default \'Malaysia\', "department_id" integer, "designation_id" integer, "employment_type" varchar check ("employment_type" in (\'full_time\', \'part_time\', \'contract\', \'freelance\', \'intern\')) not null default \'full_time\', "date_joined" date, "contract_end_date" date, "probation_end_date" date, "status" varchar check ("status" in (\'active\', \'on_leave\', \'suspended\', \'terminated\', \'resigned\')) not null default \'active\', "last_worked_date" date, "termination_reason" text, "emergency_contact_name" varchar, "emergency_contact_phone" varchar, "emergency_contact_relation" varchar, "bank_name" varchar, "bank_account_no" varchar, "epf_no" varchar, "soeso_no" varchar, "eis_no" varchar, "pcb_ref" varchar, "metadata" text, "last_active_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete set null, foreign key("department_id") references "hrms_departments"("id") on delete set null, foreign key("designation_id") references "hrms_designations"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_employee_documents" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "document_type" varchar not null, "file_path" varchar not null, "file_name" varchar not null, "mime_type" varchar not null, "file_size" integer not null, "expiry_date" date, "status" varchar check ("status" in (\'pending\', \'verified\', \'rejected\')) not null default \'pending\', "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "hrms_employee_shifts" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "shift_id" integer not null, "effective_from" date not null, "effective_to" date, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade, foreign key("shift_id") references "hrms_shifts"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "hrms_leave_allocations" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "leave_type_id" integer not null, "total_days" integer not null, "used_days" integer not null default \'0\', "carried_over_days" integer not null default \'0\', "year" integer not null, "expiry_date" date, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade, foreign key("leave_type_id") references "hrms_leave_types"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "hrms_leave_requests" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "leave_type_id" integer not null, "start_date" date not null, "end_date" date not null, "days_requested" integer not null, "half_day" varchar check ("half_day" in (\'full\', \'morning\', \'afternoon\')) not null default \'full\', "reason" text, "attachment_path" varchar, "attachment_name" varchar, "status" varchar check ("status" in (\'draft\', \'pending\', \'approved\', \'rejected\', \'cancelled\')) not null default \'draft\', "approved_by" integer, "approved_at" datetime, "rejection_reason" text, "metadata" text, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade, foreign key("leave_type_id") references "hrms_leave_types"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_leave_request_history" ("id" integer primary key autoincrement not null, "leave_request_id" integer not null, "user_id" integer, "action" varchar not null, "changes" text, "notes" text, "created_at" datetime, "updated_at" datetime, foreign key("leave_request_id") references "hrms_leave_requests"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_leave_types" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "code" varchar not null, "name" varchar not null, "max_days_per_year" integer, "encashable" tinyint(1) not null default \'0\', "requires_document" tinyint(1) not null default \'0\', "description" text, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "hrms_overtime_requests" ("id" integer primary key autoincrement not null, "employee_id" integer not null, "date" date not null, "start_time" time not null, "end_time" time not null, "hours_requested" numeric not null, "reason" text, "status" varchar check ("status" in (\'draft\', \'pending\', \'approved\', \'rejected\', \'cancelled\')) not null default \'draft\', "approved_by" integer, "approved_at" datetime, "rejection_reason" text, "created_at" datetime, "updated_at" datetime, foreign key("employee_id") references "hrms_employees"("id") on delete cascade, foreign key("approved_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "hrms_shifts" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar not null, "description" text, "start_time" time not null, "end_time" time not null, "break_start" time, "break_end" time, "working_hours" integer not null default \'8\', "working_days" text not null default \'[1,2,3,4,5]\', "type" varchar check ("type" in (\'standard\', \'rotating\', \'flexible\', \'night\')) not null default \'standard\', "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_departments_tenant_id_index" on "hrms_departments" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_designations_tenant_id_index" on "hrms_designations" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_employee_documents_employee_id_index" on "hrms_employee_documents" ("employee_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_employees_department_id_index" on "hrms_employees" ("department_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_employees_status_index" on "hrms_employees" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_employees_tenant_id_index" on "hrms_employees" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_leave_request_history_leave_request_id_index" on "hrms_leave_request_history" ("leave_request_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_leave_requests_employee_id_index" on "hrms_leave_requests" ("employee_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_leave_requests_status_index" on "hrms_leave_requests" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_leave_types_tenant_id_index" on "hrms_leave_types" ("tenant_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_overtime_requests_employee_id_index" on "hrms_overtime_requests" ("employee_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_overtime_requests_status_index" on "hrms_overtime_requests" ("status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "hrms_shifts_tenant_id_index" on "hrms_shifts" ("tenant_id")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('hrms_shifts');
        Schema::dropIfExists('hrms_overtime_requests');
        Schema::dropIfExists('hrms_leave_types');
        Schema::dropIfExists('hrms_leave_request_history');
        Schema::dropIfExists('hrms_leave_requests');
        Schema::dropIfExists('hrms_leave_allocations');
        Schema::dropIfExists('hrms_employee_shifts');
        Schema::dropIfExists('hrms_employee_documents');
        Schema::dropIfExists('hrms_employees');
        Schema::dropIfExists('hrms_designations');
        Schema::dropIfExists('hrms_departments');
    }
};
