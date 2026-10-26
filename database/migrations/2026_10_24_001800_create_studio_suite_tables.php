<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow Studio, Media Editor, Live Support & Community
 * (BAB 86 / Idea #26, #27, #28, #29).
 *
 * ✅ Didapati daripada audit:
 * - n8n (208k stars, ⚠️ Sustainable Use License): 1500+ integrasi, 9000+ template
 *   → corak "visual canvas + template marketplace" (RUJUKAN REKA BENTUK sahaja)
 * - Hermes Agent (MIT): "skills + plugin catalog" → corak template
 * - OpenClaw (MIT): "ClawHub plugin SDK + registry" → corak marketplace
 * - CapCut/Canva: tenant mahu edit gambar/video sendiri tanpa pereka bentuk
 * - "call service customer service live di jaga oleh ai, connect ke e-SIM"
 * - "buatkan satu community AI BIJAN"
 *
 * Semua modul di bawah dijana dalam SATU migration supaya mudah dijejak.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // B-Workflow Studio (Idea #26) — visual workflow + template
        // ============================================================

        Schema::create('studio_workflow_templates', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index(); // null = template global
            $t->string('key');
            $t->string('name');
            $t->string('category')->default('general');        // sales|marketing|ops|finance|hr|ai
            $t->text('description')->nullable();
            $t->json('definition');                           // {nodes:[], edges:[]}
            $t->json('required_connectors')->nullable();
            $t->json('required_permissions')->nullable();
            $t->unsignedInteger('install_count')->default(0);
            $t->boolean('is_official')->default(false);
            $t->boolean('is_active')->default(true);
            $t->string('license')->default('proprietary');
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'category']);
            $t->index(['key']);
        });

        Schema::create('studio_workflows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('workflow_template_id')->nullable();
            $t->string('name');
            $t->text('description')->nullable();
            $t->json('definition');                           // nodes + edges + config
            $t->string('status')->default('draft');           // draft|active|paused|archived
            $t->string('trigger_type')->default('manual');    // manual|schedule|event|webhook|ai
            $t->string('trigger_config')->nullable();          // JSON
            $t->boolean('is_enabled')->default(false);
            $t->boolean('dry_run_mode')->default(true);        // lalai SELAMAT
            $t->unsignedInteger('run_count')->default(0);
            $t->unsignedInteger('success_count')->default(0);
            $t->unsignedInteger('failure_count')->default(0);
            $t->timestamp('last_run_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('studio_workflow_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('workflow_id')->index();
            $t->string('status')->default('running');          // running|completed|failed|cancelled
            $t->string('trigger_type')->default('manual');
            $t->json('trigger_payload')->nullable();
            $t->json('node_results')->nullable();
            $t->string('current_node_id')->nullable();
            $t->unsignedInteger('nodes_executed')->default(0);
            $t->unsignedInteger('nodes_failed')->default(0);
            $t->unsignedInteger('duration_ms')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'workflow_id']);
        });

        // ============================================================
        // B-MediaEditor (Idea #27) — edit gambar/video tanpa designer
        // ============================================================

        Schema::create('media_projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('project_type')->default('image');      // image|video|carousel|story
            $t->string('status')->default('draft');            // draft|editing|rendering|ready|failed
            $t->json('canvas');                                // saiz, latar belakang
            $t->json('layers')->nullable();                   // senarai lapisan (teks, imej, bentuk)
            $t->json('timeline')->nullable();                 // video: tracks & clips
            $t->json('brand_kit')->nullable();
            $t->string('output_url')->nullable();
            $t->string('thumbnail_url')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->string('resolution')->default('1080x1080');
            $t->json('meta')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'project_type']);
        });

        Schema::create('media_templates', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index();
            $t->string('key');
            $t->string('name');
            $t->string('category')->default('social');         // social|product|story|promo|education
            $t->string('project_type')->default('image');
            $t->json('definition');                           // layers/timeline template
            $t->string('preview_url')->nullable();
            $t->string('resolution')->default('1080x1080');
            $t->unsignedInteger('use_count')->default(0);
            $t->boolean('is_official')->default(false);
            $t->boolean('is_active')->default(true);
            $t->string('license')->default('proprietary');
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'category']);
        });

        Schema::create('media_render_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('media_project_id')->index();
            $t->string('status')->default('queued');           // queued|rendering|completed|failed
            $t->string('output_format')->default('mp4');       // mp4|webm|png|jpg|gif
            $t->string('resolution')->nullable();
            $t->string('output_url')->nullable();
            $t->text('error_message')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->unsignedInteger('file_size_kb')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });

        // ============================================================
        // B-LiveSupport (Idea #28) — call center AI + eSIM
        // ============================================================

        Schema::create('live_support_channels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('channel_type')->default('voice');      // voice|video|chat|whatsapp_call
            $t->string('provider')->nullable();                // twilio|esim_operator|webrtc
            $t->string('inbound_number')->nullable();
            $t->string('esim_iccid')->nullable();
            $t->string('esim_provider')->nullable();
            $t->json('config')->nullable();
            $t->boolean('ai_enabled')->default(true);
            $t->boolean('human_fallback')->default(true);
            $t->decimal('ai_confidence_threshold', 5, 2)->default(0.75);
            $t->string('status')->default('active');
            $t->unsignedInteger('max_concurrent')->default(5);
            $t->json('business_hours')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('live_support_calls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('live_support_channel_id')->index();
            $t->string('external_call_id')->nullable();
            $t->string('direction')->default('inbound');       // inbound|outbound
            $t->string('caller_number')->nullable();
            $t->string('caller_name')->nullable();
            $t->string('status')->default('ringing');          // ringing|answered|ai_handling|human_handling|completed|missed|failed
            $t->string('handler_type')->default('ai');          // ai|human
            $t->unsignedBigInteger('handler_user_id')->nullable();
            $t->text('ai_summary')->nullable();
            $t->json('ai_actions')->nullable();
            $t->string('sentiment')->nullable();
            $t->string('escalation_reason')->nullable();
            $t->string('recording_url')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->decimal('confidence_avg', 5, 2)->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('answered_at')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('live_support_transcripts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('live_support_call_id')->index();
            $t->string('speaker');                             // caller|ai|human
            $t->text('text');
            $t->decimal('confidence', 5, 2)->nullable();
            $t->unsignedInteger('offset_ms')->default(0);
            $t->timestamps();
        });

        // ============================================================
        // B-Community (Idea #29) — komuniti AI BIJAN
        // ============================================================

        Schema::create('community_spaces', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->string('slug')->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('space_type')->default('forum');        // forum|group|academy|showcase|help
            $t->string('visibility')->default('public');       // public|members|private
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedInteger('member_count')->default(0);
            $t->unsignedInteger('post_count')->default(0);
            $t->boolean('is_active')->default(true);
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('community_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('community_space_id')->index();
            $t->unsignedBigInteger('author_user_id')->nullable();
            $t->string('post_type')->default('discussion');    // discussion|question|showcase|announcement|idea
            $t->string('title');
            $t->text('body')->nullable();
            $t->json('attachments')->nullable();
            $t->json('tags')->nullable();
            $t->string('status')->default('published');        // draft|published|hidden|removed
            $t->unsignedInteger('view_count')->default(0);
            $t->unsignedInteger('like_count')->default(0);
            $t->unsignedInteger('reply_count')->default(0);
            $t->boolean('is_pinned')->default(false);
            $t->boolean('is_solved')->default(false);
            $t->unsignedBigInteger('accepted_reply_id')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['community_space_id', 'status']);
        });

        Schema::create('community_replies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('community_post_id')->index();
            $t->unsignedBigInteger('author_user_id')->nullable();
            $t->text('body');
            $t->unsignedInteger('like_count')->default(0);
            $t->boolean('is_accepted')->default(false);
            $t->string('status')->default('published');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_replies');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('community_spaces');
        Schema::dropIfExists('live_support_transcripts');
        Schema::dropIfExists('live_support_calls');
        Schema::dropIfExists('live_support_channels');
        Schema::dropIfExists('media_render_jobs');
        Schema::dropIfExists('media_templates');
        Schema::dropIfExists('media_projects');
        Schema::dropIfExists('studio_workflow_runs');
        Schema::dropIfExists('studio_workflows');
        Schema::dropIfExists('studio_workflow_templates');
    }
};
