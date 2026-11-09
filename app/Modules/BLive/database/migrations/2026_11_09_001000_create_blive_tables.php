<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Live AI - jadual asas (Fasa 1/2).
 *
 * Semua jadual membawa `tenant_id` dan dipisah oleh indeks kompaun
 * supaya pertanyaan tenant sentiasa menggunakan indeks yang betul.
 */
return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------
        // Host personalities - personaliti hos AI
        // -----------------------------------------------------------------
        Schema::create('blive_host_personalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('description')->nullable();
            $table->string('archetype', 60)->default('professional_presenter');
            $table->string('primary_language', 5)->default('ms');
            $table->string('locale', 10)->default('ms-MY');
            $table->json('supported_languages')->nullable();
            $table->text('system_instructions')->nullable();
            $table->json('tone_profile')->nullable();
            $table->decimal('speaking_rate', 4, 2)->default(1.00);
            $table->boolean('allow_code_switching')->default(true);
            $table->unsignedInteger('max_speech_characters')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('safety_policies')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'slug']);
        });

        // -----------------------------------------------------------------
        // Avatars - manusia digital
        // -----------------------------------------------------------------
        Schema::create('blive_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('description')->nullable();
            $table->string('avatar_type', 30)->default('ai_generated');
            $table->string('engine', 40)->default('simulated');
            $table->string('model_reference', 255)->nullable();
            $table->unsignedInteger('asset_version')->default(1);
            $table->foreignId('voice_profile_id')->nullable();
            $table->foreignId('host_personality_id')->nullable()->constrained('blive_host_personalities')->nullOnDelete();
            $table->string('facial_profile', 120)->nullable();
            $table->string('animation_profile', 120)->nullable();
            $table->foreignId('consent_record_id')->nullable();
            $table->string('render_quality', 20)->default('high');
            $table->string('asset_storage_path', 500)->nullable();
            $table->json('appearance')->nullable();
            $table->json('body_traits')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('consent_status', 30)->default('pending');
            $table->text('consent_reference')->nullable();
            $table->boolean('is_ai_disclosed')->default(true);
            $table->unsignedBigInteger('render_minutes_used')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'consent_status']);
            $table->unique(['tenant_id', 'uuid']);
            $table->unique(['tenant_id', 'slug']);
        });

        // -----------------------------------------------------------------
        // Consent records - kebenaran identiti / suara
        // -----------------------------------------------------------------
        Schema::create('blive_consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('avatar_id')->nullable()->constrained('blive_avatars')->cascadeOnDelete();
            $table->string('consent_type', 40)->default('ai_generated');
            $table->string('subject_name', 120)->nullable();
            $table->string('subject_reference', 255)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('evidence_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'uuid']);
        });

        // -----------------------------------------------------------------
        // Live sessions - sesi=live host
        // -----------------------------------------------------------------
        Schema::create('blive_live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('avatar_id')->nullable()->constrained('blive_avatars')->nullOnDelete();
            $table->foreignId('host_personality_id')->nullable()->constrained('blive_host_personalities')->nullOnDelete();
            $table->string('title', 180);
            $table->string('session_mode', 30)->default('ai_assisted');
            $table->string('autonomy_level', 30)->default('supervised');
            $table->string('status', 30)->default('draft');
            $table->string('resolution', 20)->default('1920x1080');
            $table->string('orientation', 20)->default('landscape');
            $table->unsignedInteger('fps')->default(30);
            $table->unsignedInteger('bitrate_kbps')->default(4500);
            $table->string('language', 5)->default('ms');
            $table->json('product_ids')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('render_minutes_used')->default(0);
            $table->unsignedInteger('comments_handled')->default(0);
            $table->unsignedInteger('speech_count')->default(0);
            $table->decimal('cost_rm', 12, 6)->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
            $table->unique(['tenant_id', 'uuid']);
        });

        // -----------------------------------------------------------------
        // Stream destinations - satu sesi ke beberapa platform
        // -----------------------------------------------------------------
        Schema::create('blive_stream_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('live_session_id')->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('platform', 30);
            $table->string('channel_name', 120)->nullable();
            $table->string('channel_reference', 255)->nullable();
            $table->string('credential_reference', 255)->nullable();
            $table->string('stream_status', 30)->default('pending');
            $table->string('ingest_url', 500)->nullable();
            $table->string('stream_key_reference', 255)->nullable();
            $table->unsignedInteger('bitrate_kbps')->default(4500);
            $table->string('resolution', 20)->default('1920x1080');
            $table->string('orientation', 20)->default('landscape');
            $table->unsignedInteger('fps')->default(30);
            $table->text('last_error')->nullable();
            $table->unsignedInteger('reconnect_count')->default(0);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'stream_status']);
            $table->index(['live_session_id', 'platform']);
            $table->unique(['tenant_id', 'uuid']);
        });

        // -----------------------------------------------------------------
        // Live controls - audit jejak arahan
        // -----------------------------------------------------------------
        Schema::create('blive_live_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('live_session_id')->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('control_type', 40);
            $table->string('issued_by', 30)->default('ai');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('payload')->nullable();
            $table->text('result')->nullable();
            $table->boolean('approved')->default(false);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'live_session_id']);
            $table->index(['live_session_id', 'control_type']);
        });

        // -----------------------------------------------------------------
        // Live messages - perbualan host & penonton
        // -----------------------------------------------------------------
        Schema::create('blive_live_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('live_session_id')->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('role', 20)->default('host');
            $table->string('source', 30)->default('ai');
            $table->string('language', 5)->default('ms');
            $table->text('content');
            $table->unsignedInteger('content_length')->default(0);
            $table->string('intent', 40)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->boolean('is_question')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->boolean('escalated_to_human')->default(false);
            $table->unsignedInteger('audience_score')->default(0);
            $table->string('platform', 30)->nullable();
            $table->string('external_id', 120)->nullable();
            $table->unsignedBigInteger('tokens_used')->default(0);
            $table->decimal('cost_rm', 12, 6)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'live_session_id']);
            $table->index(['live_session_id', 'role']);
            $table->index(['live_session_id', 'is_answered']);
        });

        // -----------------------------------------------------------------
        // Moderation events
        // -----------------------------------------------------------------
        Schema::create('blive_moderation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('live_session_id')->nullable()->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('severity', 20)->default('low');
            $table->string('category', 40)->default('spam');
            $table->text('content')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->string('source', 30)->default('ai');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'severity']);
            $table->index(['live_session_id', 'category']);
        });

        // -----------------------------------------------------------------
        // Live metrics - analitik
        // -----------------------------------------------------------------
        Schema::create('blive_live_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('live_session_id')->nullable()->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('metric_key', 60);
            $table->string('metric_scope', 30)->default('session');
            $table->unsignedBigInteger('value_numeric')->default(0);
            $table->decimal('value_decimal', 16, 4)->default(0);
            $table->string('platform', 30)->nullable();
            $table->date('recorded_on')->nullable();
            $table->json('dimensions')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'metric_key']);
            $table->index(['live_session_id', 'metric_key']);
            $table->index(['tenant_id', 'recorded_on']);
        });

        // -----------------------------------------------------------------
        // Render jobs - avatar generation and render jobs
        // -----------------------------------------------------------------
        Schema::create('blive_render_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('avatar_id')->nullable()->constrained('blive_avatars')->cascadeOnDelete();
            $table->foreignId('live_session_id')->nullable()->constrained('blive_live_sessions')->cascadeOnDelete();
            $table->string('job_type', 30)->default('avatar_generate');
            $table->string('engine', 40)->default('simulated');
            $table->string('status', 30)->default('queued');
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('progress_percent')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['avatar_id', 'job_type']);
            $table->unique(['tenant_id', 'uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blive_render_jobs');
        Schema::dropIfExists('blive_live_metrics');
        Schema::dropIfExists('blive_moderation_events');
        Schema::dropIfExists('blive_live_messages');
        Schema::dropIfExists('blive_live_controls');
        Schema::dropIfExists('blive_stream_destinations');
        Schema::dropIfExists('blive_live_sessions');
        Schema::dropIfExists('blive_consent_records');
        Schema::dropIfExists('blive_avatars');
        Schema::dropIfExists('blive_host_personalities');
    }
};
