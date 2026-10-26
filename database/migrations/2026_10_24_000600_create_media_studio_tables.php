<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media Studio (BAB 82 / Fasa 4) — Media Vault, job, pemahaman, persona.
 *
 * ⚠️ Keselamatan: `media_personas` memerlukan pengesahan identiti +
 * consent sebelum boleh digunakan untuk blast video.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Persona video (avatar + suara)
        Schema::create('media_personas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120);
            $t->text('description')->nullable();
            // Rujukan avatar/suara di provider luaran (HeyGen/Synthesia).
            $t->string('avatar_ref', 190)->nullable();
            $t->string('voice_ref', 190)->nullable();
            $t->string('language', 8)->default('ms');
            // Rujukan consent biometrik / consent am (BAB 56C.6A).
            $t->unsignedBigInteger('consent_consent_id')->nullable();
            $t->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $t->timestamp('verified_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
        });

        // Aset media
        Schema::create('media_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->enum('kind', ['image', 'video', 'audio', 'document'])->index();
            $t->string('storage_disk', 40)->default('media');
            $t->string('storage_path', 500);
            $t->string('original_name', 250)->nullable();
            $t->string('mime', 120)->nullable();
            $t->bigInteger('size_bytes')->nullable();
            $t->integer('duration_seconds')->nullable();
            $t->integer('width')->nullable();
            $t->integer('height')->nullable();
            $t->string('checksum', 64)->nullable();
            $t->enum('source', ['upload', 'generated', 'recorded', 'screen'])->default('upload');
            $t->json('meta')->nullable();
            // Retensi — dipurge oleh scheduler harian.
            $t->timestamp('retention_expires_at')->nullable()->index();
            $t->timestamp('deleted_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'kind']);
            $t->index(['tenant_id', 'source']);
        });

        // Job media
        Schema::create('media_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->enum('kind', ['understanding', 'generation', 'translation', 'lipsync', 'training'])->index();
            $t->enum('status', [
                'queued', 'running', 'succeeded', 'failed', 'awaiting_approval',
            ])->default('queued')->index();
            $t->string('provider', 60)->nullable();
            $t->string('provider_job_id', 190)->nullable();
            $t->integer('progress')->default(0);
            $t->unsignedBigInteger('input_asset_id')->nullable();
            $t->unsignedBigInteger('output_asset_id')->nullable();
            $t->unsignedBigInteger('persona_id')->nullable();
            $t->json('params')->nullable();
            $t->text('error')->nullable();
            // Tahap autonomy tenant semasa job dicipta.
            $t->integer('autonomy_level')->default(0);
            $t->boolean('approval_required')->default(false);
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->integer('cost_credits')->default(0);
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index('input_asset_id');
            $t->index('output_asset_id');
        });

        // Transkripsi
        Schema::create('media_transcripts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained('media_assets')->cascadeOnDelete();
            $t->string('language', 8)->default('ms');
            // Segmen: [{start, end, text, speaker}]
            $t->json('segments')->nullable();
            $t->longText('text')->nullable();
            $t->decimal('confidence', 5, 4)->nullable();
            $t->json('keywords')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'asset_id']);
        });

        // Pemahaman
        Schema::create('media_understandings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained('media_assets')->cascadeOnDelete();
            $t->text('summary')->nullable();
            // Tindakan: [{actor, action, object, timestamp, confidence}]
            $t->json('actions')->nullable();
            // Entiti: {people: [], places: [], products: [], numbers: []}
            $t->json('entities')->nullable();
            // Timeline: [{time, event}]
            $t->json('timeline')->nullable();
            // QA pairs: [{question, answer}]
            $t->json('qa_pairs')->nullable();
            $t->string('model', 120)->nullable();
            $t->string('language', 8)->nullable();
            $t->decimal('confidence', 5, 4)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_understandings');
        Schema::dropIfExists('media_transcripts');
        Schema::dropIfExists('media_jobs');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('media_personas');
    }
};
