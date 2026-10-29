<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Quotation;
use App\Modules\CRM\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    protected $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    /**
     * Display a listing of quotations.
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'opportunity', 'approver'])
            ->where('tenant_id', app(TenantContext::class)->id());
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('quotation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('quotation_date', '<=', $request->date_to);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('quotation_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $quotations = $query->paginate(15);
        
        return response()->json($quotations);
    }

    /**
     * Store a newly created quotation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'nullable|date',
            'valid_until' => 'nullable|date|after:quotation_date',
            'currency' => 'nullable|string|max:3',
            'payment_terms_days' => 'nullable|integer|min:0',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string|max:100',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'nullable|exists:items,id',
            'lines.*.item_code' => 'nullable|string|max:100',
            'lines.*.item_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.uom' => 'nullable|string|max:10',
        ]);
        
        $quotation = $this->quotationService->createQuotation(array_merge($validated, [
            'tenant_id' => app(TenantContext::class)->id(),
        ]));
        
        return response()->json($quotation->load(['customer', 'lines']), 201);
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation)
    {
        return response()->json($quotation->load(['customer', 'opportunity', 'approver', 'lines']));
    }

    /**
     * Update the specified quotation.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'valid_until' => 'nullable|date',
            'payment_terms_days' => 'nullable|integer|min:0',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string|max:100',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines' => 'nullable|array',
            'lines.*.id' => 'nullable|exists:quotation_lines,id',
            'lines.*.item_id' => 'nullable|exists:items,id',
            'lines.*.item_code' => 'nullable|string|max:100',
            'lines.*.item_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.uom' => 'nullable|string|max:10',
        ]);
        
        $quotation = $this->quotationService->updateQuotation($quotation, $validated);
        
        return response()->json($quotation->load(['customer', 'lines']));
    }

    /**
     * Submit quotation for approval.
     */
    public function submit(Quotation $quotation)
    {
        $quotation = $this->quotationService->submitForApproval($quotation);
        
        return response()->json($quotation);
    }

    /**
     * Approve quotation.
     */
    public function approve(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'approved_by' => 'required|exists:users,id',
        ]);
        
        $quotation = $this->quotationService->approveQuotation($quotation, $validated['approved_by']);
        
        return response()->json($quotation);
    }

    /**
     * Convert quotation to sales order.
     */
    public function convertToOrder(Quotation $quotation)
    {
        $orderId = $this->quotationService->convertToOrder($quotation);
        
        return response()->json([
            'message' => 'Quotation converted to sales order',
            'order_id' => $orderId,
        ]);
    }

    /**
     * Get quotation statistics.
     */
    public function getStatistics()
    {
        $stats = $this->quotationService->getQuotationStatistics(app(TenantContext::class)->id());
        
        return response()->json($stats);
    }

    public function accept(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'accept']);
    }

    public function reject(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'reject']);
    }

    public function destroy(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'destroy']);
    }
}
