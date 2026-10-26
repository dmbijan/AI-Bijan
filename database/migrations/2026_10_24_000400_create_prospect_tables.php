<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prospecting Engine (BAB 82 / Fasa 1) — sumber, senarai, dan prospek.
 *
 * Prinsip: Tiada data peribadi sensitif tanpa consent. `consent_status`
 * lalai 'unknown' dan `consent_evidence` wajib diisi sebelum blast.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sumber prospek (CSV / API connector / scraping)
        Schema::create('prospect_sources', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 150);
            $t->enum('type', ['csv', 'api', 'scrape', 'manual']);
            $t->string('connector_slug', 80)->nullable();
            // Config tidak pernah mengandungi rahsia PLAIN — rujuk credential_ref.
            $t->json('config')->nullable();
            $t->enum('status', ['active', 'paused', 'error'])->default('active');
            $t->timestamp('last_sync_at')->nullable();
            $t->text('last_error')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'type']);
        });

        // Senarai prospek (kelompok mengikut ICP)
        Schema::create('prospect_lists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 150);
            $t->text('description')->nullable();
            $t->json('criteria')->nullable();
            $t->json('icp')->nullable();
            $t->timestamps();

            $t->index('tenant_id');
        });

        // Prospek
        Schema::create('prospects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('source_id')->nullable()->constrained('prospect_sources')->nullOnDelete();
            $t->foreignId('list_id')->nullable()->constrained('prospect_lists')->nullOnDelete();
            $t->foreignId('crm_contact_id')->nullable();
            $t->string('first_name', 120)->nullable();
            $t->string('last_name', 120)->nullable();
            $t->string('email', 190)->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('whatsapp_number', 30)->nullable();
            $t->string('company', 180)->nullable();
            $t->string('job_title', 150)->nullable();
            $t->string('website', 250)->nullable();
            $t->string('linkedin', 250)->nullable();
            $t->string('country', 2)->nullable()->default('MY');
            $t->string('company_size', 50)->nullable();
            $t->decimal('revenue_estimate', 15, 2)->nullable();
            $t->json('enrichment')->nullable();
            $t->json('tags')->nullable();

            // Consent — medan kritikal untuk pematuhan PDPA.
            $t->enum('consent_status', ['unknown', 'granted', 'denied'])->default('unknown');
            $t->string('consent_source', 150)->nullable();
            $t->timestamp('consent_at')->nullable();
            // Bukti consent (URL borang, rujukan kontrak, ID rekod import).
            $t->string('consent_evidence', 500)->nullable();
            $t->timestamp('consent_expires_at')->nullable();

            $t->enum('verification_status', ['pending', 'verified', 'invalid', 'risky'])->default('pending');
            $t->integer('score')->default(0);
            $t->json('score_breakdown')->nullable();

            // Hash untuk dedupe — unik per tenant.
            $t->string('dedupe_hash', 64)->nullable();

            $t->enum('status', [
                'new', 'enriched', 'scored', 'approved', 'contacted',
                'replied', 'qualified', 'closed', 'lost',
            ])->default('new');

            $t->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('first_touched_at')->nullable();
            $t->timestamp('last_touched_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'consent_status']);
            $t->index(['tenant_id', 'score']);
            $t->index(['tenant_id', 'email']);
            $t->index(['tenant_id', 'company']);
            $t->unique(['tenant_id', 'dedupe_hash']);
        });

        // Audit prospect
        Schema::create('prospect_audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prospect_id')->nullable()->constrained('prospects')->cascadeOnDelete();
            $t->enum('actor_type', ['human', 'ai', 'system'])->default('human');
            $t->foreignId('actor_id')->nullable();
            $t->string('action', 100);
            $t->json('payload')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 250)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'created_at']);
            $t->index(['prospect_id', 'action']);
        });

        // Job scraping
        Schema::create('scraping_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('source_id')->nullable()->constrained('prospect_sources')->nullOnDelete();
            $t->string('name', 150);
            $t->json('target_url_patterns')->nullable();
            $t->enum('status', ['queued', 'running', 'completed', 'failed', 'cancelled'])->default('queued');
            // Default TRUE — Hormati robots.txt. Jangan ubah tanpa sebab etika.
            $t->boolean('respect_robots')->default(true);
            $t->integer('rate_limit_per_minute')->default(10);
            $t->integer('total_fetched')->default(0);
            $t->integer('total_skipped')->default(0);
            $t->integer('total_extracted')->default(0);
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        // Halaman yang diambil (cache + jejak)
        Schema::create('scraped_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('job_id')->nullable()->constrained('scraping_jobs')->cascadeOnDelete();
            $t->foreignId('source_id')->nullable()->constrained('prospect_sources')->nullOnDelete();
            $t->text('url');
            $t->text('final_url')->nullable();
            $t->integer('http_status')->nullable();
            $t->string('content_hash', 64)->nullable();
            $t->string('title', 300)->nullable();
            $t->json('extracted')->nullable();
            $t->timestamp('fetched_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'job_id']);
            $t->index(['tenant_id', 'content_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_pages');
        Schema::dropIfExists('scraping_jobs');
        Schema::dropIfExists('prospect_audit_logs');
        Schema::dropIfExists('prospects');
        Schema::dropIfExists('prospect_lists');
        Schema::dropIfExists('prospect_sources');
    }
};
