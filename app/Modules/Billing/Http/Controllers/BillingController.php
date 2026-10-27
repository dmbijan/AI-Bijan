<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Models\BillingSubscription;
use App\Modules\Billing\Models\Reseller;
use App\Modules\Billing\Models\ResellerClick;
use App\Modules\Billing\Models\ResellerCommission;
use App\Modules\Billing\Models\ResellerReferral;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\ChipWebhookService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint langganan + webhook CHIP + portal reseller.
 */
class BillingController extends Controller
{
    public function __construct(
        private BillingService $billing,
        private ChipWebhookService $webhook,
    ) {}

    private function tenantId(): int
    {
        // ⚠️ MESTI dari konteks, BUKAN header klien.
        return (int) app(TenantContext::class)->id();
    }

    // ===============================================================
    // LANGGANAN (tenant-scoped)
    // ===============================================================

    /** GET /api/v1/billing/subscription */
    public function langgananSemasa(): JsonResponse
    {
        $langganan = BillingSubscription::where('tenant_id', $this->tenantId())
            ->latest('id')
            ->first();

        return response()->json(['success' => true, 'data' => $langganan]);
    }

    /**
     * GET /api/v1/billing/packages
     * Senarai pakej token AI — WAJIB dipilih semasa langganan.
     */
    public function pakej(): JsonResponse
    {
        $pakej = collect($this->billing->pakejToken())
            ->map(fn ($p, $kod) => [
                'code' => $kod,
                'name' => $p['name'],
                'tokens' => $p['tokens'],
                'price' => number_format((float) $p['price'], 2, '.', ''),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $pakej,
            'nota' => 'Pakej token WAJIB dipilih semasa langganan — AI tidak berfungsi tanpa token.',
        ]);
    }

    /**
     * POST /api/v1/billing/subscribe
     * Cipta invois CHIP (langganan + token) dan pulangkan URL checkout.
     */
    public function langgan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_code' => ['required', 'string', 'in:starter,growth,enterprise'],
            // ⚠️ WAJIB — bukan 'nullable'. Tanpa token, AI mati.
            'token_package' => ['required', 'string'],
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();

        $cas = $this->billing->ciptaInvoisLangganan(
            $this->tenantId(),
            $data['plan_code'],
            $data['token_package'],
            [
                'email' => $data['email'],
                'name' => $data['name'] ?? $user?->name,
                'phone' => $data['phone'] ?? null,
            ]
        );

        $pecahan = $cas->chip_payload['pecahan'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'charge_id' => $cas->id,
                'checkout_url' => $cas->chip_checkout_url,
                'amount' => (string) $cas->amount,
                'pecahan' => [
                    'langganan' => number_format((float) ($pecahan['langganan'] ?? 0), 2, '.', ''),
                    'token' => number_format((float) ($pecahan['token'] ?? 0), 2, '.', ''),
                    'pakej_token' => $pecahan['pakej_token'] ?? $data['token_package'],
                ],
                'period' => $cas->period_key,
                'status' => $cas->status,
            ],
        ], 201);
    }

    // ===============================================================
    // WEBHOOK CHIP (awam — pengesahan melalui tandatangan)
    // ===============================================================

    /**
     * POST /api/v1/webhooks/chip
     *
     * ⚠️ TIADA auth:sanctum dan TIADA tenant middleware — CHIP tidak
     * menghantar token kita. Keselamatan terletak pada tandatangan RSA.
     */
    public function webhookChip(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Signature-Timestamp');

        $hasil = $this->webhook->proses($raw, $signature, $timestamp);

        // Sentiasa 200 supaya CHIP tidak retry berlebihan.
        // Status sebenar ada dalam badan.
        return response()->json([
            'received' => true,
            'processed' => $hasil['ok'],
            'message' => $hasil['mesej'],
        ]);
    }

    // ===============================================================
    // RESELLER (platform-level untuk reseller sendiri)
    // ===============================================================

    /** GET /api/v1/reseller/me */
    public function resellerSaya(Request $request): JsonResponse
    {
        $reseller = Reseller::where('user_id', $request->user()->id)->first();

        // T-105: pagar eksplisit — laluan ini tiada `perm:` kerana data ialah
        // data platform per-reseller. Identiti MESTI wujud; 404 (bukan 403)
        // supaya kewujudan reseller lain tidak boleh disiasat.
        abort_if($request->user() === null, 403, 'Pengesahan diperlukan.');

        if ($reseller === null) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan reseller berdaftar.',
            ], 404);
        }

        $komisen = ResellerCommission::where('reseller_id', $reseller->id);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $reseller->code,
                'status' => $reseller->status,
                'commission_value' => (string) $reseller->commission_value,
                'jumlah_rujukan' => ResellerReferral::where('reseller_id', $reseller->id)->count(),
                'komisen_pending' => (string) (clone $komisen)->where('status', 'pending')->sum('commission_amount'),
                'komisen_approved' => (string) (clone $komisen)->where('status', 'approved')->sum('commission_amount'),
                'komisen_paid' => (string) (clone $komisen)->where('status', 'paid')->sum('commission_amount'),
                'komisen_reversed' => (string) (clone $komisen)->where('status', 'reversed')->sum('commission_amount'),
            ],
        ]);
    }

    /**
     * GET /api/v1/reseller/commissions
     * Senarai komisen — TANPA data peribadi pelanggan.
     */
    public function senaraiKomisen(Request $request): JsonResponse
    {
        // T-105: skop MESTI pengguna semasa sahaja — tiada `reseller_id` daripada
        // permintaan. `firstOrFail()` → 404 jika bukan reseller.
        abort_if($request->user() === null, 403, 'Pengesahan diperlukan.');

        $reseller = Reseller::where('user_id', $request->user()->id)->firstOrFail();

        $komisen = ResellerCommission::where('reseller_id', $reseller->id)
            ->orderByDesc('period')
            ->limit(100)
            ->get([
                'period', 'base_amount', 'commission_amount',
                'currency', 'status', 'earned_at', 'mature_at', 'paid_at',
            ]);

        return response()->json(['success' => true, 'data' => $komisen]);
    }

    /**
     * GET /r/{code}
     * Pautan rujukan awam — rekod klik, set cookie 90 hari, redirect ke daftar.
     */
    public function pautanRujukan(Request $request, string $code)
    {
        $reseller = Reseller::where('code', $code)->where('status', Reseller::STATUS_ACTIVE)->first();

        if ($reseller === null) {
            return redirect(config('billing.urls.tidak_sah', '/'));
        }

        ResellerClick::create([
            'reseller_id' => $reseller->id,
            'code' => $reseller->code,
            // ⚠️ IP di-hash — PDPA
            'ip_hash' => ResellerClick::hashIp($request->ip()),
            'user_agent_hash' => $request->userAgent()
                ? hash_hmac('sha256', $request->userAgent(), config('app.key', 'aibijan'))
                : null,
            'referrer' => mb_substr((string) $request->headers->get('referer'), 0, 500),
            'landing_url' => mb_substr($request->fullUrl(), 0, 500),
            'utm_source' => $request->query('utm_source'),
            'utm_campaign' => $request->query('utm_campaign'),
            'clicked_at' => now(),
        ]);

        $cookie = cookie(
            'aibijan_reseller',
            $reseller->code,
            (int) config('billing.reseller.cookie_days', 90) * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,          // httpOnly
            false,
            'lax'
        );

        return redirect(config('billing.urls.daftar', '/register'))->withCookie($cookie);
    }
}
