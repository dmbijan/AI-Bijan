<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Dokumen rasmi: rekod dikeluarkan (salinan data + versi templat + cap SHA-256) dan siri nombor per jenis/tahun.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->ulid('ulid')->unique();
            $t->string('type', 40);
            $t->string('number', 40);
            $t->string('title');
            $t->string('locale', 2)->default('ms');
            $t->string('theme', 20)->default('korporat');
            $t->string('template_version', 40);
            $t->json('data');
            $t->char('sha256', 64);
            $t->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('status', ['dikeluarkan', 'dibatalkan'])->default('dikeluarkan');
            $t->string('void_reason')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'type', 'number']);
            $t->index(['tenant_id', 'type', 'created_at']);
        });

        Schema::create('document_sequences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('type', 40);
            $t->unsignedSmallInteger('year');
            $t->unsignedInteger('last')->default(0);
            $t->unique(['tenant_id', 'type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('documents');
    }
};
