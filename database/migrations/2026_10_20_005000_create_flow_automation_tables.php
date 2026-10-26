<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workflow Definitions (BAB 28.1)
        if (!Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('trigger_type', [
                    'domain_event', 'webhook', 'schedule', 'manual', 'record_change',
                ])->index();
                $table->json('trigger_config')->nullable();
                $table->json('conditions')->nullable();
                $table->json('steps');
                $table->boolean('is_active')->default(false)->index();
                $table->boolean('requires_approval')->default(false);
                $table->unsignedInteger('version')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->datetime('last_run_at')->nullable();
                $table->unsignedBigInteger('run_count')->default(0);
                $table->unsignedBigInteger('failure_count')->default(0);
                $table->boolean('kill_switch')->default(false)->index();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index(['tenant_id', 'trigger_type']);
            });
        }

        // Workflow Runs
        if (!Schema::hasTable('workflow_runs')) {
            Schema::create('workflow_runs', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('workflow_id')->constrained('workflow_definitions')->onDelete('cascade');
                $table->unsignedInteger('workflow_version')->default(1);
                $table->string('trigger_source', 40);
                $table->json('trigger_payload')->nullable();
                $table->string('idempotency_key', 64)->index();
                $table->enum('status', [
                    'pending', 'running', 'waiting_approval',
                    'completed', 'failed', 'cancelled', 'dead_letter',
                ])->default('pending')->index();
                $table->unsignedInteger('current_step')->default(0);
                $table->unsignedInteger('total_steps')->default(0);
                $table->json('steps_log')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedTinyInteger('retry_count')->default(0);
                $table->datetime('next_retry_at')->nullable()->index();
                $table->datetime('started_at')->nullable();
                $table->datetime('completed_at')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
                $table->index(['workflow_id', 'status']);
                // Idempotency: satu key per tenant
                $table->index(['tenant_id', 'idempotency_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('workflow_definitions');
    }
};
