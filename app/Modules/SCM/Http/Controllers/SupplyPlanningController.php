<?php

namespace App\Modules\SCM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use App\Modules\SCM\Models\LandedCost;
use App\Modules\SCM\Services\SupplyPlanningService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kelompok 2 stok: SUP-011, SUP-013, SUP-014, SUP-015. */
class SupplyPlanningController extends Controller
{
    public function __construct(private SupplyPlanningService $svc) {}

    private function ex(string $table)
    {
        return Rule::exists($table, 'id')->where('tenant_id', app(TenantContext::class)->id());
    }

    public function forecast(Request $r): JsonResponse
    {
        $d = $r->validate(['days' => 'nullable|integer|min:1|max:90']);
        $fc = $this->svc->forecast($d['days'] ?? 30);
        $items = Item::whereIn('id', array_keys($fc))->get(['id', 'sku', 'name', 'uom'])->keyBy('id');
        $rows = collect($fc)->map(fn ($f) => $f + ['sku' => $items[$f['item_id']]->sku ?? null, 'name' => $items[$f['item_id']]->name ?? null, 'uom' => $items[$f['item_id']]->uom ?? null])
            ->sortByDesc('forecast')->values();

        return response()->json(['data' => $rows, 'meta' => ['history_days' => SupplyPlanningService::HISTORY_DAYS, 'method' => 'Purata harian 8 minggu (separuh berat 4 minggu terkini) × corak hari dalam minggu']]);
    }

    public function reorder(): JsonResponse
    {
        return response()->json(['data' => $this->svc->suggestions()]);
    }

    public function reorderSettings(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'reorder_level' => 'nullable|numeric|min:0', 'safety_stock' => 'nullable|numeric|min:0', 'reorder_qty' => 'nullable|numeric|min:0',
            'min_order_qty' => 'nullable|numeric|min:0', 'lead_time_days' => 'nullable|integer|min:1|max:365',
            'preferred_supplier_id' => ['nullable', 'integer', $this->ex('suppliers')],
        ]);
        $it = Item::findOrFail($id);
        $it->update($d);

        return response()->json(['data' => $it]);
    }

    public function createPos(Request $r): JsonResponse
    {
        $d = $r->validate([
            'lines' => 'required|array|min:1|max:200', 'lines.*.item_id' => ['required', 'integer', $this->ex('items')],
            'lines.*.qty' => 'nullable|integer|min:1', 'lines.*.supplier_id' => ['nullable', 'integer', $this->ex('suppliers')], 'lines.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        return response()->json(['data' => $this->svc->createDraftPos($d['lines'])], 201);
    }

    public function landedCosts(): JsonResponse
    {
        $lc = LandedCost::with('charges', 'lines')->latest('id')->limit(100)->get();
        $mv = StockMovement::whereIn('id', $lc->pluck('stock_movement_id'))->pluck('number', 'id');
        $items = Item::whereIn('id', $lc->pluck('lines')->flatten()->pluck('item_id'))->pluck('name', 'id');
        $receipts = StockMovement::where('type', 'receipt')->where('status', 'posted')->latest('id')->limit(50)->get(['id', 'number', 'reference', 'posted_at']);

        return response()->json(['data' => $lc->map(fn ($x) => $x->toArray() + ['movement_number' => $mv[$x->stock_movement_id] ?? null,
            'lines' => $x->lines->map(fn ($l) => $l->toArray() + ['item_name' => $items[$l->item_id] ?? null])]), 'receipts' => $receipts]);
    }

    public function storeLandedCost(Request $r): JsonResponse
    {
        $d = $r->validate([
            'stock_movement_id' => ['required', 'integer', $this->ex('stock_movements')], 'method' => 'nullable|in:value,qty,weight', 'notes' => 'nullable|string|max:255',
            'charges' => 'required|array|min:1|max:20', 'charges.*.type' => 'required|in:freight,duty,insurance,clearance,other',
            'charges.*.description' => 'nullable|string|max:150', 'charges.*.amount' => 'required|numeric|min:0.01',
        ]);

        return response()->json(['data' => $this->svc->createLandedCost($d['stock_movement_id'], $d['charges'], $d['method'] ?? 'value', $d['notes'] ?? null)], 201);
    }

    public function postLandedCost(int $id): JsonResponse
    {
        return response()->json(['data' => $this->svc->postLandedCost(LandedCost::with('lines')->findOrFail($id))]);
    }

    public function pim(Request $r): JsonResponse
    {
        $r->validate(['search' => 'nullable|string|max:80', 'incomplete' => 'nullable|boolean']);
        $q = Item::where('is_active', true)->whereNull('parent_id')
            ->when($r->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))->orderBy('name');
        $items = $q->limit(300)->get();
        $variants = Item::whereIn('parent_id', $items->pluck('id'))->get()->groupBy('parent_id');
        $rows = $items->map(fn ($it) => $it->toArray() + $this->svc->completeness($it) + ['variants' => ($variants[$it->id] ?? collect())->values()]);
        if ($r->boolean('incomplete')) {
            $rows = $rows->filter(fn ($x) => $x['score'] < 100)->values();
        }

        return response()->json(['data' => $rows, 'meta' => ['average_score' => $rows->count() ? (int) round($rows->avg('score')) : null]]);
    }

    public function savePim(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'name' => 'sometimes|string|max:255', 'barcode' => 'nullable|string|max:64', 'category' => 'nullable|string|max:80', 'brand' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:10000', 'images' => 'nullable|array|max:12', 'images.*' => 'url|max:500',
            'attributes' => 'nullable|array|max:30', 'attributes.*' => 'nullable|string|max:120', 'weight_g' => 'nullable|integer|min:0|max:10000000',
            'sell_price' => 'nullable|numeric|min:0', 'channel_content' => 'nullable|array', 'channel_content.*.title' => 'nullable|string|max:255',
            'channel_content.*.description' => 'nullable|string|max:10000',
        ]);
        $it = Item::findOrFail($id);
        $old = $it->only(array_keys($d));
        $it->update($d);
        app(AuditLogger::class)->log('inventory.pim_updated', $it, $old, $d);

        return response()->json(['data' => $it->toArray() + $this->svc->completeness($it)]);
    }

    public function variants(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['options' => 'required|array|min:1|max:3', 'options.*' => 'required|array|min:1|max:20', 'options.*.*' => 'string|max:40']);
        $made = $this->svc->generateVariants(Item::findOrFail($id), $d['options']);

        return response()->json(['data' => $made, 'meta' => ['created' => count($made)]], 201);
    }
}
