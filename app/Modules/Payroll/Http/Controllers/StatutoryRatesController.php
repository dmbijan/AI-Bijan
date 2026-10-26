<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\StatutoryRate;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * B-Payroll Statutory Rates Controller
 * 
 * Manages Malaysian statutory contribution rates.
 * These rates can be updated when government announces changes.
 * 
 * Current CORRECT rates (per audit A-02):
 * - EPF Employee: 11% (ALL salary levels)
 * - EPF Employer: 13% (<=RM5K) / 12% (>RM5K)
 * - SOCSO: Tier-based per PERKESO table
 * - EIS: 0.2% max RM100/year
 * - HRDF: 1% NO CAP
 */
class StatutoryRatesController extends Controller
{
    /**
     * List all statutory rate versions.
     */
    public function index(Request $request)
    {
        $query = StatutoryRate::where('tenant_id', app(TenantContext::class)->id())
            ->orderBy('year', 'desc');

        return response()->json($query->paginate(20));
    }

    /**
     * Set current year statutory rates.
     * 
     * POST /api/v1/payroll/statutory-rates
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|digits:4',
            
            // EPF rates
            'epf_employee_rate' => 'nullable|numeric|min:0|max:20',
            'epf_employer_base_rate' => 'nullable|numeric|min:0|max:20',
            'epf_employer_reduced_rate' => 'nullable|numeric|min:0|max:20',
            'epf_salary_threshold' => 'nullable|integer|min:0',
            
            // SOCSO rates
            'socso_max_annual_ee' => 'nullable|numeric|min:0',
            'socso_max_annual_er' => 'nullable|numeric|min:0',
            
            // EIS rates
            'eis_rate' => 'nullable|numeric|min:0|max:5',
            'eis_max_annual' => 'nullable|numeric|min:0',
            
            // HRDF Levy — CORRECTED: 1% NO CAP
            'hrdf_rate' => 'nullable|numeric|min:0|max:5',
            
            // PCB settings
            'pcb_use_mptg_table' => 'nullable|boolean',
            
            // Metadata
            'effective_from' => 'nullable|date',
            'source_reference' => 'nullable|string|max:255',
        ]);

        // Deactivate previous active rates for this tenant/year
        StatutoryRate::where('tenant_id', app(TenantContext::class)->id())
            ->where('year', $validated['year'])
            ->update(['is_active' => false]);

        $rate = StatutoryRate::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'year' => $validated['year'],
            'epf_employee_rate' => $validated['epf_employee_rate'] ?? 11.00,
            'epf_employer_base_rate' => $validated['epf_employer_base_rate'] ?? 13.00,
            'epf_employer_reduced_rate' => $validated['epf_employer_reduced_rate'] ?? 12.00,
            'epf_salary_threshold' => $validated['epf_salary_threshold'] ?? 5000,
            'socso_max_annual_ee' => $validated['socso_max_annual_ee'] ?? 0,
            'socso_max_annual_er' => $validated['socso_max_annual_er'] ?? 0,
            'eis_rate' => $validated['eis_rate'] ?? 0.20,
            'eis_max_annual' => $validated['eis_max_annual'] ?? 100.00,
            'hrdf_rate' => $validated['hrdf_rate'] ?? 1.00,
            'pcb_use_mptg_table' => $validated['pcb_use_mptg_table'] ?? true,
            'effective_from' => $validated['effective_from'] ?? now(),
            'source_reference' => $validated['source_reference'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Statutory rates saved',
            'data' => $rate,
        ], 201);
    }

    /**
     * Get current active rates.
     */
    public function current(Request $request)
    {
        $rate = StatutoryRate::where('tenant_id', app(TenantContext::class)->id())
            ->where('is_active', true)
            ->first();

        if (!$rate) {
            // Return defaults (corrected per audit A-02)
            return response()->json([
                'year' => date('Y'),
                'epf_employee_rate' => 11.00,
                'epf_employer_base_rate' => 13.00,
                'epf_employer_reduced_rate' => 12.00,
                'epf_salary_threshold' => 5000,
                'socso_max_annual_ee' => 0,
                'socso_max_annual_er' => 0,
                'eis_rate' => 0.20,
                'eis_max_annual' => 100.00,
                'hrdf_rate' => 1.00,
                'pcb_use_mptg_table' => true,
                'note' => 'Default Malaysian statutory rates (corrected per audit A-02)',
            ]);
        }

        return response()->json($rate);
    }
}
