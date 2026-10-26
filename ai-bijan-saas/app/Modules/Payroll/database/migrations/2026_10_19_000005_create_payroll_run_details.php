<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create payroll_run_details table.
     * 
     * Stores individual employee salary breakdown for each payroll run.
     * This is the core table that holds all calculated values per employee.
     */
    public function up(): void
    {
        Schema::create('payroll_run_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Earnings
            $table->decimal('basic_salary', 12, 2)->comment('Basic salary for this period');
            $table->decimal('fixed_allowance', 12, 2)->default(0)->comment('Fixed allowances');
            $table->decimal('overtime_pay', 12, 2)->default(0)->comment('Calculated from overtime_records');
            $table->decimal('other_earnings', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->comment('Total earnings before deductions');
            
            // Statutory deductions (Employee portion)
            $table->decimal('epf_ee', 12, 2)->default(0)->comment('EPF/KWSP Employee contribution');
            $table->decimal('socso_ee', 12, 2)->default(0)->comment('SOCSO/PERKESO Employee contribution');
            $table->decimal('eis_ee', 12, 2)->default(0)->comment('EIS/SIP Employee contribution');
            $table->decimal('pcb', 12, 2)->default(0)->comment('PCB tax deduction');
            $table->decimal('other_deductions', 12, 2)->default(0);
            
            // Net pay
            $table->decimal('net_pay', 12, 2)->comment('Take-home salary');
            
            // Employer costs (not deducted from employee)
            $table->decimal('epf_er', 12, 2)->default(0)->comment('EPF/KWSP Employer contribution');
            $table->decimal('socso_er', 12, 2)->default(0)->comment('SOCSO/PERKESO Employer contribution');
            $table->decimal('eis_er', 12, 2)->default(0)->comment('EIS/SIP Employer contribution');
            $table->decimal('hrdf', 12, 2)->default(0)->comment('HRDF Levy — 1% NO CAP');
            
            // Calculation metadata
            $table->string('statutory_year')->nullable()->comment('Which year rates were used');
            $table->json('calculation_notes')->nullable()->comment('Debug info for audit trail');
            
            $table->timestamps();
            
            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_details');
    }
};
