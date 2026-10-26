<?php

namespace App\Modules\Invoicing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Invoicing\Models\RecurringBilling;
use App\Modules\Invoicing\Models\SalesOrder;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoiceService;

class RecurringBillingService
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Create a new recurring billing schedule
     */
    public function createRecurringBilling(array $data): RecurringBilling
    {
        return DB::transaction(function () use ($data) {
            $billing = RecurringBilling::create([
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'billing_type' => $data['billing_type'] ?? 'subscription',
                'frequency' => $data['frequency'] ?? 'monthly',
                'interval' => $data['interval'] ?? 1,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? null,
                'amount' => $data['amount'],
                'tax_percentage' => $data['tax_percentage'] ?? config('invoicing.tax.sst_rate', 0),
                'currency' => $data['currency'] ?? 'MYR',
                'auto_send_invoice' => $data['auto_send_invoice'] ?? false,
                'auto_send_reminder' => $data['auto_send_reminder'] ?? false,
                'reminder_days_before' => $data['reminder_days_before'] ?? 7,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Calculate first next billing date
            $billing->calculateNextBillingDate();

            return $billing;
        });
    }

    /**
     * Process all due recurring billings
     */
    public function processDueBillings(): int
    {
        $dueBillings = RecurringBilling::active()
            ->where('next_billing_date', '<=', now()->toDateString())
            ->get();

        $processed = 0;

        foreach ($dueBillings as $billing) {
            try {
                $this->processSingleBilling($billing);
                $processed++;
            } catch (\Exception $e) {
                Log::error("Failed to process recurring billing #{$billing->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }

    /**
     * Process a single recurring billing
     */
    public function processSingleBilling(RecurringBilling $billing): void
    {
        DB::transaction(function () use ($billing) {
            // Create invoice if auto_send_invoice is enabled
            if ($billing->auto_send_invoice && $billing->customer_id) {
                $invoiceData = [
                    'tenant_id' => $billing->tenant_id,
                    'customer_id' => $billing->customer_id,
                    'customer_name' => $billing->customer_name,
                    'customer_email' => $billing->customer_email,
                    'issue_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'subtotal' => $billing->amount,
                    'tax_total' => $billing->amount * ($billing->tax_percentage / 100),
                    'grand_total' => $billing->grand_total,
                    'currency' => $billing->currency,
                    'notes' => "Auto-generated from recurring billing: {$billing->billing_type}",
                    'created_by' => 1, // System user
                ];

                $invoice = $this->invoiceService->createQuotation($invoiceData);
                
                // Update tracking
                $billing->total_invoices_generated++;
                $billing->last_invoice_date = now()->toDateString();
            }

            // Update billing dates
            $billing->last_billing_date = now()->toDateString();
            $billing->calculateNextBillingDate();

            // Check if completed
            if ($billing->isCompleted()) {
                $billing->status = 'completed';
            }

            $billing->save();
        });
    }

    /**
     * Send reminders for upcoming due billings
     */
    public function sendReminders(): int
    {
        $billings = RecurringBilling::active()
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now()->addDays(30)->toDateString())
            ->get();

        $sent = 0;

        foreach ($billings as $billing) {
            $daysUntilDue = now()->diffInDays($billing->next_billing_date, false);
            
            if ($daysUntilDue <= ($billing->reminder_days_before ?? 7)) {
                // Send reminder email/notification
                // TODO: Implement notification service integration
                Log::info("Sending reminder for recurring billing #{$billing->id}, due in {$daysUntilDue} days");
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Cancel a recurring billing
     */
    public function cancelRecurringBilling(RecurringBilling $billing): bool
    {
        if (!$billing->canBeCancelled()) {
            return false;
        }

        $billing->cancel();
        return true;
    }

    /**
     * Get upcoming billings report
     */
    public function getUpcomingBillings(int $tenantId, int $daysAhead = 30): array
    {
        return RecurringBilling::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('next_billing_date', '>=', now()->toDateString())
            ->where('next_billing_date', '<=', now()->addDays($daysAhead)->toDateString())
            ->orderBy('next_billing_date')
            ->get()
            ->toArray();
    }
}
