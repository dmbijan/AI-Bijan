<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\TaxCode;
use App\Modules\Finance\Services\TaxService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FIN-010 Cukai SST. */
class TaxController extends Controller
{
    public function __construct(private TaxService $svc) {}

    public function codes(): JsonResponse
    {
        $this->svc->pastikanLalai();

        return response()->json(['data' => TaxCode::orderBy('type')->orderBy('code')->orderBy('effective_from')->get()]);
    }

    public function storeCode(Request $r): JsonResponse
    {
        $this->svc->pastikanLalai();
        $d = $r->validate([
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'name' => 'required|string|max:120',
            'type' => 'required|in:sales,service,exempt,none',
            'rate' => 'required|numeric|min:0|max:100',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);
        if (in_array($d['type'], ['exempt', 'none'], true) && (float) $d['rate'] !== 0.0) {
            return response()->json(['message' => 'Kod dikecualikan/luar skop mesti 0%.'], 422);
        }
        // Tempoh baharu bagi kod sedia ada: tutup tempoh terbuka sebelumnya sehari sebelum.
        $terbuka = TaxCode::where('code', $d['code'])->whereNull('effective_to')->where('effective_from', '<', $d['effective_from'])->first();
        if (TaxCode::where('code', $d['code'])->where('effective_from', $d['effective_from'])->exists()) {
            return response()->json(['message' => 'Kod ini sudah ada tempoh bermula tarikh yang sama.'], 422);
        }
        $terbuka?->update(['effective_to' => \Carbon\Carbon::parse($d['effective_from'])->subDay()->toDateString()]);
        $k = TaxCode::create($d);
        app(AuditLogger::class)->log('finance.tax_code_saved', $k, null, $k->only(['code', 'rate', 'effective_from', 'effective_to']));

        return response()->json(['data' => $k], 201);
    }

    public function updateCode(Request $r, int $id): JsonResponse
    {
        $k = TaxCode::findOrFail($id);
        $d = $r->validate(['name' => 'sometimes|string|max:120', 'is_active' => 'sometimes|boolean', 'effective_to' => 'sometimes|nullable|date']);
        $lama = $k->only(array_keys($d));
        $k->update($d);
        app(AuditLogger::class)->log('finance.tax_code_updated', $k, $lama, $k->only(array_keys($d)));

        return response()->json(['data' => $k]);
    }

    public function settings(): JsonResponse
    {
        return response()->json(['data' => $this->svc->tetapan()]);
    }

    public function updateSettings(Request $r): JsonResponse
    {
        $d = $r->validate([
            'sst_registration_no' => 'nullable|string|max:40',
            'sst_registered_from' => 'nullable|date',
            'period_start_month' => 'nullable|integer|min:1|max:12',
            'default_tax_code' => ['nullable', 'string', 'max:10', Rule::exists('tax_codes', 'code')->where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())],
        ]);
        $t = $this->svc->tetapan();
        $lama = $t->only(array_keys($d));
        $t->update($d);
        app(AuditLogger::class)->log('finance.tax_settings_updated', $t, $lama, $t->only(array_keys($d)));

        return response()->json(['data' => $t]);
    }

    public function calculate(Request $r): JsonResponse
    {
        $d = $r->validate(['amount' => 'required|numeric|min:0', 'code' => 'required|string|max:10', 'date' => 'nullable|date', 'inclusive' => 'nullable|boolean']);
        try {
            return response()->json(['data' => $this->svc->kira((float) $d['amount'], $d['code'], $d['date'] ?? null, (bool) ($d['inclusive'] ?? false))]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function period(Request $r): JsonResponse
    {
        $r->validate(['date' => 'nullable|date']);

        return response()->json(['data' => $this->svc->tempoh($r->input('date'))]);
    }

    public function sst02(Request $r): JsonResponse
    {
        $r->validate(['from' => 'nullable|date|required_with:to', 'to' => 'nullable|date|after_or_equal:from', 'date' => 'nullable|date']);
        $p = $r->filled('from') ? ['from' => $r->input('from'), 'to' => $r->input('to')] : $this->svc->tempoh($r->input('date'));

        return response()->json(['data' => $this->svc->drafSst02($p['from'], $p['to'])]);
    }
}
