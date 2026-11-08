<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gen_ai_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 120);
            $table->string('kind', 40)->default('openai');
            $table->string('base_url', 255)->nullable();
            $table->text('api_key')->nullable();
            $table->string('default_model', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('capabilities')->nullable();
            $table->json('config')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('gen_ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('provider_id')->nullable()->constrained('gen_ai_providers')->nullOnDelete();
            $table->string('model', 120)->nullable();
            $table->string('capability', 30)->default('text');
            $table->string('task_type', 60)->nullable();
            $table->string('status', 20)->default('pending');
            $table->longText('prompt');
            $table->longText('system_prompt')->nullable();
            $table->json('parameters')->nullable();
            $table->longText('output_text')->nullable();
            $table->string('output_url', 500)->nullable();
            $table->json('output_meta')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost_rm', 14, 6)->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->string('correlation_id', 40)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'capability']);
            $table->index(['tenant_id', 'created_at']);
            $table->unique(['tenant_id', 'uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gen_ai_jobs');
        Schema::dropIfExists('gen_ai_providers');
    }
};
