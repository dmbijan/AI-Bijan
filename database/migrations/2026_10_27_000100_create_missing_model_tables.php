<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 27 Okt 2026 — Jadual HILANG untuk model yang sudah wujud.
 *
 * ⚠️ MASALAH: 11 model Eloquent menunjuk ke jadual yang TIADA migration.
 * Setiap satu menghasilkan 500 "no such table" apabila dipanggil:
 *
 *   Projects: Milestone, ProjectDocument, ProjectActivity
 *   Assets  : MaintenanceRequest, MaintenancePart, AssetDocument, AssetTransfer
 *   Docs    : DocumentShare, DocumentTemplate, DocumentVersion,
 *             DocumentExtractionHistory
 *
 * Kesan sebenar: `MilestoneService::senarai()` — dipanggil oleh 8 tempat —
 * akan gagal pada setiap permintaan.
 *
 * Skema diambil daripada `$fillable` setiap model (bukan diteka).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── PROJECTS ──────────────────────────────────────────────────────
        Schema::create('milestones', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('project_id')->index();
            $t->string('name');
            $t->text('description')->nullable();
            $t->date('due_date')->nullable();
            $t->string('status', 30)->default('pending');   // pending|in_progress|completed|cancelled
            $t->decimal('completion_percentage', 5, 2)->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('project_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('project_id')->index();
            $t->unsignedBigInteger('uploaded_by')->nullable()->index();
            $t->string('file_path');
            $t->string('file_name');
            $t->string('mime_type', 150)->nullable();
            $t->unsignedBigInteger('file_size')->default(0);
            $t->string('folder', 100)->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
        });

        Schema::create('project_activities', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('project_id')->index();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('action', 80);
            $t->json('changes')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'project_id']);
        });

        // ── ASSETS ────────────────────────────────────────────────────────
        Schema::create('maintenance_requests', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('asset_id')->index();
            $t->unsignedBigInteger('requested_by')->nullable()->index();
            $t->unsignedBigInteger('assigned_to')->nullable()->index();
            $t->string('type', 50)->default('corrective');   // preventive|corrective|predictive
            $t->string('priority', 20)->default('medium');   // low|medium|high|critical
            $t->string('status', 30)->default('open');       // open|assigned|in_progress|resolved|closed
            $t->string('title');
            $t->text('description')->nullable();
            $t->text('resolution')->nullable();
            $t->date('scheduled_date')->nullable();
            $t->date('completed_date')->nullable();
            $t->decimal('estimated_cost', 15, 2)->default(0);
            $t->decimal('actual_cost', 15, 2)->default(0);
            $t->decimal('downtime_hours', 8, 2)->default(0);
            $t->unsignedBigInteger('vendor_id')->nullable()->index();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'priority']);
        });

        Schema::create('maintenance_parts', function (Blueprint $t) {
            $t->id();
            // ⚠️ Jadual anak — tiada tenant_id (diwarisi daripada induk)
            $t->unsignedBigInteger('maintenance_request_id')->index();
            $t->string('part_name');
            $t->string('part_number', 100)->nullable();
            $t->unsignedInteger('quantity')->default(1);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->decimal('total_cost', 15, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('asset_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('asset_id')->index();
            $t->unsignedBigInteger('uploaded_by')->nullable()->index();
            $t->string('file_path');
            $t->string('file_name');
            $t->string('mime_type', 150)->nullable();
            $t->unsignedBigInteger('file_size')->default(0);
            $t->string('type', 50)->nullable();   // invoice|warranty|manual|photo|other
            $t->timestamps();
        });

        Schema::create('asset_transfers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('asset_id')->index();
            $t->unsignedBigInteger('from_user')->nullable()->index();
            $t->unsignedBigInteger('to_user')->nullable()->index();
            $t->string('from_location')->nullable();
            $t->string('to_location')->nullable();
            $t->text('reason')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable()->index();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'asset_id']);
        });

        // ── DOCUMENTS ─────────────────────────────────────────────────────
        Schema::create('document_shares', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('document_id')->index();
            $t->unsignedBigInteger('shared_by')->nullable()->index();
            $t->string('token', 64)->unique();
            $t->enum('access_level', ['view', 'download', 'edit'])->default('view');
            $t->timestamp('expires_at')->nullable();
            $t->unsignedInteger('max_views')->nullable();
            $t->unsignedInteger('view_count')->default(0);
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('document_templates', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index();   // NULL = templat global
            $t->string('key', 80)->index();
            $t->string('name');
            $t->string('type', 50);              // invoice|quotation|receipt|contract
            $t->string('format', 20)->default('pdf');
            $t->json('variables')->nullable();
            $t->text('body')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['tenant_id', 'type']);
        });

        Schema::create('document_versions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('document_id')->index();
            $t->unsignedInteger('version')->default(1);
            $t->string('file_path')->nullable();
            $t->string('checksum', 64)->nullable();
            $t->unsignedBigInteger('created_by')->nullable()->index();
            $t->text('change_note')->nullable();
            $t->timestamps();

            $t->unique(['document_id', 'version']);
        });

        Schema::create('document_extraction_history', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('document_id')->nullable()->index();
            $t->string('method', 50)->default('ocr');    // ocr|classify|extract|embedding
            $t->string('model', 100)->nullable();
            $t->json('input_meta')->nullable();
            $t->json('output_data')->nullable();
            $t->decimal('confidence', 5, 2)->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->enum('status', ['success', 'failed', 'partial'])->default('success');
            $t->text('error_message')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_extraction_history');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_shares');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('maintenance_parts');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('project_activities');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('milestones');
    }
};
