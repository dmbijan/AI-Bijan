<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vector store tenant (BAB 17A.4 / T-08).
 *
 * Fasa 1: simpan embedding dalam JSON + carian kosinus dalam PHP (fallback).
 * Qdrant kekal sebagai sasaran produksi; jadual ini membolehkan carian semantik
 * berjalan tanpa server vector luaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title', 200);
            $t->string('doc_type', 50)->default('custom'); // sop|policy|contract|product_info|training|notes|custom
            $t->string('source', 255)->nullable();
            $t->longText('content');
            $t->string('content_hash', 64)->index();
            $t->unsignedInteger('chunk_count')->default(0);
            $t->boolean('is_active')->default(true);
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'doc_type']);
            $t->unique(['tenant_id', 'content_hash']);
        });

        Schema::create('knowledge_chunks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('document_id')->constrained('knowledge_documents')->cascadeOnDelete();
            $t->unsignedSmallInteger('chunk_index');
            $t->text('text');
            // Embedding disimpan sebagai JSON (array float). NULL = belum dijana.
            $t->longText('embedding')->nullable();
            $t->unsignedSmallInteger('dimensions')->nullable();
            $t->string('embedding_model', 100)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'document_id']);
            $t->unique(['document_id', 'chunk_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
        Schema::dropIfExists('knowledge_documents');
    }
};
