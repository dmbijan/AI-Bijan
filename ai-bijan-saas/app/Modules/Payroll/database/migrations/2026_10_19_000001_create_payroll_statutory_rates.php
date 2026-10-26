<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create statutory_rates table.
     * 
     * Stores Malaysian statutory contribution rates that can be updated
     * when government announces rate changes.
     * 
     * Source references:
     * - EPF (KWSP): Employees Provident Fund Act 1991
     *   Employee: 11% for all salary levels
     *   Employer: 13% (salary <= RM5,000) or 12% (salary > RM5,000)
     * - SOCSO (PERKESO): Social Security Act 1969
     *   Employee & Employer: Tier-based contribution
     * - EIS (SIP): Employment Insurance System
     *   Employee: 0.2% of gross salary (max RM100/year)
     * - HRDF: Human Resource Development Fund Act 1991
     *   Employer: 1% of gross salary (NO cap — corrected per audit)
     * - PCB: Monthly Tax Deduction per LHDN mPTG tables
     */
    public function up(): void
    {
        Schema::create('statutory_rates', function (Blueprint $table) {
            $table->id();
            // NULL = kadar lalai global yang terpakai kepada semua tenant
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('year')->index(); // e.g., '2026'
            $table->string('type')->nullable()->index(); // 'epf_employee', 'epf_employer', 'socso', 'eis', 'hrdf', 'pcb'
            
            // EPF rates (corrected per audit A-02)
            $table->decimal('epf_employee_rate', 5, 2)->default(11.00)->comment('11% for ALL salary levels');
            $table->decimal('epf_employer_base_rate', 5, 2)->default(13.00)->comment('13% for salary <= RM5,000');
            $table->decimal('epf_employer_reduced_rate', 5, 2)->default(12.00)->comment('12% for salary > RM5,000');
            $table->integer('epf_salary_threshold')->default(5000)->comment('Threshold for reduced employer rate');
            
            // SOCSO rates (tier-based, stored as max annual contribution)
            $table->decimal('socso_max_annual_ee', 10, 2)->default(0)->comment('Max annual SOCSO employee contribution');
            $table->decimal('socso_max_annual_er', 10, 2)->default(0)->comment('Max annual SOCSO employer contribution');
            
            // EIS / SIP rates
            $table->decimal('eis_rate', 5, 2)->default(0.20)->comment('0.2% employment insurance premium');
            $table->decimal('eis_max_annual', 10, 2)->default(100.00)->comment('Maximum RM100 per year');
            
            // HRDF Levy (CORRECTED: 1% NO CAP — was incorrectly 0.5% with RM100 cap)
            $table->decimal('hrdf_rate', 5, 2)->default(1.00)->comment('1% of gross salary — NO cap per HRDF Act 1991');
            
            // PCB (handled via lookup table, not formula)
            $table->boolean('pcb_use_mptg_table')->default(true);
            
            // Metadata
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('source_reference')->nullable()->comment('e.g., "Gazette date", "Ministry notice"');
            $table->timestamps();
            
            $table->unique(['year', 'type'], 'statutory_year_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statutory_rates');
    }
};
