<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_brand_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('brand', 120)->nullable();
            $table->string('handle', 80)->nullable();
            $table->string('cta', 200)->nullable();
            $table->string('color_primary', 9)->nullable();
            $table->string('color_accent', 9)->nullable();
            $table->string('color_background', 9)->nullable();
            $table->string('font', 60)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('schema_version')->default(1);
            $table->timestamps();

            $table->index(['tenant_id', 'is_default']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('video_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->string('name', 200);
            $table->string('slug', 220);
            $table->string('skill', 30)->default('mograph');
            $table->string('status', 20)->default('draft');
            $table->string('topic', 300)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('gaya', 40)->nullable();
            $table->string('aspect_ratio', 10)->default('9:16');
            $table->unsignedTinyInteger('frame_rate')->default(60);
            $table->decimal('duration_seconds', 10, 2)->default(0);
            $table->foreignId('brand_kit_id')->nullable()->constrained('video_brand_kits')->nullOnDelete();
            $table->string('voiceover_path', 500)->nullable();
            $table->string('script_path', 500)->nullable();
            $table->string('source_video_path', 500)->nullable();
            $table->string('output_path', 500)->nullable();
            $table->string('output_url', 500)->nullable();
            $table->string('thumb_path', 500)->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('stage', 60)->nullable();
            $table->json('qc_report')->nullable();
            $table->json('settings')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('preflight_ok')->default(false);
            $table->json('preflight_report')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'skill']);
            $table->unique(['tenant_id', 'uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_projects');
        Schema::dropIfExists('video_brand_kits');
    }
};
