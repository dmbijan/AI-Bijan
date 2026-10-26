<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create accounts_payable table.
     * 
     * Tracks amounts owed to suppliers (creditors).
     */
    public function up(): void
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Supplier reference
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            
            // Invoice details
            $table->string('invoice_number')->unique()->comment('Supplier invoice number');
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            
            // Amounts
            $table->decimal('original_amount', 18, 2)->default(0);
            $table->decimal('outstanding_amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            
            // Payment tracking
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('withholding_tax', 18, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'partial_paid', 'fully_paid', 'overdue', 'disputed', 'cancelled'])->default('pending');
            
            // Journal entry link
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'due_date']);
        });
        
        // Accounts receivable (customers who owe us money)
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Customer reference
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            
            // Invoice details
            $table->string('invoice_number')->unique()->comment('Customer invoice number');
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            
            // Amounts
            $table->decimal('original_amount', 18, 2)->default(0);
            $table->decimal('outstanding_amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            
            // Payment tracking
            $table->decimal('received_amount', 18, 2)->default(0);
            $table->decimal('discount_given', 18, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'partial_paid', 'fully_paid', 'overdue', 'bad_debt', 'cancelled'])->default('pending');
            
            // Aging buckets
            $table->integer('days_overdue')->default(0);
            $table->enum('aging_bucket', ['current', '1_30', '31_60', '61_90', '91_180', '181_plus'])->default('current');
            
            // Journal entry link
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'due_date']);
        });
        
        // Payments made/received
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            $table->enum('payment_type', ['payable', 'receivable'])->index();
            $table->morphs('payable'); // polymorphic: can link to accounts_payable or accounts_receivable
            
            // Payment details
            $table->string('payment_reference')->unique()->comment('PAY-YYYY-NNNNN or REC-YYYY-NNNNN');
            $table->date('payment_date')->index();
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            
            // Payment method
            $table->enum('method', ['cheque', 'wire_transfer', 'ewallet', 'cash', 'credit_card', 'other'])->default('wire_transfer');
            $table->string('reference_number')->nullable()->comment('Cheque/transaction reference');
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            
            // Journal entry link
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            // Reconciliation
            $table->boolean('is_reconciled')->default(false);
            $table->date('reconciled_at')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'payment_date']);
            $table->index(['tenant_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('accounts_payable');
    }
};
