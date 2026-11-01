<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\TreasuryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** FIN-006 Treasury. */
class TreasuryController extends Controller
{
    public function __construct(private TreasuryService $svc) {}

    public function position(): JsonResponse
    {
        return response()->json(['data' => $this->svc->kedudukan()]);
    }

    public function forecast(Request $r): JsonResponse
    {
        $d = $r->validate(['days' => 'nullable|integer|min:1|max:180', 'include_overdue' => 'nullable|boolean', 'threshold' => 'nullable|numeric|min:0']);

        return response()->json(['data' => $this->svc->ramalan((int) ($d['days'] ?? 30), (bool) ($d['include_overdue'] ?? false), (float) ($d['threshold'] ?? 0))]);
    }
}
