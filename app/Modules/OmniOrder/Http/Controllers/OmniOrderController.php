<?php

namespace App\Modules\OmniOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OmniOrder\Models\OmniChannelConnection;
use App\Modules\OmniOrder\Models\OmniCodPayout;
use App\Modules\OmniOrder\Models\OmniCourier;
use App\Modules\OmniOrder\Models\OmniOrder;
use App\Modules\OmniOrder\Services\OmniOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OmniOrderController extends Controller
{
    public function __construct(private OmniOrderService $omni) {}

    /**
     * Senarai pesanan (satu baris gilir).
     */
    public function index(Request $request): JsonResponse
    {
        $query = OmniOrder::query()->orderByDesc('created_at');

        foreach (['status', 'channel', 'payment_method', 'courier'] as $medan) {
            if ($request->filled($medan)) {
                $query->where($medan, $request->input($medan));
            }
        }

        if ($request->boolean('only_duplicate')) {
            $query->where('is_duplicate', true);
        }

        if ($request->boolean('only_risk')) {
            $query->where('is_high_risk', true);
        }

        if ($request->boolean('only_high_value')) {
            $query->where('is_high_value', true);
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(min(500, (int) $request->input('limit', 100)))->get(),
            'meta' => ['saluran' => OmniOrder::senaraiSaluran()],
        ]);
    }

    /**
     * Terima pesanan baharu.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'channel' => ['nullable', 'string', 'max:40'],
            'external_order_id' => ['nullable', 'string', 'max:120'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'customer_state' => ['nullable', 'string', 'max:100'],
            'customer_postcode' => ['nullable', 'string', 'max:10'],
            'total_sen' => ['required', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:cod,bank,prepaid'],
            'payment_status' => ['nullable', 'string', 'max:30'],
            'courier' => ['nullable', 'string', 'max:40'],
            'items' => ['nullable', 'array'],
            'payload' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $pesanan = $this->omni->terimaPesanan($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pesanan diterima.',
            'data' => $pesanan,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => OmniOrder::with([])->findOrFail($id),
        ]);
    }

    /**
     * Kelulusan pukal.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'skip_duplicates' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $hasil = $this->omni->lulusPukal(
            $data['ids'],
            (bool) ($data['skip_duplicates'] ?? true)
        );

        return response()->json([
            'success' => true,
            'message' => 'Kelulusan pukal selesai.',
            'data' => $hasil,
        ]);
    }

    /**
     * Cetak AWB.
     */
    public function printAwb(int $id): JsonResponse
    {
        $pesanan = OmniOrder::findOrFail($id);
        $hasil = $this->omni->cetakAwb($pesanan);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => [
                'awb_url' => $hasil['awb_url'],
                'tracking_no' => $hasil['tracking_no'],
            ],
        ], $hasil['success'] ? 200 : 422);
    }

    /**
     * Push ke kurier.
     */
    public function pushCourier(int $id): JsonResponse
    {
        $pesanan = OmniOrder::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan dihantar ke kurier.',
            'data' => $this->omni->pushKeKurier($pesanan),
        ]);
    }

    /**
     * Pulihkan penghantaran gagal.
     */
    public function recoverDelivery(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $pesanan = OmniOrder::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->omni->pulihkanPenghantaran($pesanan, $v->validated()['reason']),
        ]);
    }

    /**
     * Hantar pesanan ke UnifiedOrder (modul Orders).
     */
    public function sendToUnifiedOrder(int $id): JsonResponse
    {
        $pesanan = OmniOrder::findOrFail($id);
        $hasil = $this->omni->hantarKeUnifiedOrder($pesanan);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => ['unified_order_id' => $hasil['order_id']],
        ], $hasil['success'] ? 200 : 422);
    }

    /**
     * Ringkasan papan pemuka.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->omni->ringkasan(),
        ]);
    }

    /**
     * COD payouts.
     */
    public function codPayouts(Request $request): JsonResponse
    {
        $query = OmniCodPayout::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('overdue')) {
            $query->whereNull('received_at')
                ->whereNotNull('expected_at')
                ->where('expected_at', '<', now());
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    /**
     * Rekod payout COD diterima.
     */
    public function recordCodPayout(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'received_sen' => ['required', 'integer', 'min:0'],
            'payout_reference' => ['nullable', 'string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $payout = OmniCodPayout::findOrFail($id);
        $data = $v->validated();

        $payout->update([
            'received_sen' => $data['received_sen'],
            'payout_reference' => $data['payout_reference'] ?? null,
            'status' => $data['received_sen'] >= $payout->expected_sen
                ? OmniCodPayout::STATUS_RECEIVED
                : OmniCodPayout::STATUS_DISPUTED,
            'received_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout COD direkod.',
            'data' => $payout->fresh(),
        ]);
    }

    /**
     * Kurier.
     */
    public function couriers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => OmniCourier::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Saluran jualan.
     */
    public function channels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => OmniChannelConnection::query()->orderBy('channel')->get(),
            'meta' => ['disokong' => OmniOrder::senaraiSaluran()],
        ]);
    }

    /**
     * Sambung saluran.
     */
    public function connectChannel(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'channel' => ['required', 'string', 'max:40'],
            'name' => ['nullable', 'string', 'max:120'],
            'shop_id' => ['nullable', 'string', 'max:120'],
            'credentials' => ['nullable', 'array'],
            'auto_approve' => ['nullable', 'boolean'],
            'sync_interval_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        $sambungan = OmniChannelConnection::updateOrCreate(
            ['channel' => $data['channel'], 'shop_id' => $data['shop_id'] ?? null],
            array_merge($data, [
                'status' => OmniChannelConnection::STATUS_CONNECTED,
                'last_synced_at' => now(),
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Saluran disambungkan.',
            'data' => $sambungan,
        ], 201);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
