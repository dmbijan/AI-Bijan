<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal AI Tasks (BAB 17.5)
        if (!Schema::hasTable('ai_tasks')) {
            Schema::create('ai_tasks', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('agent_id')->nullable();
                $table->string('assistant_type')->default('owner')->index();
                $table->text('objective');
                $table->string('input_reference')->nullable();
                $table->json('input_data')->nullable();
                $table->json('allowed_tools')->nullable();
                $table->enum('risk_level', ['low', 'moderate', 'high', 'critical'])->default('low')->index();
                $table->boolean('approval_required')->default(false);
                $table->decimal('budget_limit', 15, 2)->nullable();
                $table->datetime('deadline')->nullable();
                $table->string('status')->default('Draft')->index();
                $table->string('result_reference')->nullable();
                $table->json('result_data')->nullable();
                $table->text('error_details')->nullable();
                $table->unsignedBigInteger('approval_id')->nullable();
                $table->datetime('completed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'user_id', 'status']);
                $table->index(['tenant_id', 'user_id', 'created_at']);
            });
        }

        // Personal AI Memory Items (BAB 17A.3)
        if (!Schema::hasTable('ai_memory_items')) {
            Schema::create('ai_memory_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->enum('memory_type', ['preference', 'context', 'insight', 'knowledge', 'history'])->index();
                $table->string('category', 100)->index();
                $table->text('content_text');
                $table->json('content_json')->nullable();
                $table->string('embedding_id')->nullable()->index();
                $table->string('source', 100)->default('user_input');
                $table->decimal('confidence_score', 3, 2)->default(1.00);
                $table->boolean('is_verified')->default(false)->index();
                $table->datetime('expires_at')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'user_id', 'memory_type']);
                $table->index(['tenant_id', 'is_verified']);
            });
        }

        // User AI Preferences
        if (!Schema::hasTable('ai_user_preferences')) {
            Schema::create('ai_user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('language', 10)->default('ms');
                $table->string('tone', 50)->default('formal');
                $table->string('report_format', 50)->default('standard');
                $table->string('preferred_model', 100)->nullable();
                $table->string('default_assistant', 50)->default('owner');
                $table->string('timezone', 50)->default('Asia/Kuala_Lumpur');
                $table->json('notification_channels')->nullable();
                $table->time('quiet_hours_start')->nullable();
                $table->time('quiet_hours_end')->nullable();
                $table->text('custom_instructions')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_user_preferences');
        Schema::dropIfExists('ai_memory_items');
        Schema::dropIfExists('ai_tasks');
    }
};
