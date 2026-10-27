<?php

namespace App\Modules\Invoicing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Invoicing\Models\CreditNote;
use App\Modules\Invoicing\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CreditNoteController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * List credit notes
     */
    public function index(Request $request): JsonResponse
    {
        $query = CreditNote::where('tenant_id', app(TenantContext::class)->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $creditNotes = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $creditNotes,
        ]);
    }

    /**
     * Store a new credit note
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'reason' => 'required|in:return,damage,overcharge,discount',
            'original_amount' => 'required|numeric|min:0',
            'credit_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['created_by'] = $request->user()->id;

        $creditNote = $this->invoiceService->createCreditNote($validated);

        return response()->json([
            'success' => true,
            'message' => 'Credit note created successfully',
            'data' => $creditNote,
        ], 201);
    }

    /**
     * Apply credit note to invoice
     */
    public function apply(Request $request, CreditNote $creditNote): JsonResponse
    {
        if ($creditNote->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
        ]);

        $this->invoiceService->applyCreditNote($creditNote, $validated['amount'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Credit applied successfully',
            'data' => $creditNote->fresh(),
        ]);
    }
}
