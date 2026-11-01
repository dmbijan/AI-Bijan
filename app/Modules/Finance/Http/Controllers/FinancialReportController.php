<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    protected $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get Profit & Loss statement.
     * GET /api/finance/reports/profit-loss?date_from=&date_to=
     */
    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'org_unit_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('org_units', 'id')->where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())],
        ]);

        // P&L per cawangan/jabatan: jurnal yang ditanda unit itu dan unit di bawahnya.
        // Ahli berskop cawangan hanya boleh lihat unit dalam skopnya (lalai: semua unit skopnya).
        $ds = app(\App\Support\Tenancy\DataScope::class);
        $units = null;
        if (! empty($validated['org_unit_id'])) {
            $ds->authorize('units', (int) $validated['org_unit_id'], 'Unit ini di luar skop anda.');
            $units = app(\App\Services\AccessService::class)->descendants((int) $validated['org_unit_id'], true);
        }
        if ($ds->restricted()) {
            $units = $units === null ? $ds->units() : array_values(array_intersect($units, $ds->units()));
        }

        $report = $this->reportService->generateProfitLoss($validated['date_from'], $validated['date_to'], $units);
        $report['scope'] = $units === null ? ['type' => 'company'] : ['type' => 'units', 'unit_ids' => $units,
            'note' => 'Hanya jurnal yang ditanda unit ini. Jurnal ibu pejabat tanpa unit (cth. sewa pusat) tidak termasuk.'];

        return response()->json($report);
    }

    /**
     * Get Balance Sheet.
     * GET /api/finance/reports/balance-sheet?as_of_date=
     */
    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
        ]);
        
        $report = $this->reportService->generateBalanceSheet($validated['as_of_date']);
        
        return response()->json($report);
    }

    /**
     * Get Trial Balance.
     * GET /api/finance/reports/trial-balance?date_from=&date_to=
     */
    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);
        
        $trialBalance = $this->reportService->getTrialBalance(
            $validated['date_from'],
            $validated['date_to']
        );
        
        return response()->json($trialBalance);
    }

    /**
     * Get General Ledger for an account.
     * GET /api/finance/reports/general-ledger/{accountId}?date_from=&date_to=
     */
    public function generalLedger($accountId, Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);
        
        $ledger = $this->reportService->getGeneralLedger(
            $accountId,
            $validated['date_from'],
            $validated['date_to']
        );
        
        return response()->json($ledger);
    }
}
