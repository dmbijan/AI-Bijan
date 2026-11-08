<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('type', 20)->default('gantt');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('duration_days')->default(1);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('depends_on_id')->nullable();
            $table->integer('lag_days')->default(0);
            $table->string('color', 20)->nullable();
            $table->boolean('is_milestone')->default(false);
            $table->boolean('is_baseline')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
            $table->index(['tenant_id', 'project_id']);
        });

        Schema::create('project_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 200);
            $table->timestamp('captured_at')->nullable();
            $table->date('scheduled_start')->nullable();
            $table->date('scheduled_end')->nullable();
            $table->unsignedInteger('planned_duration_days')->default(0);
            $table->decimal('planned_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->integer('variance_days')->default(0);
            $table->decimal('variance_cost', 15, 2)->default(0);
            $table->json('snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'captured_at']);
        });

        Schema::create('project_budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('category', 30)->default('other');
            $table->string('unit', 30)->default('unit');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('planned_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('spent_to_date', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'category']);
        });

        Schema::create('project_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('project_portfolio_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('project_portfolios')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['portfolio_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_portfolio_project');
        Schema::dropIfExists('project_portfolios');
        Schema::dropIfExists('project_budget_lines');
        Schema::dropIfExists('project_baselines');
        Schema::dropIfExists('project_timelines');
    }
};
