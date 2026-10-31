<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Services\KedaiPaymentService;
use App\Modules\POS\Models\POSSale;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Fasa 1.4 — bayaran pelanggan kedai melalui akaun CHIP kedai sendiri. */
class KedaiPaymentController extends Controller
{
    public function __construct(private KedaiPaymentService $svc) {}

    public function show(): JsonResponse
    {
        $g = $this->svc->gateway();

        return response()->json(['data' => $g ? [
            'provider' => 'chip', 'brand_id' => $g->brand_id, 'kunci' => $g->kunciBertopeng(),
            'disahkan' => optional($g->verified_at)->toIso8601String(), 'aktif' => $g->is_active,
        ] : null]);
    }

    public function update(Request $r): JsonResponse
    {
        $d = $r->validate(['brand_id' => 'required|string|max:100', 'api_key' => 'required|string|min:20|max:500']);
        try {
            $g = $this->svc->simpanTetapan(trim($d['brand_id']), trim($d['api_key']));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        app(AuditLogger::class)->log('payments.chip_connected', $g, null, ['brand_id' => $g->brand_id, 'kunci' => $g->kunciBertopeng()]);

        return $this->show();
    }

    public function pautanJualan(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['email' => 'nullable|email|max:190']);
        $sale = POSSale::find($id);
        if (! $sale) {
            return response()->json(['message' => 'Jualan tidak dijumpai.'], 404);
        }
        try {
            $link = $this->svc->pautanJualan($sale, $d['email'] ?? null, $r->user()->email);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => ['id' => $link->id, 'url' => $link->url, 'jumlah' => $link->amount, 'status' => $link->status]], 201);
    }

    /** Callback CHIP (awam). Sentiasa 200 supaya CHIP tidak mengulang tanpa had; keputusan dalam badan. */
    public function callback(Request $r, string $ulid): JsonResponse
    {
        [$ok, $mesej] = $this->svc->callback($ulid, $r->getContent(), $r->header('X-Signature'));

        return response()->json(['ok' => $ok, 'mesej' => $mesej]);
    }
}
