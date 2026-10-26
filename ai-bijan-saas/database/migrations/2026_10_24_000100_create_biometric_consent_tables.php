<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biometric consent gate (BAB 56C.6A).
 *
 * Face recognition TIDAK boleh aktif selagi pagar consent belum lengkap:
 * consent berasingan, kaedah alternatif, notis PDPA, pegawai PDPA,
 * tempoh retensi dan semakan undang-undang.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Konfigurasi pagar per tenant (satu baris per tenant).
        Schema::create('biometric_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $t->boolean('enabled')->default(false);
            $t->string('alternative_method', 30)->nullable(); // rfid|pin|qr|manual
            $t->string('dpo_name')->nullable();
            $t->string('dpo_email')->nullable();
            $t->unsignedInteger('retention_days')->default(30);
            $t->string('consent_version', 20)->nullable();
            $t->date('legal_review_date')->nullable();
            $t->text('privacy_notice')->nullable();
            $t->timestamps();
        });

        // Consent individu per pekerja.
        Schema::create('biometric_consents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('consent_version', 20);
            $t->timestamp('signed_at');
            $t->timestamp('withdrawn_at')->nullable();
            $t->string('document_path')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'user_id']);
        });

        // Embedding wajah — hanya wujud jika consent sah.
        Schema::create('biometric_embeddings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('consent_id')->constrained('biometric_consents')->cascadeOnDelete();
            $t->longText('embedding');
            $t->decimal('quality_score', 5, 4)->default(0);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'user_id']);
        });

        // Audit setiap cubaan padanan.
        Schema::create('biometric_access_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('camera_ref', 100)->nullable();
            $t->boolean('matched')->default(false);
            $t->decimal('confidence', 5, 4)->nullable();
            $t->string('purpose', 50)->default('attendance');
            $t->string('outcome', 30)->default('ok'); // ok|no_consent|not_matched|gate_locked
            $t->timestamp('created_at')->nullable();

            $t->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_access_logs');
        Schema::dropIfExists('biometric_embeddings');
        Schema::dropIfExists('biometric_consents');
        Schema::dropIfExists('biometric_settings');
    }
};
