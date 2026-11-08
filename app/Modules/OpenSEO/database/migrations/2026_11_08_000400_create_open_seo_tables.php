<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('domain', 255)->nullable();
            $table->string('base_url', 255)->nullable();
            $table->string('language', 5)->default('ms');
            $table->string('locale', 10)->default('ms-MY');
            $table->string('country', 3)->default('MY');
            $table->string('device', 20)->default('desktop');
            $table->string('status', 20)->default('active');
            $table->json('target_keywords')->nullable();
            $table->json('competitors')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('serp_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('seo_project_id')->constrained('seo_projects')->cascadeOnDelete();
            $table->string('keyword', 180);
            $table->integer('position')->default(0);
            $table->integer('previous_position')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('title', 300)->nullable();
            $table->string('description', 500)->nullable();
            $table->integer('search_volume')->default(0);
            $table->integer('difficulty')->default(0);
            $table->string('device', 20)->default('desktop');
            $table->string('country', 3)->default('MY');
            $table->json('serp_features')->nullable();
            $table->json('competitors')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['seo_project_id', 'keyword']);
            $table->index(['tenant_id', 'checked_at']);
        });

        Schema::create('seo_keyword_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('seo_project_id')->constrained('seo_projects')->cascadeOnDelete();
            $table->string('keyword', 180);
            $table->string('intent', 30)->default('informational');
            $table->integer('search_volume')->default(0);
            $table->integer('difficulty')->default(0);
            $table->decimal('cpc', 10, 4)->default(0);
            $table->string('competition', 40)->nullable();
            $table->integer('current_position')->default(0);
            $table->integer('best_position')->default(0);
            $table->decimal('volume_trend', 8, 4)->default(0);
            $table->decimal('difficulty_trend', 8, 4)->default(0);
            $table->decimal('priority_score', 8, 2)->default(0);
            $table->boolean('content_gap')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seo_project_id', 'priority_score']);
            $table->unique(['seo_project_id', 'keyword']);
        });

        Schema::create('seo_backlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('seo_project_id')->constrained('seo_projects')->cascadeOnDelete();
            $table->string('source_url', 500);
            $table->string('source_domain', 255)->nullable();
            $table->string('source_title', 300)->nullable();
            $table->string('target_url', 500)->nullable();
            $table->string('anchor_text', 200)->nullable();
            $table->integer('domain_authority')->default(0);
            $table->integer('page_authority')->default(0);
            $table->string('link_type', 20)->default('text');
            $table->boolean('is_dofollow')->default(true);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_lost')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['seo_project_id', 'is_lost']);
            $table->index(['tenant_id', 'source_domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_backlinks');
        Schema::dropIfExists('seo_keyword_research');
        Schema::dropIfExists('serp_snapshots');
        Schema::dropIfExists('seo_projects');
    }
};
