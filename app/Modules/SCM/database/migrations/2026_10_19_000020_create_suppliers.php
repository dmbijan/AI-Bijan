<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create suppliers table.
     * 
     * Supplier master data with Malaysian compliance fields:
     * - Business registration (SSM, GST, SST)
     * - Payment terms & bank details
     * - Performance tracking
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Company identification
            $table->string('supplier_code')->unique()->comment('SUP-YYYY-NNNNN');
            $table->string('company_name');
            $table->string('company_name_bm')->nullable();
            
            // Registration numbers
            $table->string('ssm_number')->nullable()->comment('SSM/Company Registration Number');
            $table->string('gst_number')->nullable()->comment('GST Registration Number');
            $table->string('sst_number')->nullable()->comment('SST Registration Number');
            $table->string('tax_identification')->nullable()->comment('Business Tax ID');
            
            // Contact information
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            
            // Classification
            $table->enum('category', ['raw_materials', 'services', 'equipment', 'office_supplies', 'logistics', 'other'])->default('other');
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            
            // Payment terms
            $table->integer('payment_terms_days')->default(30)->comment('Net 30, Net 60, etc.');
            $table->string('currency', 3)->default('MYR');
            
            // Bank details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            
            // Withholding tax rate (Malaysian WP 10/15/25)
            $table->decimal('withholding_tax_rate', 5, 2)->default(0)->comment('WP 10%, 15%, or 25%');
            
            // Performance tracking
            $table->decimal('rating', 2, 1)->default(0)->comment('1.0 - 5.0 star rating');
            $table->integer('on_time_delivery_count')->default(0);
            $table->integer('late_delivery_count')->default(0);
            $table->integer('quality_issues_count')->default(0);
            
            // Approval
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
        });
        
        // Supplier contacts (multiple per supplier)
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('suppliers');
    }
};
