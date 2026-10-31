<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSDineArea;
use App\Modules\POS\Models\POSDineOrder;
use App\Modules\POS\Models\POSDineTable;
use App\Modules\POS\Services\DineOrderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Dine-in: zon, meja dan pesanan (BAB 88 / T-76).
 *
 * Semua ID disahkan milik tenant semasa melalui `Rule::exists(... where tenant_id)`
 * — elak IDOR melalui parameter URL (pelajaran audit P0-4).
 */
class DineInController extends Controller
{
    public function __construct(private DineOrderService $dineService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    // ── Zon & meja ────────────────────────────────────────────────

    public function areas()
    {
        return response()->json([
            'data' => POSDineArea::where('is_active', true)->with('tables')->orderBy('name')->get(),
        ]);
    }

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:30',
        ]);

        $area = POSDineArea::create($data + ['tenant_id' => $this->tenantId(), 'is_active' => true]);

        return response()->json(['data' => $area], 201);
    }

    public function storeTable(Request $request)
    {
        $data = $request->validate([
            'dine_area_id' => [
                'required',
                Rule::exists('pos_dine_areas', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'name' => ['required', 'string', 'max:60', Rule::unique('pos_dine_tables', 'name')->where('tenant_id', $this->tenantId())],
            'code' => 'nullable|string|max:30',
            'seats' => 'nullable|integer|min:1|max:50',
        ]);

        $meja = POSDineTable::create($data + ['tenant_id' => $this->tenantId(), 'status' => 'free', 'is_active' => true]);

        return response()->json(['data' => $meja], 201);
    }

    /** Senarai meja (dengan token QR) untuk skrin staf. */
    public function tables()
    {
        return response()->json(['data' => POSDineTable::with('area:id,name')
            ->withCount(['orders as open_orders' => fn ($q) => $q->whereIn('status', [POSDineOrder::STATUS_SUBMITTED, POSDineOrder::STATUS_ACCEPTED])])
            ->orderBy('name')->get()]);
    }

    /** Jana token QR baharu (QR lama terus tak boleh dipakai). */
    public function regenerateQr(int $id)
    {
        $meja = POSDineTable::findOrFail($id);
        $meja->update(['qr_token' => POSDineTable::tokenBaharu()]);

        return response()->json(['data' => $meja]);
    }

    // ── Pesanan ───────────────────────────────────────────────────

    /** Senarai pesanan (tapis status). */
    public function orders(Request $request)
    {
        $query = POSDineOrder::with(['items', 'table'])
            ->orderByDesc('submitted_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->limit(100)->get());
    }

    /** Cipta pesanan dine-in. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'dine_table_id' => [
                'required',
                Rule::exists('pos_dine_tables', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'customer_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => [
                'nullable',
                Rule::exists('items', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'items.*.item_name' => 'required_without:items.*.item_id|string|max:150',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'items.*.modifier_options' => 'nullable|array',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $meja = POSDineTable::findOrFail($data['dine_table_id']);
        $order = $this->dineService->cipta($this->tenantId(), $meja, $data);

        return response()->json(['data' => $order], 201);
    }

    /** Terima pesanan → jualan draf. */
    public function accept(Request $request, int $id)
    {
        $order = POSDineOrder::findOrFail($id);

        $sale = $this->dineService->terima(
            $order,
            $this->tenantId(),
            (int) $request->user()->id,
            $request->integer('register_id') ?: null
        );

        return response()->json([
            'message' => 'Pesanan diterima.',
            'sale_id' => $sale->id,
            'sale_no' => $sale->sale_no,
            'total_amount' => (float) $sale->total_amount,
            'service_charge_amount' => (float) $sale->service_charge_amount,
        ]);
    }

    /** Tolak pesanan. */
    public function reject(Request $request, int $id)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);

        $order = $this->dineService->tolak(POSDineOrder::findOrFail($id), $data['reason'] ?? null);

        return response()->json(['message' => 'Pesanan ditolak.', 'status' => $order->status]);
    }
}
