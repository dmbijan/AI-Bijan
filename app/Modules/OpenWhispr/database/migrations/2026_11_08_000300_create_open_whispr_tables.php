<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->string('title', 150)->nullable();
            $table->string('language', 5)->default('ms');
            $table->string('engine', 40)->default('whisper_local');
            $table->string('model', 60)->nullable();
            $table->string('status', 20)->default('recording');
            $table->string('audio_path', 255)->nullable();
            $table->unsignedBigInteger('audio_size_bytes')->default(0);
            $table->decimal('duration_seconds', 10, 2)->default(0);
            $table->longText('raw_text')->nullable();
            $table->longText('processed_text')->nullable();
            $table->decimal('confidence', 5, 4)->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->json('segments')->nullable();
            $table->string('target_type', 60)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('processing_ms')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->unique(['tenant_id', 'uuid']);
        });

        Schema::create('dictation_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('word', 80);
            $table->json('aliases')->nullable();
            $table->string('category', 60)->nullable();
            $table->decimal('weight', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'word']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictation_vocabularies');
        Schema::dropIfExists('dictation_sessions');
    }
};
