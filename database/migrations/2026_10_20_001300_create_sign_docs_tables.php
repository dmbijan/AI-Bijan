<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sign_docs_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('category')->default('general');
            $table->json('tags')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('sign_docs_documents')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->boolean('is_template')->default(false);
            $table->foreignId('template_id')->nullable()->constrained('sign_docs_documents')->nullOnDelete();
            $table->date('expiry_date')->nullable();
            $table->string('access_level')->default('private');
            $table->boolean('password_protected')->default(false);
            $table->string('watermark_text')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->string('share_link')->nullable();
            $table->timestamp('share_expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('category');
            $table->index('status');
            $table->index('share_link');
            $table->index(['is_template', 'status']);
        });

        Schema::create('sign_docs_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('other');
            $table->longText('content')->nullable();
            $table->json('placeholders')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('sign_docs_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('sign_docs_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signer_role')->nullable();
            $table->string('signature_type'); // drawn, typed, uploaded, digital_certificate
            $table->json('signature_data')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->integer('signature_position_x')->default(0);
            $table->integer('signature_position_y')->default(0);
            $table->unsignedInteger('signature_page')->default(1);
            $table->string('font_family')->nullable();
            $table->unsignedInteger('font_size')->default(16);
            $table->string('digital_certificate_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status')->default('pending'); // pending, signed, rejected, expired
            $table->timestamp('signed_at')->nullable();
            $table->json('audit_trail_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('document_id');
            $table->index('status');
            $table->index('tenant_id');
        });

        Schema::create('sign_docs_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('sign_docs_documents')->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('step_type'); // review, approval, signature
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('document_id');
            $table->index('status');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sign_docs_workflow_steps');
        Schema::dropIfExists('sign_docs_signatures');
        Schema::dropIfExists('sign_docs_templates');
        Schema::dropIfExists('sign_docs_documents');
    }
};
