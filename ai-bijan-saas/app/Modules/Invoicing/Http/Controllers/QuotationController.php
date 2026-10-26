<?php

namespace App\Modules\Invoicing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Invoicing\Models\Quotation;
use App\Modules\Invoicing\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuotationController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * List quotations with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quotation::with(['customer', 'salesOrder'])
            ->where('tenant_id', app(TenantContext::class)->id());

        // Filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('from_date')) {
            $query->where('issue_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('issue_date', '<=', $request->to_date);
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $quotations,
        ]);
    }

    /**
     * Store a new quotation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'issue_date' => 'nullable|date',
            'validity_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['created_by'] = $request->user()->id;

        $quotation = $this->invoiceService->createQuotation($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation created successfully',
            'data' => $quotation->load(['customer', 'lines']),
        ], 201);
    }

    /**
     * Display a specific quotation
     */
    public function show(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $quotation->load(['customer', 'lines', 'salesOrder']),
        ]);
    }

    /**
     * Update quotation
     */
    public function update(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($quotation->status, ['draft', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update quotation with status: ' . $quotation->status,
            ], 422);
        }

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'issue_date' => 'nullable|date',
            'validity_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'lines' => 'nullable|array',
        ]);

        $quotation->update($validated);

        // Update expiry date if validity changed
        if (isset($validated['validity_days'])) {
            $quotation->expiry_date = now()->addDays($validated['validity_days'])->toDateString();
            $quotation->save();
        }

        // Recalculate totals
        $quotation->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Quotation updated successfully',
            'data' => $quotation->load(['customer', 'lines']),
        ]);
    }

    /**
     * Submit quotation for approval
     */
    public function submit(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($quotation->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft quotations can be submitted',
            ], 422);
        }

        $quotation->update([
            'status' => 'pending',
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quotation submitted for approval',
            'data' => $quotation,
        ]);
    }

    /**
     * Accept quotation
     */
    public function accept(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$quotation->canConvertToOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation cannot be accepted',
            ], 422);
        }

        $quotation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quotation accepted',
            'data' => $quotation,
        ]);
    }

    /**
     * Reject quotation
     */
    public function reject(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $quotation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quotation rejected',
            'data' => $quotation,
        ]);
    }

    /**
     * Convert quotation to sales order
     */
    public function convertToOrder(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$quotation->canConvertToOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation cannot be converted to order',
            ], 422);
        }

        $validated = $request->validate([
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $order = $this->invoiceService->convertToSalesOrder($quotation, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation converted to sales order',
            'data' => $order->load(['items', 'quotation']),
        ], 201);
    }

    /**
     * Delete quotation
     */
    public function destroy(Request $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($quotation->status, ['draft', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete quotation with status: ' . $quotation->status,
            ], 422);
        }

        $quotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quotation deleted successfully',
        ]);
    }
}
