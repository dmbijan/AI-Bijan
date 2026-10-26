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
        // Sites table
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('primary_domain')->nullable();
            $table->json('secondary_domains')->nullable();
            $table->string('theme')->default('default');
            $table->string('status')->default('draft');
            $table->boolean('is_live')->default(false);
            $table->boolean('maintenance_mode')->default(false);
            $table->boolean('coming_soon_mode')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_tag_manager_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->longText('custom_head_code')->nullable();
            $table->longText('custom_body_code')->nullable();
            $table->string('language')->default('en');
            $table->string('timezone')->default('UTC');
            $table->timestamps();

            $table->index(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
        });

        // Site Pages table
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('template')->default('default');
            $table->string('status')->default('draft');
            $table->longText('content')->nullable();
            $table->json('sections')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'published_at']);
        });

        // Site Themes table
        Schema::create('site_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // Site Sections table
        Schema::create('site_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('type');
            $table->string('name')->nullable();
            $table->json('settings')->nullable();
            $table->json('content')->nullable();
            $table->json('styles')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['page_id', 'type']);
        });

        // Site Posts table
        Schema::create('site_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'published_at']);
        });

        // Site Post Comments table
        Schema::create('site_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('site_posts')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('site_post_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->longText('content');
            $table->string('status')->default('pending');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['post_id', 'is_approved']);
        });

        // Site Forms table
        Schema::create('site_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('fields');
            $table->string('submit_action')->default('email');
            $table->text('success_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('spam_protection')->nullable();
            $table->json('notification_email')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Site Form Submissions table
        Schema::create('site_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('site_forms')->cascadeOnDelete();
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_spam')->default(false);
            $table->timestamps();

            $table->index(['form_id', 'created_at']);
        });

        // Site Domains table
        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('type')->default('primary');
            $table->boolean('is_primary')->default(false);
            $table->boolean('ssl_enabled')->default(false);
            $table->string('ssl_status')->default('none');
            $table->string('redirect_to')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->json('dns_records')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'site_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('site_form_submissions');
        Schema::dropIfExists('site_forms');
        Schema::dropIfExists('site_post_comments');
        Schema::dropIfExists('site_posts');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('site_themes');
        Schema::dropIfExists('site_pages');
        Schema::dropIfExists('sites');
    }
};
