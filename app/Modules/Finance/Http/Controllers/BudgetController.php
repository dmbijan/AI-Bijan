<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Services\BudgetService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** FIN-005 Bajet vs sebenar. */
class BudgetController extends Controller
{
    public function __construct(private BudgetService $svc) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => Budget::orderByDesc('fiscal_year')->orderBy('name')->get()]);
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'fiscal_year' => 'required|integer|min:2000|max:2100']);
        if (Budget::where('fiscal_year', $d['fiscal_year'])->where('name', $d['name'])->exists()) {
            return response()->json(['message' => 'Bajet dengan nama ini sudah ada untuk tahun tersebut.'], 422);
        }
        $b = Budget::create($d);
        app(AuditLogger::class)->log('finance.budget_created', $b, null, $d);

        return response()->json(['data' => $b], 201);
    }

    public function setLines(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'lines' => 'required|array|min:1|max:500',
            'lines.*.account_id' => 'required|integer',
            'lines.*.months' => 'nullable|array|size:12',
            'lines.*.months.*' => 'numeric|min:0|max:1000000000',
            'lines.*.annual' => 'nullable|numeric|min:0|max:1000000000|required_without:lines.*.months',
        ]);

        $b = Budget::findOrFail($id);

        return $this->cuba(fn () => $this->svc->tetapkanBaris($b, $d['lines']), 'finance.budget_lines_set');
    }

    public function approve(Request $r, int $id): JsonResponse
    {
        $b = Budget::findOrFail($id);

        return $this->cuba(fn () => $this->svc->lulus($b, $r->user()->id), 'finance.budget_approved');
    }

    public function report(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['from_month' => 'nullable|integer|min:1|max:12', 'to_month' => 'nullable|integer|min:1|max:12']);
        $b = Budget::findOrFail($id);
        try {
            return response()->json(['data' => $this->svc->laporan($b, (int) ($d['from_month'] ?? 1), (int) ($d['to_month'] ?? 12))]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function cuba(callable $f, string $peristiwa): JsonResponse
    {
        try {
            $b = $f();
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        app(AuditLogger::class)->log($peristiwa, $b, null, ['status' => $b->status]);

        return response()->json(['data' => $b->load('lines')]);
    }
}
