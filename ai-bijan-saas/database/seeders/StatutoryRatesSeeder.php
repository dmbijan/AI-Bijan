<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Seed Malaysian statutory rates with CORRECT values per audit A-02.
 * 
 * These rates are based on official government sources:
 * - EPF (KWSP): Employees Provident Fund Act 1991
 * - SOCSO (PERKESO): Social Security Act 1969
 * - EIS (SIP): Employment Insurance System
 * - HRDF: Human Resource Development Fund Act 1991
 */
class StatutoryRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Malaysian statutory rates for 2026
        // These are applied to ALL tenants by default
        
        $year = date('Y');
        
        // Kadar global: `type` berkongsi unique key dengan kadar per-tenant, jadi
        // gunakan query builder (tiada tenant scope) dan kemas kini jika sudah ada.
        DB::table('statutory_rates')->updateOrInsert(
            ['tenant_id' => null, 'year' => $year, 'type' => 'default'],
            [
            'year' => $year,
            
            // EPF (KWSP) - Employees Provident Fund Act 1991
            // CORRECTED: Employee pays 11% for ALL salary levels
            'epf_employee_rate' => 11.00,
            
            // CORRECTED: Employer pays 13% for <=RM5K, 12% for >RM5K
            'epf_employer_base_rate' => 13.00,      // <= RM5,000
            'epf_employer_reduced_rate' => 12.00,   // > RM5,000
            'epf_salary_threshold' => 5000,
            
            // SOCSO (PERKESO) - Social Security Act 1969
            // Tier-based calculation (placeholder values)
            'socso_max_annual_ee' => 1200.00,
            'socso_max_annual_er' => 1200.00,
            
            // EIS (SIP) - Employment Insurance System
            'eis_rate' => 0.20,       // 0.2% of gross salary
            'eis_max_annual' => 100.00, // Maximum RM100/year
            
            // HRDF Levy - Human Resource Development Fund Act 1991
            // CORRECTED: 1% of gross salary — NO CAP
            // OLD (WRONG): 0.5% with RM100/month cap
            'hrdf_rate' => 1.00,      // 1% — NO CAP
            
            // PCB settings
            'pcb_use_mptg_table' => true,
            
            // Metadata
            'effective_from' => now(),
            'source_reference' => 'Malaysian Government Official Rates ' . $year,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Statutory rates seeded successfully for year ' . $year);
        $this->command->info('   EPF Employee: 11% (all salary levels)');
        $this->command->info('   EPF Employer: 13% (<=RM5K) / 12% (>RM5K)');
        $this->command->info('   SOCSO: Tier-based (max ~RM1,200/year)');
        $this->command->info('   EIS: 0.2% (max RM100/year)');
        $this->command->info('   HRDF: 1% (NO CAP — corrected per audit A-02)');
    }
}
