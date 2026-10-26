<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\WorkOrder;
use App\Modules\Manufacturing\ManufacturingService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManufacturingController extends Controller
{
    private function tid(): int
    {
        return app(TenantContext::class)->id();
    }

    public function boms(Request $r)
    {
        return response()->json(Bom::with(['item:id,sku,name', 'lines.item:id,sku,name,uom'])->when($r->query('item_id'), fn ($q, $i) => $q->where('item_id', $i))->orderByDesc('id')->get());
    }

    public function showBom(int $id, ManufacturingService $svc)
    {
        $b = Bom::with(['item:id,sku,name', 'lines.item:id,sku,name,uom'])->findOrFail($id);

        return response()->json($b->toArray() + ['standard_cost' => $svc->standardCost($b)]);
    }

    public function createBom(Request $r, ManufacturingService $svc)
    {
        $item = Rule::exists('items', 'id')->where('tenant_id', $this->tid());
        $d = $r->validate(['item_id' => ['required', 'integer', $item], 'type' => 'required|in:manufacture,kit', 'quantity' => 'nullable|numeric|gt:0', 'labour_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0', 'is_default' => 'boolean', 'notes' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1|max:200', 'lines.*.item_id' => ['required', 'integer', $item], 'lines.*.qty' => 'required|numeric|gt:0', 'lines.*.wastage_pct' => 'nullable|numeric|min:0|max:100']);

        return response()->json($svc->createBom($d), 201);
    }

    public function deactivateBom(int $id)
    {
        $b = Bom::findOrFail($id);
        $b->update(['is_active' => false, 'is_default' => false]);
        app(AuditLogger::class)->log('manufacturing.bom_deactivated', $b);

        return response()->json(['ok' => true]);
    }

    public function workOrders(Request $r)
    {
        return response()->json(WorkOrder::with('materials')->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))->orderByDesc('id')->paginate(50));
    }

    public function createWorkOrder(Request $r, ManufacturingService $svc)
    {
        $wh = Rule::exists('warehouses', 'id')->where('tenant_id', $this->tid());
        $d = $r->validate(['bom_id' => ['required', 'integer', Rule::exists('boms', 'id')->where('tenant_id', $this->tid())], 'qty' => 'required|numeric|gt:0',
            'source_warehouse_id' => ['required', 'integer', $wh], 'target_warehouse_id' => ['required', 'integer', $wh]]);

        return response()->json($svc->createWorkOrder($d), 201);
    }

    public function completeWorkOrder(Request $r, int $id, ManufacturingService $svc)
    {
        $d = $r->validate(['qty_good' => 'required|numeric|gt:0', 'qty_reject' => 'nullable|numeric|min:0', 'consumed' => 'nullable|array', 'consumed.*' => 'numeric|min:0']);

        return response()->json($svc->complete(WorkOrder::findOrFail($id), $d['qty_good'], $d['qty_reject'] ?? 0, $d['consumed'] ?? []));
    }

    public function cancelWorkOrder(int $id)
    {
        $wo = WorkOrder::findOrFail($id);
        abort_unless(in_array($wo->status, ['draft', 'released'], true), 422, 'Work order selesai tidak boleh dibatalkan.');
        $wo->update(['status' => 'cancelled']);
        app(AuditLogger::class)->log('manufacturing.wo_cancelled', $wo);

        return response()->json(['ok' => true]);
    }

    /** Untuk POS / kedai online: tolak stok (atau bahan resipi kit) bagi satu baris jualan, idempoten. */
    public function saleDeduct(Request $r, ManufacturingService $svc)
    {
        $d = $r->validate(['item_id' => ['required', 'integer', Rule::exists('items', 'id')->where('tenant_id', $this->tid())], 'qty' => 'required|numeric|gt:0',
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('tenant_id', $this->tid())], 'idempotency_key' => 'required|string|max:100']);

        return response()->json($svc->deductForSale($d['item_id'], $d['qty'], $d['warehouse_id'], $d['idempotency_key']));
    }
}
