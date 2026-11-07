<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('description')->nullable();
            $table->string('language', 5)->default('ms');
            $table->string('locale', 10)->default('ms-MY');
            $table->string('gender', 20)->nullable();
            $table->string('age_range', 30)->nullable();
            $table->decimal('pitch', 4, 2)->default(1.00);
            $table->decimal('speed', 4, 2)->default(1.00);
            $table->string('timbre', 60)->nullable();
            $table->string('emotion', 40)->nullable();
            $table->string('engine', 40)->default('elevenlabs');
            $table->string('engine_voice_id', 120)->nullable();
            $table->string('sample_path', 255)->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_cloned')->default(false);
            $table->json('settings')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedBigInteger('total_characters')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('voice_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->foreignId('voice_profile_id')->nullable()->constrained('voice_profiles')->cascadeOnDelete();
            $table->string('engine', 40)->default('elevenlabs');
            $table->longText('text');
            $table->unsignedInteger('text_length')->default(0);
            $table->string('audio_path', 255)->nullable();
            $table->string('audio_url', 500)->nullable();
            $table->decimal('duration_seconds', 10, 2)->default(0);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('format', 10)->default('mp3');
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->decimal('cost_rm', 12, 6)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'voice_profile_id']);
            $table->unique(['tenant_id', 'uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_tracks');
        Schema::dropIfExists('voice_profiles');
    }
};
