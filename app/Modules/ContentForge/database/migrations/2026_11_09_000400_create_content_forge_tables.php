<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ContentForge — penjanaan kandungan & SEO (asal: SEO Writing AI).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sites', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('url', 255);
            $t->string('platform', 40)->default('wordpress');
            $t->string('username', 120);
            $t->text('app_password');
            $t->string('default_category', 120)->nullable();
            $t->unsignedBigInteger('default_author_id')->nullable();
            $t->boolean('auto_publish')->default(false);
            $t->string('status', 20)->default('disconnected');
            $t->timestamp('last_published_at')->nullable();
            $t->text('last_error')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('content_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->uuid('uuid');
            $t->string('title', 250);
            $t->string('slug', 280);
            $t->string('type', 30)->default('article');
            $t->string('status', 20)->default('draft');
            $t->string('language', 5)->default('ms');
            $t->text('brief')->nullable();
            $t->json('keywords')->nullable();
            $t->unsignedInteger('target_word_count')->default(1500);
            $t->unsignedInteger('actual_word_count')->default(0);
            $t->longText('body_html')->nullable();
            $t->longText('body_markdown')->nullable();
            $t->json('outline')->nullable();
            $t->string('meta_title', 250)->nullable();
            $t->string('meta_description', 300)->nullable();
            $t->json('serp_analysis')->nullable();
            $t->unsignedTinyInteger('seo_score')->default(0);
            $t->json('seo_issues')->nullable();
            $t->string('generated_by', 60)->nullable();
            $t->string('model_id', 120)->nullable();
            $t->unsignedInteger('tokens_used')->default(0);
            $t->unsignedBigInteger('cost_sen')->default(0);
            $t->string('published_url', 500)->nullable();
            $t->timestamp('published_at')->nullable();
            $t->foreignId('wordpress_site_id')->nullable()->constrained('content_sites')->nullOnDelete();
            $t->timestamp('scheduled_publish_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'type']);
            $t->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_documents');
        Schema::dropIfExists('content_sites');
    }
};
