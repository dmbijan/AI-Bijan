<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('uuid');
            $table->string('title', 250)->nullable();
            $table->text('description')->nullable();
            $table->string('recording_path', 500)->nullable();
            $table->string('recording_url', 500)->nullable();
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('status', 20)->default('uploading');
            $table->decimal('duration_seconds', 12, 3)->default(0);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('resolution', 20)->nullable();
            $table->unsignedTinyInteger('frame_rate')->default(30);
            $table->boolean('has_audio')->default(true);
            $table->boolean('has_mouse_cursor')->default(true);
            $table->string('privacy', 20)->default('private');
            $table->string('share_token', 64)->unique();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'privacy']);
        });

        Schema::create('recording_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('recording_id')->constrained('screen_recordings')->cascadeOnDelete();
            $table->text('body');
            $table->decimal('timestamp_seconds', 12, 3)->default(0);
            $table->decimal('x_position', 8, 4)->default(0);
            $table->decimal('y_position', 8, 4)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 150)->nullable();
            $table->timestamps();

            $table->index(['recording_id', 'timestamp_seconds']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recording_comments');
        Schema::dropIfExists('screen_recordings');
    }
};
