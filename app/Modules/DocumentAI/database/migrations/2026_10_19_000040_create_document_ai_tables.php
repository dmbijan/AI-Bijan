<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create documents table for AI-powered document processing.
     * 
     * Features:
     * - Document storage with metadata
     * - OCR processing status tracking
     * - ML extraction results
     * - Document classification
     * - Version control
     */
    public function up(): void
    {
        // Documents master table (dicipta oleh migration core jika belum wujud)
        if (Schema::hasTable('documents')) {
            return;
        }

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Document identification
            $table->string('document_number')->unique()->comment('DOC-YYYY-NNNNN');
            $table->string('title');
            $table->text('description')->nullable();
            
            // File information
            $table->string('file_path')->comment('Storage path for original file');
            $table->string('file_mime')->comment('MIME type (application/pdf, image/png, etc.)');
            $table->integer('file_size')->comment('File size in bytes');
            $table->string('file_hash')->nullable()->comment('SHA256 hash for integrity');
            
            // Document type classification
            $table->enum('document_type', [
                'invoice',
                'receipt',
                'purchase_order',
                'sales_order',
                'contract',
                'letter',
                'identity_card',
                'business_registration',
                'bank_statement',
                'tax_return',
                'custom'
            ])->default('custom');
            
            // OCR Processing
            $table->boolean('ocr_processed')->default(false);
            $table->timestamp('ocr_processed_at')->nullable();
            $table->text('ocr_text')->nullable()->comment('Raw extracted text from OCR');
            $table->decimal('ocr_confidence', 5, 2)->default(0)->comment('OCR confidence score 0-100');
            
            // ML Extraction Results
            $table->boolean('ml_extracted')->default(false);
            $table->timestamp('ml_extracted_at')->nullable();
            $table->json('extracted_fields')->nullable()->comment('Structured data extracted by ML');
            $table->decimal('ml_confidence', 5, 2)->default(0)->comment('ML extraction confidence score 0-100');
            
            // Document classification
            $table->enum('classification_status', ['pending', 'classified', 'review_required'])->default('pending');
            $table->string('classification_label')->nullable();
            $table->decimal('classification_confidence', 5, 2)->default(0);
            
            // Processing status
            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed', 'archived'])->default('uploaded');
            $table->text('error_message')->nullable();
            
            // Metadata extraction
            $table->date('document_date')->nullable()->comment('Date on document');
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->string('reference_number')->nullable();
            
            // Tags and categories
            $table->json('tags')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Retention
            $table->date('retention_until')->nullable();
            $table->boolean('is_confidential')->default(false);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'document_type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'classification_status']);
        });
        
        // Document versions for version control
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            
            $table->integer('version_number')->default(1);
            $table->string('file_path');
            $table->string('file_mime');
            $table->integer('file_size');
            $table->string('file_hash');
            $table->text('change_summary')->nullable();
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['document_id', 'version_number']);
        });
        
        // Document extraction history for audit trail
        Schema::create('document_extraction_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            
            $table->enum('extraction_type', ['ocr', 'ml_invoice', 'ml_receipt', 'ml_contract', 'custom']);
            $table->string('model_version')->nullable()->comment('ML model version used');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->integer('processing_time_ms')->default(0);
            $table->enum('status', ['success', 'failed', 'retrying'])->default('success');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            $table->index(['document_id', 'extraction_type']);
        });
        
        // Document templates for ML training
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            $table->string('template_name');
            $table->enum('template_type', ['invoice', 'receipt', 'purchase_order', 'contract', 'custom'])->default('invoice');
            
            $table->json('field_definitions')->nullable()->comment('Expected fields to extract');
            $table->text('sample_image_path')->nullable()->comment('Sample document for reference');
            
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->decimal('average_confidence', 5, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'template_type']);
        });
        
        // Document sharing table
        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            
            $table->string('share_token')->unique();
            $table->foreignId('shared_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->foreignId('shared_with_user')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shared_with_email')->nullable();
            
            $table->enum('access_level', ['view', 'download', 'edit'])->default('view');
            $table->timestamp('expires_at')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['share_token']);
            $table->index(['document_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_shares');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_extraction_history');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
