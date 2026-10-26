<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create employees table.
     * Stores employee master data for payroll processing.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Personal identification
            $table->string('name');
            $table->string('ic_no')->unique()->comment('MyKad number');
            $table->string('epf_no')->nullable()->comment('KWSP member number');
            $table->string('socso_no')->nullable()->comment('PERKESO/SOCSO number');
            $table->string('eis_no')->nullable()->comment('EIS/SIP reference (usually same as SOCSO)');
            $table->string('tax_no')->nullable()->comment('PCB/TIN number');
            
            // Employment details
            $table->string('position');
            $table->string('department')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->comment('Termination date');
            $table->enum('employment_type', ['permanent', 'contract', 'part_time', 'casual'])->default('permanent');
            
            // Salary structure
            $table->decimal('basic_salary', 12, 2)->default(0)->comment('Monthly basic salary in RM');
            $table->decimal('fixed_allowance', 12, 2)->default(0)->comment('Fixed monthly allowances');
            $table->enum('salary_currency', ['MYR', 'SGD', 'USD'])->default('MYR');
            
            // Bank details for salary disbursement
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
