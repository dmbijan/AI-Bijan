<?php

namespace App\Modules\Invoicing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Invoicing\Services\EInvoisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected EInvoisService $eInvoisService;

    public function __construct(InvoiceService $invoiceService, EInvoisService $eInvoisService)
    {
        $this->invoiceService = $invoiceService;
        $this->eInvoisService = $eInvoisService;
    }

    /**
     * List invoices with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'salesOrder', 'items'])
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
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Display a specific invoice
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice->load(['customer', 'salesOrder', 'items']),
        ]);
    }

    /**
     * Create invoice from sales order
     */
    public function createFromOrder(Request $request, $orderId): JsonResponse
    {
        $order = app(TenantContext::class)->id() === app(TenantContext::class)->id() 
            ? \App\Modules\Invoicing\Models\SalesOrder::with('items')->find($orderId)
            : null;

        if (!$order || $order->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order not found',
            ], 404);
        }

        $validated = $request->validate([
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'customer_tax_id' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $invoice = $this->invoiceService->createInvoiceFromOrder($order, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $invoice->load(['customer', 'items']),
        ], 201);
    }

    /**
     * Record payment against invoice
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,fpx,card,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validated['amount'] > $invoice->amount_outstanding) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount cannot exceed outstanding amount',
            ], 422);
        }

        $validated['created_by'] = $request->user()->id;

        $receipt = $this->invoiceService->recordPayment($invoice, $validated['amount'], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => [
                'receipt' => $receipt,
                'invoice' => $invoice->fresh(),
            ],
        ]);
    }

    /**
     * Submit invoice to LHDN e-Invois
     */
    public function submitEInvois(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Validate first
        $validation = $this->eInvoisService->validateForEInvois($invoice);
        
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice validation failed',
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
            ], 422);
        }

        $result = $this->eInvoisService->submitToLHDN($invoice);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice submitted to LHDN successfully',
                'data' => $result + ['invoice' => $invoice->fresh()],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Submission failed',
            'data' => $result,
        ], 422);
    }

    /**
     * Cancel invoice
     */
    public function cancel(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $result = $this->invoiceService->cancelInvoice($invoice, $validated['reason']);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully',
                'data' => $invoice->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot cancel this invoice',
        ], 422);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($request->filled('from_date')) {
            $filters['from_date'] = $request->from_date;
        }
        if ($request->filled('to_date')) {
            $filters['to_date'] = $request->to_date;
        }
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        $stats = $this->invoiceService->getInvoiceStatistics(
            app(TenantContext::class)->id(),
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Download invoice PDF (placeholder for future implementation)
     */
    public function downloadPdf(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // TODO: Implement PDF generation using DomPDF or similar
        return response()->json([
            'success' => true,
            'message' => 'PDF generation coming soon',
        ]);
    }
}
