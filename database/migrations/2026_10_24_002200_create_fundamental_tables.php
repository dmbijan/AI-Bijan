<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Fundamental P1 — Platform Health & Observability (BAB 90).
 *
 * 6 jadual:
 *   1. health_checks       — definisi check + sejarah status
 *   2. observability_logs — log terstruktur dengan correlation ID
 *   3. metric_snapshots    — nilai metrik berkala (Prometheus-style)
 *   4. alert_rules         — peraturan amaran + sejarah
 *   5. backup_snapshots    — inventori backup
 *   6. restore_drills      — rekod uji pemulihan (RPO/RTO)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('check_key', 80)->index();
            $table->string('name', 150);
            $table->string('component', 60)->index();
            $table->string('status', 30)->default('unknown');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['component', 'status']);
            $table->index(['tenant_id', 'check_key', 'checked_at'], 'health_tenant_key_time_idx');
        });

        Schema::create('observability_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('level', 20)->default('info')->index();
            $table->string('channel', 40)->default('app')->index();
            $table->string('event_name', 120)->index();
            $table->string('correlation_id', 64)->index();
            $table->string('request_id', 64)->nullable()->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('service', 60)->nullable();
            $table->string('host', 80)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'level', 'created_at'], 'obs_tenant_level_time_idx');
            $table->index(['correlation_id', 'created_at']);
        });

        Schema::create('metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('metric_key', 100)->index();
            $table->string('name', 150);
            $table->string('type', 20)->default('gauge');
            $table->string('labels', 200)->nullable();
            $table->double('value')->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'metric_key', 'recorded_at'], 'metrics_tenant_key_time_idx');
        });

        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('rule_key', 80)->index();
            $table->string('name', 150);
            $table->string('metric_key', 100)->index();
            $table->string('operator', 20)->default('gt');
            $table->double('threshold')->default(0);
            $table->unsignedInteger('window_minutes')->default(5);
            $table->string('severity', 20)->default('warning');
            $table->string('channel', 40)->default('in_app');
            $table->text('message_template')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('trigger_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_enabled']);
        });

        Schema::create('backup_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_id', 80)->index();
            $table->string('disk', 40)->default('local');
            $table->string('filename', 255);
            $table->string('type', 30)->default('full');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->string('status', 30)->default('pending');
            $table->string('error_message', 500)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('restore_drills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('backup_snapshot_id')->nullable()->index();
            $table->string('drill_type', 40)->default('full_restore');
            $table->string('status', 30)->default('planned');
            $table->unsignedInteger('rpo_actual_minutes')->nullable();
            $table->unsignedInteger('rto_actual_minutes')->nullable();
            $table->unsignedInteger('rpo_target_minutes')->nullable();
            $table->unsignedInteger('rto_target_minutes')->nullable();
            $table->boolean('passed')->nullable();
            $table->text('findings')->nullable();
            $table->unsignedBigInteger('performed_by_user_id')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restore_drills');
        Schema::dropIfExists('backup_snapshots');
        Schema::dropIfExists('alert_rules');
        Schema::dropIfExists('metric_snapshots');
        Schema::dropIfExists('observability_logs');
        Schema::dropIfExists('health_checks');
    }
};
