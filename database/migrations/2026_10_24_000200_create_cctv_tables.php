<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Vision Fasa 1 (BAB 56C) — kamera dan peristiwa.
 *
 * Skop ketat: daftar kamera, terima peristiwa dari Vision Service, retensi,
 * audit tontonan. TIADA face recognition di sini — ia melalui pagar
 * biometrik BAB 56C.6A.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_cameras', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // Nombor cawangan tanpa FK: modul Organization/Branch belum berkod.
            $t->unsignedBigInteger('branch_id')->nullable();
            $t->string('name', 120);
            $t->string('location', 200)->nullable();
            // RTSP disimpan berenkripsi; ruang 1024 untuk URL panjang.
            $t->text('rtsp_url_encrypted')->nullable();
            $t->enum('status', ['aktif', 'tidak_aktif', 'rosak'])->default('aktif');
            $t->boolean('record_events')->default(true);
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('cctv_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('camera_id')->constrained('cctv_cameras')->cascadeOnDelete();
            $t->enum('event_type', ['person', 'vehicle', 'loitering', 'perimeter_breach', 'after_hours', 'motion']);
            $t->decimal('confidence', 5, 4)->default(0);
            $t->timestamp('started_at');
            $t->timestamp('ended_at')->nullable();
            $t->string('clip_path')->nullable();
            $t->string('thumbnail_path')->nullable();
            $t->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('acknowledged_at')->nullable();
            $t->string('note', 500)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'started_at']);
            $t->index(['tenant_id', 'event_type']);
        });

        Schema::create('cctv_retention', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('camera_id')->constrained('cctv_cameras')->cascadeOnDelete();
            $t->unsignedInteger('days')->default(14);
            $t->unsignedBigInteger('storage_used_mb')->default(0);
            $t->timestamps();

            $t->unique(['camera_id']);
        });

        Schema::create('cctv_view_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('camera_id')->nullable()->constrained('cctv_cameras')->nullOnDelete();
            $t->foreignId('event_id')->nullable()->constrained('cctv_events')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 30)->default('view_live');
            $t->string('ip_address', 45)->nullable();
            $t->string('purpose', 100)->nullable();
            $t->timestamp('created_at')->nullable();

            $t->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctv_view_logs');
        Schema::dropIfExists('cctv_retention');
        Schema::dropIfExists('cctv_events');
        Schema::dropIfExists('cctv_cameras');
    }
};
