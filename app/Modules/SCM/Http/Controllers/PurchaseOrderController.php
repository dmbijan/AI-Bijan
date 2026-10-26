<?php

namespace App\Modules\SCM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SCM\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    protected $poService;

    /**
     * ID tenant semasa — MESTI dari konteks, BUKAN header klien.
     * ⚠️ 26 Okt 2026: kod lama guna `$request->header('X-Tenant-ID')` → IDOR.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    /**
     * List purchase orders.
     * GET /api/scm/purchase-orders
     */
    public function index(Request $request)
    {
        $query = DB::table('purchase_orders')
            ->where('tenant_id', $this->tenantId())
            ->with(['supplier', 'lines', 'approver'])
            ->orderBy('order_date', 'desc');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('po_number', 'like', "%{$request->search}%")
                  ->orWhere('internal_notes', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->paginate(20));
    }

    /**
     * Get single PO.
     * GET /api/scm/purchase-orders/{id}
     */
    public function show($id, Request $request)
    {
        $po = DB::table('purchase_orders')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->with(['lines', 'supplier', 'approver', 'grns'])
            ->first();
        
        if (! $po) {
            return response()->json(['message' => 'PO not found'], 404);
        }
        
        return response()->json($po);
    }

    /**
     * Create new draft PO.
     * POST /api/scm/purchase-orders
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|integer',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_terms_days' => 'nullable|integer|min:1',
            'internal_notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'nullable|integer',
            'lines.*.item_code' => 'nullable|string|max:100',
            'lines.*.item_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity_ordered' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom' => 'nullable|string|max:10',
            'lines.*.is_taxable' => 'nullable|boolean',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $validated['tenant_id'] = $this->tenantId();
        
        try {
            $po = $this->poService->createPurchaseOrder($validated);
            return response()->json($po, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Submit PO for approval.
     * POST /api/scm/purchase-orders/{id}/submit
     */
    public function submit($id, Request $request)
    {
        $po = DB::table('purchase_orders')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();
        
        if (! $po || $po->status !== 'draft') {
            return response()->json(['message' => 'Invalid PO for submission'], 400);
        }
        
        try {
            $submitted = $this->poService->submitForApproval(\App\Modules\SCM\Models\PurchaseOrder::find($id));
            return response()->json($submitted);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve a pending PO.
     * POST /api/scm/purchase-orders/{id}/approve
     */
    public function approve($id, Request $request)
    {
        $po = \App\Modules\SCM\Models\PurchaseOrder::find($id);
        
        if (! $po || $po->tenant_id !== $this->tenantId() || $po->status !== 'pending_approval') {
            return response()->json(['message' => 'Invalid PO for approval'], 400);
        }
        
        try {
            $approved = $this->poService->approvePurchaseOrder($po);
            return response()->json($approved);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Send PO to supplier.
     * POST /api/scm/purchase-orders/{id}/send
     */
    public function send($id, Request $request)
    {
        $po = \App\Modules\SCM\Models\PurchaseOrder::find($id);
        
        if (! $po || $po->tenant_id !== $this->tenantId() || $po->status !== 'approved') {
            return response()->json(['message' => 'Invalid PO for sending'], 400);
        }
        
        try {
            $sent = $this->poService->sendToSupplier($po);
            return response()->json($sent);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Receive goods against PO.
     * POST /api/scm/purchase-orders/{id}/receive
     */
    public function receive($id, Request $request)
    {
        $po = \App\Modules\SCM\Models\PurchaseOrder::find($id);
        
        if (! $po || $po->tenant_id !== $this->tenantId()) {
            return response()->json(['message' => 'PO not found'], 404);
        }
        
        $validated = $request->validate([
            'received_items' => 'required|array',
            'received_items.*.purchase_order_line_id' => 'required|integer',
            'received_items.*.quantity_received' => 'required|integer|min:0',
            'received_items.*.quantity_rejected' => 'nullable|integer|min:0',
        ]);
        
        try {
            $grn = $this->poService->receiveGoods($po, $validated['received_items']);
            return response()->json($grn, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a PO.
     * POST /api/scm/purchase-orders/{id}/cancel
     */
    public function cancel($id, Request $request)
    {
        $po = \App\Modules\SCM\Models\PurchaseOrder::find($id);
        
        if (! $po || $po->tenant_id !== $this->tenantId()) {
            return response()->json(['message' => 'PO not found'], 404);
        }
        
        try {
            $this->poService->cancelPurchaseOrder($po);
            return response()->json(['message' => 'PO cancelled']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
