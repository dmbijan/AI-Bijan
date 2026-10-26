<?php

namespace App\Modules\Invoicing\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\Invoicing\Models\Quotation;
use App\Modules\Invoicing\Models\SalesOrder;
use App\Modules\Invoicing\Models\SalesOrderItem;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Invoicing\Models\CreditNote;
use App\Modules\Invoicing\Models\DebitNote;
use App\Modules\Invoicing\Models\Receipt;

class InvoiceService
{
    /**
     * Create a new quotation
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quotation = Quotation::create([
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'validity_days' => $data['validity_days'] ?? 30,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Set expiry date
            $quotation->expiry_date = now()->addDays($quotation->validity_days)->toDateString();

            // Add lines if provided
            if (isset($data['lines'])) {
                foreach ($data['lines'] as $lineData) {
                    $quotation->lines()->create(array_merge($lineData, [
                        'tenant_id' => $data['tenant_id'],
                    ]));
                }
            }

            $quotation->recalculateTotals();
            return $quotation;
        });
    }

    /**
     * Convert quotation to sales order
     */
    public function convertToSalesOrder(Quotation $quotation, array $orderData = []): SalesOrder
    {
        return DB::transaction(function () use ($quotation, $orderData) {
            $order = SalesOrder::create([
                'tenant_id' => $quotation->tenant_id,
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'customer_name' => $orderData['customer_name'] ?? $quotation->customer_name,
                'customer_email' => $orderData['customer_email'] ?? $quotation->customer_email,
                'customer_phone' => $orderData['customer_phone'] ?? $quotation->customer_phone,
                'billing_address' => $orderData['billing_address'] ?? null,
                'shipping_address' => $orderData['shipping_address'] ?? null,
                'order_date' => $orderData['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $orderData['expected_delivery_date'] ?? null,
                'subtotal' => $quotation->subtotal,
                'tax_total' => $quotation->tax_total,
                'discount_total' => $quotation->discount_total,
                'shipping_total' => $quotation->shipping_total,
                'grand_total' => $quotation->grand_total,
                'currency' => $quotation->currency,
                'exchange_rate' => $quotation->exchange_rate,
                'notes' => $orderData['notes'] ?? $quotation->notes,
                'terms_conditions' => $orderData['terms_conditions'] ?? $quotation->terms_conditions,
                'status' => 'pending',
                'created_by' => $orderData['created_by'] ?? auth()->id(),
            ]);

            // Copy lines from quotation to order items
            foreach ($quotation->lines as $line) {
                $order->items()->create([
                    'product_id' => $line->product_id ?? null,
                    'product_name' => $line->product_name ?? $line->description ?? null,
                    'product_sku' => $line->product_sku ?? null,
                    'description' => $line->description ?? null,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'discount_percentage' => $line->discount_percentage ?? 0,
                    'tax_percentage' => $line->tax_percentage ?? 0,
                ]);
            }

            $order->recalculateTotals();

            // Update quotation status
            $quotation->update([
                'status' => 'converted',
                'converted_to_order_id' => $order->id,
                'converted_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Create invoice from sales order
     */
    public function createInvoiceFromOrder(SalesOrder $order, array $invoiceData = []): Invoice
    {
        return DB::transaction(function () use ($order, $invoiceData) {
            $invoice = Invoice::create([
                'tenant_id' => $order->tenant_id,
                'sales_order_id' => $order->id,
                'quotation_id' => $order->quotation_id,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_tax_id' => $invoiceData['customer_tax_id'] ?? null,
                'billing_address' => $invoiceData['billing_address'] ?? $order->billing_address,
                'issue_date' => $invoiceData['issue_date'] ?? now()->toDateString(),
                'due_date' => $invoiceData['due_date'] ?? now()->addDays(30)->toDateString(),
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'discount_total' => $order->discount_total,
                'shipping_total' => $order->shipping_total,
                'grand_total' => $order->grand_total,
                'currency' => $order->currency,
                'exchange_rate' => $order->exchange_rate,
                'notes' => $invoiceData['notes'] ?? $order->notes,
                'terms_conditions' => $invoiceData['terms_conditions'] ?? $order->terms_conditions,
                'status' => 'issued',
                'created_by' => $invoiceData['created_by'] ?? auth()->id(),
            ]);

            // Copy items from order to invoice
            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percentage' => $item->discount_percentage ?? 0,
                    'tax_percentage' => $item->tax_percentage ?? 0,
                ]);
            }

            $invoice->recalculateTotals();
            return $invoice;
        });
    }

    /**
     * Record payment against invoice
     */
    public function recordPayment(Invoice $invoice, float $amount, array $paymentData = []): Receipt
    {
        return DB::transaction(function () use ($invoice, $amount, $paymentData) {
            // Record receipt
            $receipt = Receipt::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
                'reference_number' => $paymentData['reference_number'] ?? null,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'notes' => $paymentData['notes'] ?? null,
                'received_at' => $paymentData['received_at'] ?? now(),
                'created_by' => $paymentData['created_by'] ?? auth()->id(),
            ]);

            // Update invoice
            $invoice->recordPayment($amount);

            return $receipt;
        });
    }

    /**
     * Create credit note
     */
    public function createCreditNote(array $data): CreditNote
    {
        return DB::transaction(function () use ($data) {
            $creditNote = CreditNote::create([
                'tenant_id' => $data['tenant_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'reason' => $data['reason'],
                'original_amount' => $data['original_amount'] ?? 0,
                'credit_amount' => $data['credit_amount'],
                'remaining_credit' => $data['credit_amount'],
                'currency' => $data['currency'] ?? 'MYR',
                'notes' => $data['notes'] ?? null,
                'issued_at' => $data['issued_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? null,
                'status' => 'issued',
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $creditNote;
        });
    }

    /**
     * Apply credit note to invoice
     */
    public function applyCreditNote(CreditNote $creditNote, float $amount = null): void
    {
        $applyAmount = $amount ?? min($creditNote->remaining_credit, $creditNote->invoice->amount_outstanding);
        
        $creditNote->applyToInvoice($applyAmount);
    }

    /**
     * Create debit note
     */
    public function createDebitNote(array $data): DebitNote
    {
        return DB::transaction(function () use ($data) {
            $debitNote = DebitNote::create([
                'tenant_id' => $data['tenant_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'reason' => $data['reason'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'MYR',
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $debitNote;
        });
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Invoice $invoice, string $reason = ''): bool
    {
        if (!$invoice->canBeCancelled()) {
            return false;
        }

        return DB::transaction(function () use ($invoice, $reason) {
            $invoice->update([
                'status' => 'cancelled',
                'notes' => ($invoice->notes ?? '') . "\n\nCancelled: " . $reason,
            ]);
            return true;
        });
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStatistics(int $tenantId, array $filters = []): array
    {
        $query = Invoice::where('tenant_id', $tenantId);

        // Apply filters
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }
        if (isset($filters['from_date'])) {
            $query->where('issue_date', '>=', $filters['from_date']);
        }
        if (isset($filters['to_date'])) {
            $query->where('issue_date', '<=', $filters['to_date']);
        }

        $totalInvoices = $query->count();
        $totalAmount = $query->sum('grand_total');
        $totalPaid = $query->sum('amount_paid');
        $totalOutstanding = $query->sum('amount_outstanding');
        $overdueCount = (clone $query)->overdue()->count();
        $overdueAmount = (clone $query)->overdue()->sum('amount_outstanding');

        return [
            'total_invoices' => $totalInvoices,
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'collection_rate' => $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0,
        ];
    }
}
