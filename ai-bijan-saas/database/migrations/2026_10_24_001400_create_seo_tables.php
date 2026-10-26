<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO & Metadata Engine (BAB 86 / Idea #20).
 *
 * ✅ Didapati daripada audit: Tiada pesaing MYEFL menyediakan SEO untuk
 * semua objek (produk, halaman, artikel, video, senarai). Odoo ada 7 apps
 * SEO; Shopify ada 1,800+ apps SEO — kebanyakannya untuk satu platform sahaja.
 *
 * Ciri:
 * - Profil SEO per tenant (meta default, OG, Twitter card, robots, sitemap)
 * - Audit SEO automatik (meta hilang, title terlalu panjang, image alt hilang…)
 * - Metadata per objek (produk, halaman, artikel, video) dengan preview SERP
 * - Sitemap + robots.txt dinamik (dari PHP, bukan static file)
 * - Suggestion AI: title/description/kwarna optimum untuk enjin carian + AI
 * - Schema.org JSON-LD (Product, Article, LocalBusiness, Breadcrumb…)
 * - Core Web Vitals tracking ringkas
 */
return new class extends Migration
{
    public function up(): void
    {
        // Profil SEO per tenant
        Schema::create('seo_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('site_name')->nullable();
            $t->string('site_url')->nullable();
            $t->string('default_title_template')->default('{page_title} | {site_name}');
            $t->string('default_description_template')->nullable();
            $t->string('default_keyword')->nullable();
            $t->string('og_image_url')->nullable();
            $t->string('twitter_handle')->nullable();
            $t->string('twitter_card_type')->default('summary_large_image');
            $t->string('robots_txt')->nullable();
            $t->string('google_site_verification')->nullable();
            $t->string('bing_site_verification')->nullable();
            $t->string('locale')->default('en-MY');
            $t->string('timezone')->default('Asia/Kuala_Lumpur');
            $t->boolean('auto_sitemap')->default(true);
            $t->boolean('auto_robots')->default(true);
            $t->boolean('is_indexable')->default(true);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique('tenant_id');
        });

        // Metadata objek (produk, halaman, artikel, video…)
        Schema::create('seo_metadata', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('object_type');                      // product|page|article|video|category|blog_post
            $t->unsignedBigInteger('object_id');
            $t->string('slug')->nullable();
            $t->string('title')->nullable();
            $t->string('description')->nullable();
            $t->text('keywords')->nullable();
            $t->string('canonical_url')->nullable();
            $t->string('og_title')->nullable();
            $t->text('og_description')->nullable();
            $t->string('og_image_url')->nullable();
            $t->string('og_type')->default('website');
            $t->string('twitter_title')->nullable();
            $t->text('twitter_description')->nullable();
            $t->string('twitter_image_url')->nullable();
            $t->json('json_ld')->nullable();                // schema.org
            $t->json('hreflang')->nullable();
            $t->boolean('no_index')->default(false);
            $t->boolean('no_follow')->default(false);
            $t->decimal('seo_score', 5, 2)->default(0);
            $t->json('audit_findings')->nullable();
            $t->boolean('is_published')->default(false);
            $t->unsignedInteger('view_count')->default(0);
            $t->timestamps();

            $t->unique(['tenant_id', 'object_type', 'object_id']);
            $t->index(['tenant_id', 'object_type']);
        });

        // Audit SEO — isu yang dikesan
        Schema::create('seo_audit_issues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('seo_metadata_id')->nullable()->index();
            $t->string('object_type')->nullable();
            $t->unsignedBigInteger('object_id')->nullable();
            $t->string('rule');                              // meta_title_missing, title_too_long, ...
            $t->string('severity')->default('warning');       // info|warning|error
            $t->text('message')->nullable();
            $t->text('suggestion')->nullable();
            $t->boolean('is_resolved')->default(false);
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'is_resolved']);
            $t->index(['tenant_id', 'severity']);
        });

        // Cadangan AI untuk SEO
        Schema::create('seo_suggestions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('seo_metadata_id')->nullable()->index();
            $t->string('object_type')->nullable();
            $t->unsignedBigInteger('object_id')->nullable();
            $t->string('field');                             // title|description|keywords|og_title|slug
            $t->text('current_value')->nullable();
            $t->text('suggested_value')->nullable();
            $t->text('reasoning')->nullable();
            $t->decimal('confidence', 5, 2)->default(0);
            $t->string('status')->default('pending');         // pending|accepted|rejected|applied
            $t->timestamp('applied_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        // Sitemap
        Schema::create('seo_sitemaps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('object_type');
            $t->unsignedBigInteger('object_id');
            $t->string('url');
            $t->string('changefreq')->default('weekly');       // always|hourly|daily|weekly|monthly|yearly|never
            $t->decimal('priority', 2, 1)->default(0.50);
            $t->timestamp('lastmod')->nullable();
            $t->boolean('is_indexed')->default(false);
            $t->timestamp('indexed_at')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'object_type', 'object_id']);
        });

        // Core Web Vitals / performance
        Schema::create('seo_page_metrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('url');
            $t->string('object_type')->nullable();
            $t->unsignedBigInteger('object_id')->nullable();
            $t->decimal('lcp_ms', 10, 2)->nullable();          // Largest Contentful Paint
            $t->decimal('fid_ms', 10, 2)->nullable();          // First Input Delay
            $t->decimal('cls', 6, 3)->nullable();              // Cumulative Layout Shift
            $t->decimal('ttfb_ms', 10, 2)->nullable();         // Time to First Byte
            $t->unsignedInteger('sample_size')->default(0);
            $t->timestamp('measured_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'url']);
        });

        // Kata kunci &-competitive keyword
        Schema::create('seo_keywords', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('keyword');
            $t->string('intent')->default('informational');    // informational|commercial|transactional|navigational
            $t->string('source')->default('manual');           // manual|ai|analytics|competitor
            $t->unsignedInteger('search_volume')->nullable();
            $t->unsignedInteger('difficulty')->nullable();
            $t->decimal('current_rank', 5, 1)->nullable();
            $t->string('target_url')->nullable();
            $t->boolean('is_tracked')->default(false);
            $t->json('related_keywords')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
        Schema::dropIfExists('seo_page_metrics');
        Schema::dropIfExists('seo_sitemaps');
        Schema::dropIfExists('seo_suggestions');
        Schema::dropIfExists('seo_audit_issues');
        Schema::dropIfExists('seo_metadata');
        Schema::dropIfExists('seo_profiles');
    }
};
