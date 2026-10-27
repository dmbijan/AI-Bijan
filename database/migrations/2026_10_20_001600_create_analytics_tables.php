<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_20_001600_create_analytics_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `analytics_dashboards`, `analytics_metrics`, `analytics_metric_values`, `analytics_reports`, `analytics_report_runs`, `dashboard_widgets`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - analytics_dashboards.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - analytics_dashboards.created_by → users.id (jadual induk tidak wujud)
 *   - analytics_metrics.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - analytics_metric_values.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - analytics_reports.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - analytics_reports.created_by → users.id (jadual induk tidak wujud)
 *   - analytics_report_runs.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - dashboard_widgets.tenant_id → tenants.id (jadual induk tidak wujud)
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

        DB::unprepared('CREATE TABLE "analytics_dashboards" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar not null, "description" text, "type" varchar not null default \'main\', "layout" text, "widgets" text, "is_active" tinyint(1) not null default \'1\', "is_default" tinyint(1) not null default \'0\', "shared_with" text, "created_by" integer, "metadata" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("created_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "analytics_metrics" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar not null, "category" varchar not null, "type" varchar not null, "description" text, "formula" text, "data_source" varchar, "data_source_table" varchar, "data_source_column" varchar, "aggregation_type" varchar not null default \'sum\', "unit" varchar, "decimal_places" integer not null default \'2\', "target_value" numeric, "threshold_warning" numeric, "threshold_critical" numeric, "is_active" tinyint(1) not null default \'1\', "refresh_interval_minutes" integer not null default \'5\', "last_value" numeric, "previous_value" numeric, "trend_direction" varchar, "trend_percentage" numeric, "last_calculated_at" datetime, "metadata" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "analytics_metric_values" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "metric_id" integer not null, "value" numeric not null, "calculated_at" datetime not null, "metadata" text, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("metric_id") references "analytics_metrics"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "analytics_reports" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "code" varchar not null, "description" text, "type" varchar not null default \'on_demand\', "format" varchar not null default \'pdf\', "metrics" text, "filters" text, "schedule" text, "recipients" text, "is_active" tinyint(1) not null default \'1\', "last_run_at" datetime, "next_run_at" datetime, "created_by" integer, "metadata" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "schedule_cron" varchar, "data_source" varchar, "columns" text, "group_by" text, "sort_by" text, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("created_by") references "users"("id") on delete set null)');
        DB::unprepared('CREATE TABLE "analytics_report_runs" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "report_id" integer not null, "status" varchar not null default \'pending\', "started_at" datetime, "completed_at" datetime, "duration_seconds" integer, "output_file" varchar, "recipients_sent" text, "error_message" text, "metadata" text, "deleted_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("report_id") references "analytics_reports"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "dashboard_widgets" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "dashboard_id" integer not null, "title" varchar not null, "type" varchar not null, "metric_id" integer, "chart_config" text, "filters" text, "position_x" integer not null default \'0\', "position_y" integer not null default \'0\', "width" integer not null default \'6\', "height" integer not null default \'4\', "refresh_interval" integer not null default \'30\', "is_active" tinyint(1) not null default \'1\', "metadata" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "data_source" varchar, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("dashboard_id") references "analytics_dashboards"("id") on delete cascade, foreign key("metric_id") references "analytics_metrics"("id") on delete set null)');

        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_dashboards_tenant_id_is_default_index" on "analytics_dashboards" ("tenant_id", "is_default")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_dashboards_tenant_id_type_index" on "analytics_dashboards" ("tenant_id", "type")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_metric_values_metric_id_calculated_at_index" on "analytics_metric_values" ("metric_id", "calculated_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_metric_values_tenant_id_calculated_at_index" on "analytics_metric_values" ("tenant_id", "calculated_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_metrics_tenant_id_category_index" on "analytics_metrics" ("tenant_id", "category")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_metrics_tenant_id_is_active_index" on "analytics_metrics" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_report_runs_report_id_status_index" on "analytics_report_runs" ("report_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_report_runs_tenant_id_started_at_index" on "analytics_report_runs" ("tenant_id", "started_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_reports_tenant_id_is_active_index" on "analytics_reports" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "analytics_reports_tenant_id_type_index" on "analytics_reports" ("tenant_id", "type")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "dashboard_widgets_dashboard_id_is_active_index" on "dashboard_widgets" ("dashboard_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "dashboard_widgets_dashboard_id_type_index" on "dashboard_widgets" ("dashboard_id", "type")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('analytics_report_runs');
        Schema::dropIfExists('analytics_reports');
        Schema::dropIfExists('analytics_metric_values');
        Schema::dropIfExists('analytics_metrics');
        Schema::dropIfExists('analytics_dashboards');
    }
};
