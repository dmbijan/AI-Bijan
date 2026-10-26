<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create payroll_runs table.
     * 
     * A payroll run represents one complete payroll cycle for a tenant.
     * It processes all employees and calculates gross pay, statutory deductions,
     * and net pay for each employee.
     */
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Run identification
            $table->string('name')->comment('e.g., "Payroll October 2026"');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date')->nullable()->comment('Actual salary payment date');
            
            // Status
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'processed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Summary totals (for quick reference)
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_epf_ee', 15, 2)->default(0)->comment('Total EPF Employee contribution');
            $table->decimal('total_socso_ee', 15, 2)->default(0)->comment('Total SOCSO Employee contribution');
            $table->decimal('total_eis_ee', 15, 2)->default(0)->comment('Total EIS Employee contribution');
            $table->decimal('total_pcb', 15, 2)->default(0)->comment('Total PCB tax deduction');
            $table->decimal('total_other_deductions', 15, 2)->default(0);
            $table->decimal('total_net_pay', 15, 2)->default(0)->comment('Total net salary to be paid');
            $table->decimal('total_epf_er', 15, 2)->default(0)->comment('Total EPF Employer contribution');
            $table->decimal('total_socso_er', 15, 2)->default(0)->comment('Total SOCSO Employer contribution');
            $table->decimal('total_eis_er', 15, 2)->default(0)->comment('Total EIS Employer contribution');
            $table->decimal('total_hrdf', 15, 2)->default(0)->comment('Total HRDF Levy');
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
