<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Analytics Metrics
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category'); // finance, sales, operations, hr, marketing, customer, inventory, custom
            $table->string('type'); // kpi, metric, ratio, rate, score
            $table->text('description')->nullable();
            $table->json('formula')->nullable();
            $table->string('data_source')->nullable();
            $table->string('data_source_table')->nullable();
            $table->string('data_source_column')->nullable();
            $table->string('aggregation_type')->default('sum'); // sum, avg, count, min, max, ratio, cumulative
            $table->string('unit')->nullable();
            $table->unsignedSmallInteger('decimal_places')->default(2);
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('threshold_warning', 15, 2)->nullable();
            $table->decimal('threshold_critical', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('refresh_interval_minutes')->default(5);
            $table->decimal('last_value', 15, 2)->nullable();
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->string('trend_direction')->nullable(); // up, down, stable
            $table->decimal('trend_percentage', 8, 2)->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Analytics Metric Values (History)
        Schema::create('analytics_metric_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('metric_id')->constrained('analytics_metrics')->cascadeOnDelete();
            $table->decimal('value', 15, 2);
            $table->timestamp('calculated_at');
            $table->json('metadata')->nullable();

            $table->index(['metric_id', 'calculated_at']);
            $table->index(['tenant_id', 'calculated_at']);
        });

        // Analytics Reports
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('on_demand'); // scheduled, on_demand, automated
            $table->string('format')->default('pdf'); // pdf, excel, csv, json
            $table->json('metrics')->nullable();
            $table->json('filters')->nullable();
            $table->json('schedule')->nullable(); // {interval: daily, time: 09:00}
            $table->json('recipients')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Report Runs
        Schema::create('analytics_report_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('report_id')->constrained('analytics_reports')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, running, completed, failed, cancelled
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('output_file')->nullable();
            $table->json('recipients_sent')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();

            $table->index(['report_id', 'status']);
            $table->index(['tenant_id', 'started_at']);
        });

        // Dashboards
        Schema::create('analytics_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('main'); // main, executive, operational, custom
            $table->json('layout')->nullable(); // grid configuration
            $table->json('widgets')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('shared_with')->nullable(); // array of user IDs
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_default']);
        });

        // Dashboard Widgets
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('dashboard_id')->constrained('analytics_dashboards')->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // kpi_card, line_chart, bar_chart, pie_chart, table, gauge, trend, metric
            $table->foreignId('metric_id')->nullable()->constrained('analytics_metrics')->nullOnDelete();
            $table->json('chart_config')->nullable();
            $table->json('filters')->nullable();
            $table->unsignedInteger('position_x')->default(0);
            $table->unsignedInteger('position_y')->default(0);
            $table->unsignedInteger('width')->default(6); // out of 12 columns
            $table->unsignedInteger('height')->default(4);
            $table->unsignedInteger('refresh_interval')->default(30); // seconds
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dashboard_id', 'type']);
            $table->index(['dashboard_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('analytics_dashboards');
        Schema::dropIfExists('analytics_report_runs');
        Schema::dropIfExists('analytics_reports');
        Schema::dropIfExists('analytics_metric_values');
        Schema::dropIfExists('analytics_metrics');
    }
};
