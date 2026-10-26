<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create journal_entries table.
     * 
     * Double-entry bookkeeping system:
     * - Each journal entry must balance (total debits = total credits)
     * - Supports multi-line entries per transaction
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Entry identification
            $table->string('entry_number')->unique()->comment('JE-YYYY-NNNNN');
            $table->date('entry_date')->index()->comment('Transaction date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft')->index();
            
            // Transaction details
            $table->string('reference_number')->nullable()->comment('External reference (invoice, receipt, etc.)');
            $table->text('description')->nullable()->comment('Narration/description');
            $table->text('narration_bm')->nullable()->comment('Bahasa Melayu narration');
            
            // Period tracking
            $table->string('fiscal_year')->nullable()->comment('FY-YYYY');
            $table->string('period')->nullable()->comment('YYYY-MM');
            
            // Source tracking
            $table->string('source_module')->nullable()->comment('Payroll, SCM, CRM, Manual, etc.');
            $table->string('source_id')->nullable()->comment('ID of source document');
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'entry_date']);
            $table->index(['tenant_id', 'fiscal_year', 'period']);
        });
        
        // Journal entry lines (debit/credit lines)
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            
            // Amounts
            $table->decimal('debit', 18, 2)->default(0)->comment('Debit amount');
            $table->decimal('credit', 18, 2)->default(0)->comment('Credit amount');
            
            // Line details
            $table->text('description')->nullable();
            
            // Currency (for multi-currency support)
            $table->string('currency', 3)->default('MYR');
            $table->decimal('exchange_rate', 10, 6)->default(1)->comment('Exchange rate to MYR');
            $table->decimal('amount_foreign', 18, 2)->default(0)->comment('Amount in foreign currency');
            
            // Reference
            $table->string('cost_center')->nullable()->comment('Cost center code');
            $table->string('project')->nullable()->comment('Project code');
            
            $table->integer('line_number')->default(0);
            
            $table->index(['account_id']);
            $table->index(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
