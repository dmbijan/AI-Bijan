<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create customers table.
     * 
     * Customer master data for CRM:
     * - Individual or business customers
     * - Malaysian compliance (SSM, SST, Tax ID)
     * - Credit limits & payment terms
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Customer identification
            $table->string('customer_code')->unique()->comment('CUST-YYYY-NNNNN');
            $table->enum('customer_type', ['individual', 'business'])->default('business');
            $table->string('name');
            $table->string('name_bm')->nullable();
            
            // Business registration (for business customers)
            $table->string('ssm_number')->nullable()->comment('SSM/Company Registration Number');
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
            $table->enum('category', ['retail', 'wholesale', 'distributor', 'enterprise', 'government'])->default('retail');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            
            // Financial settings
            $table->decimal('credit_limit', 18, 2)->default(0)->comment('Maximum credit allowed');
            $table->integer('payment_terms_days')->default(30);
            $table->string('currency', 3)->default('MYR');
            
            // Sales info
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('region')->nullable();
            
            // Banking
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
        });
        
        // Customer contacts (multiple per customer)
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            
            $table->timestamps();
        });
        
        // Leads table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Lead identification
            $table->string('lead_number')->unique()->comment('LEAD-YYYY-NNNNN');
            $table->string('source')->comment('Website, Referral, Cold Call, Trade Show, etc.');
            
            // Contact info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            
            // Lead details
            $table->text('requirements')->nullable();
            $table->decimal('estimated_value', 18, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            
            // Status tracking
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Conversion
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            
            // Loss reason
            $table->text('loss_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
        });
        
        // Opportunities table
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Opportunity identification
            $table->string('opportunity_number')->unique()->comment('OPP-YYYY-NNNNN');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            
            // Opportunity details
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('estimated_value', 18, 2)->default(0);
            $table->decimal('probability', 5, 2)->default(0)->comment('Win probability percentage');
            
            // Pipeline stage
            $table->enum('stage', [
                'prospecting',
                'qualification',
                'needs_analysis',
                'proposal',
                'negotiation',
                'closed_won',
                'closed_lost'
            ])->default('prospecting');
            
            // Dates
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            
            // Forecast
            $table->enum('forecast_category', ['best_case', 'commit', 'opportunity', 'pipeline'])->default('pipeline');
            
            // Competitor info
            $table->string('competitors')->nullable();
            $table->text('win_strategy')->nullable();
            $table->text('loss_reason')->nullable();
            
            // Next action
            $table->text('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            
            // Assignment
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'owner_id']);
        });
        
        // Quotations table
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Quotation identification
            $table->string('quotation_number')->unique()->comment('QT-YYYY-NNNNN');
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            
            // Dates
            $table->date('quotation_date')->index();
            $table->date('valid_until')->index();
            
            // Status
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            
            // Financial details
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0)->comment('SST if applicable');
            $table->decimal('shipping_cost', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            
            // Payment terms
            $table->integer('payment_terms_days')->default(30);
            $table->date('payment_due_date')->nullable();
            
            // Shipping
            $table->text('shipping_address')->nullable();
            $table->string('shipping_method')->nullable();
            
            // Terms & conditions
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Conversion
            $table->foreignId('order_id')->nullable()->comment('Converted to sales order');
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
        });
        
        // Quotation line items
        Schema::create('quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            
            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            
            $table->string('uom')->default('pcs');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_lines');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customers');
    }
};
