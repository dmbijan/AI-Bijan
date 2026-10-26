<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create chart_of_accounts table.
     * 
     * Malaysian-standard chart of accounts with proper account types:
     * - 1xxx = Assets (Aset)
     * - 2xxx = Liabilities (Kewajipan)
     * - 3xxx = Equity (Ekuiti)
     * - 4xxx = Revenue (Pendapatan)
     * - 5xxx = Cost of Goods Sold (Harga Pokok Jualan)
     * - 6xxx = Operating Expenses (Perbelanjaan Operasi)
     */
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Account identification
            $table->string('code')->comment('Account code e.g., 10100, 40100');
            $table->string('name');
            $table->string('name_bm')->nullable()->comment('Name in Bahasa Melayu');
            
            // Account classification
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense', 'cost_of_goods'])->index();
            $table->enum('subtype', [
                'current_asset', 'non_current_asset', 'cash', 'bank', 
                'accounts_receivable', 'inventory',
                'current_liability', 'non_current_liability', 'accounts_payable',
                'equity', 'retained_earnings',
                'operating_revenue', 'other_income',
                'cost_direct', 'cost_indirect',
                'operating_expense', 'depreciation', 'tax_expense'
            ])->nullable();
            
            // Account behavior
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('System accounts cannot be deleted');
            $table->boolean('allow_posting')->default(true)->comment('Can journal entries be posted to this account?');
            
            // Normal balance direction
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
            
            // Parent account for hierarchical structure
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            
            // Summary/consolidation
            $table->boolean('is_summary')->default(false)->comment('Is this a summary account for consolidation?');
            
            // Metadata
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'code'], 'account_code_unique');
            $table->index(['tenant_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
