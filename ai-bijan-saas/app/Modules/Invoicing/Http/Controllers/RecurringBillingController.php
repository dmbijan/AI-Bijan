<?php

namespace App\Modules\Invoicing\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Invoicing\Models\RecurringBilling;
use App\Modules\Invoicing\Services\RecurringBillingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecurringBillingController extends Controller
{
    protected RecurringBillingService $recurringBillingService;

    public function __construct(RecurringBillingService $recurringBillingService)
    {
        $this->recurringBillingService = $recurringBillingService;
    }

    /**
     * List recurring billings
     */
    public function index(Request $request): JsonResponse
    {
        $query = RecurringBilling::where('tenant_id', app(TenantContext::class)->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('billing_type')) {
            $query->where('billing_type', $request->billing_type);
        }

        $billings = $query->orderBy('next_billing_date', 'asc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $billings,
        ]);
    }

    /**
     * Store a new recurring billing
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'billing_type' => 'required|in:subscription,retainer,usage-based,milestone',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'interval' => 'nullable|integer|min:1|max:12',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'auto_send_invoice' => 'nullable|boolean',
            'auto_send_reminder' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['created_by'] = $request->user()->id;

        $billing = $this->recurringBillingService->createRecurringBilling($validated);

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing schedule created successfully',
            'data' => $billing,
        ], 201);
    }

    /**
     * Update recurring billing
     */
    public function update(Request $request, RecurringBilling $billing): JsonResponse
    {
        if ($billing->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'end_date' => 'nullable|date|after:start_date',
            'auto_send_invoice' => 'nullable|boolean',
            'auto_send_reminder' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        $billing->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing updated successfully',
            'data' => $billing->fresh(),
        ]);
    }

    /**
     * Pause recurring billing
     */
    public function pause(Request $request, RecurringBilling $billing): JsonResponse
    {
        if ($billing->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $billing->pause();

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing paused',
            'data' => $billing->fresh(),
        ]);
    }

    /**
     * Resume recurring billing
     */
    public function resume(Request $request, RecurringBilling $billing): JsonResponse
    {
        if ($billing->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $billing->resume();

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing resumed',
            'data' => $billing->fresh(),
        ]);
    }

    /**
     * Cancel recurring billing
     */
    public function cancel(Request $request, RecurringBilling $billing): JsonResponse
    {
        if ($billing->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->recurringBillingService->cancelRecurringBilling($billing);

        return response()->json([
            'success' => true,
            'message' => 'Recurring billing cancelled',
            'data' => $billing->fresh(),
        ]);
    }

    /**
     * Process billing manually
     */
    public function processNow(Request $request, RecurringBilling $billing): JsonResponse
    {
        if ($billing->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->recurringBillingService->processSingleBilling($billing);
            
            return response()->json([
                'success' => true,
                'message' => 'Billing processed successfully',
                'data' => $billing->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process billing: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upcoming billings report
     */
    public function upcoming(Request $request): JsonResponse
    {
        $daysAhead = $request->get('days', 30);

        $upcoming = $this->recurringBillingService->getUpcomingBillings(
            app(TenantContext::class)->id(),
            $daysAhead
        );

        return response()->json([
            'success' => true,
            'data' => $upcoming,
        ]);
    }
}
