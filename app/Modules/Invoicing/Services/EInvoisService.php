<?php

namespace App\Modules\Invoicing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Modules\Invoicing\Models\Invoice;

class EInvoisService
{
    /**
     * Submit invoice to LHDN MyInvois API
     * 
     * @see https://myinvois.hasil.gov.my/ (for official API documentation)
     */
    public function submitToLHDN(Invoice $invoice): array
    {
        if (!$invoice->canSendEInvois()) {
            return [
                'success' => false,
                'error' => 'Invoice cannot be submitted for e-Invois',
            ];
        }

        try {
            // Get LHDN API configuration
            $baseUrl = config('invoicing.einvois.api_base_url', 'https://myinvois-api.hasil.gov.my/v1');
            $apiKey = config('invoicing.einvois.api_key');
            $apiSecret = config('invoicing.einvois.api_secret');

            if (!$apiKey || !$apiSecret) {
                Log::warning('e-Invois API credentials not configured');
                return [
                    'success' => false,
                    'error' => 'e-Invois API credentials not configured',
                ];
            }

            // Prepare payload for LHDN MyInvois API
            $payload = $this->buildLHDNPayload($invoice);

            // Make API request (sandbox/production)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->connectTimeout(10)->post("{$baseUrl}/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update invoice with e-Invois reference
                $invoice->update([
                    'einvois_submission_status' => 'submitted',
                    'einvois_reference_no' => $data['uuid'] ?? $data['referenceNo'] ?? null,
                    'einvois_submission_date' => now(),
                ]);

                // If already accepted (synchronous validation)
                if (($data['status'] ?? '') === 'VALIDATED' || ($data['validationStatus'] ?? '') === 'accepted') {
                    $invoice->update([
                        'einvois_submission_status' => 'accepted',
                    ]);
                }

                return [
                    'success' => true,
                    'uuid' => $data['uuid'] ?? null,
                    'status' => $data['status'] ?? 'submitted',
                    'message' => 'Invoice submitted successfully',
                ];
            } else {
                $error = $response->json()['message'] ?? $response->body();
                Log::error('e-Invois submission failed: ' . $error);
                
                $invoice->update([
                    'einvois_submission_status' => 'rejected',
                    'einvois_rejection_reason' => $error,
                ]);

                return [
                    'success' => false,
                    'error' => 'Submission failed: ' . $error,
                ];
            }
        } catch (\Exception $e) {
            Log::error('e-Invois submission exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Submission failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build payload for LHDN MyInvois API
     * 
     * Structure based on LHDN MyInvois API specification
     */
    protected function buildLHDNPayload(Invoice $invoice): array
    {
        return [
            'type' => 'INVOICE',
            'channel' => 'API',
            'documentReferenceNo' => $invoice->invoice_number,
            'createdOn' => now()->toIso8601String(),
            'modifiedOn' => now()->toIso8601String(),
            'invoiceType' => $this->getInvoiceType($invoice),
            'invoiceDate' => $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d') : now()->format('Y-m-d'),
            'paymentTermsDate' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
            'buyerReferenceNo' => $invoice->customer_tax_id ?? null,
            'sellerOrganization' => $this->getSellerOrganization($invoice),
            'buyerOrganization' => $this->getBuyerOrganization($invoice),
            'invoiceCurrencyCode' => $invoice->currency ?? 'MYR',
            'conversionRateToMYR' => $invoice->exchange_rate ?? 1.0,
            'lineItems' => $this->buildLineItems($invoice),
            'summarisedAmounts' => $this->buildSummarisedAmounts($invoice),
            'taxTotals' => $this->buildTaxTotals($invoice),
            'additionalCosts' => $this->buildAdditionalCosts($invoice),
            'invoiceTotals' => $this->buildInvoiceTotals($invoice),
        ];
    }

    /**
     * Get seller organization details (tenant configuration)
     */
    protected function getSellerOrganization(Invoice $invoice): array
    {
        // TODO: Get from tenant settings
        return [
            'name' => config('app.name', 'AI BIJAN SaaS'),
            'registrationNo' => config('invoicing.einvois.seller_registration_no', ''),
            'partyIdentifier' => config('invoicing.einvois.seller_ein', ''),
            'address' => config('invoicing.einvois.seller_address', ''),
            'postalCode' => config('invoicing.einvois.seller_postal_code', ''),
            'countryCode' => 'MY',
            'email' => config('invoicing.einvois.seller_email', ''),
            'phone' => config('invoicing.einvois.seller_phone', ''),
        ];
    }

    /**
     * Get buyer organization details
     */
    protected function getBuyerOrganization(Invoice $invoice): array
    {
        return [
            'name' => $invoice->customer_name ?? '',
            'registrationNo' => $invoice->customer_tax_id ?? '',
            'partyIdentifier' => $invoice->customer_tax_id ?? '',
            'address' => $invoice->billing_address ?? '',
            'email' => $invoice->customer_email ?? '',
        ];
    }

    /**
     * Build line items from invoice items
     */
    protected function buildLineItems(Invoice $invoice): array
    {
        $items = [];
        
        foreach ($invoice->items as $item) {
            $items[] = [
                'orderQty' => (float) $item->quantity,
                'orderUnitPrice' => number_format($item->unit_price, 2, '.', ''),
                'commodityCd' => null, // TODO: Map product to Malaysian Commodity Code
                'brokerTransactionInd' => false,
                'discountDetails' => $item->discount_amount > 0 ? [
                    ['discountAmt' => number_format($item->discount_amount, 2, '.', '')],
                ] : null,
                'taxDetails' => $item->tax_amount > 0 ? [
                    ['taxAmt' => number_format($item->tax_amount, 2, '.'), 'taxRate' => (float) $item->tax_percentage, 'taxExemptAmt' => 0],
                ] : null,
                'totalDiscountAmt' => number_format($item->discount_amount, 2, '.', ''),
                'totalTaxAmt' => number_format($item->tax_amount, 2, '.', ''),
                'totalChargeAmt' => number_format($item->line_total, 2, '.', ''),
                'freeGoodsInd' => false,
            ];
        }

        return $items;
    }

    /**
     * Build summarised amounts
     */
    protected function buildSummarisedAmounts(Invoice $invoice): array
    {
        return [
            'totalChargeAmt' => number_format($invoice->subtotal, 2, '.', ''),
            'totalDiscountAmt' => number_format($invoice->discount_total, 2, '.', ''),
            'netSaleValue' => number_format($invoice->subtotal - $invoice->discount_total, 2, '.', ''),
        ];
    }

    /**
     * Build tax totals
     */
    protected function buildTaxTotals(Invoice $invoice): array
    {
        return [
            'totalTaxAmt' => number_format($invoice->tax_total, 2, '.', ''),
            'selfAdjustedTaxAmt' => 0,
            'selfAssessedTaxAmt' => 0,
        ];
    }

    /**
     * Build additional costs (shipping, etc.)
     */
    protected function buildAdditionalCosts(Invoice $invoice): array
    {
        if ($invoice->shipping_total > 0) {
            return [
                ['costAmt' => number_format($invoice->shipping_total, 2, '.'), 'costType' => 'FREIGHT'],
            ];
        }

        return [];
    }

    /**
     * Build invoice totals
     */
    protected function buildInvoiceTotals(Invoice $invoice): array
    {
        return [
            'totalChargeAmt' => number_format($invoice->grand_total, 2, '.', ''),
            'TotalSettlementAmt' => number_format($invoice->grand_total, 2, '.', ''),
            'totalCreditNoteAmt' => 0,
            'totalDebitNoteAmt' => 0,
            'totalRelatedPartyDissolutionAmt' => 0,
        ];
    }

    /**
     * Determine invoice type based on status
     */
    protected function getInvoiceType(Invoice $invoice): string
    {
        return 'INVOICE'; // Could also be CREDIT_NOTE or DEBIT_NOTE
    }

    /**
     * Cancel invoice with LHDN
     */
    public function cancelWithLHDN(Invoice $invoice, string $reason = ''): bool
    {
        if ($invoice->einvois_submission_status !== 'accepted') {
            return false;
        }

        try {
            $baseUrl = config('invoicing.einvois.api_base_url', 'https://myinvois-api.hasil.gov.my/v1');
            $apiKey = config('invoicing.einvois.api_key');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->delete("{$baseUrl}/invoices/{$invoice->einvois_reference_no}", [
                'cancellationReason' => $reason,
                'cancelledBy' => auth()->user()?->email ?? 'system',
            ]);

            if ($response->successful()) {
                $invoice->update([
                    'einvois_submission_status' => 'cancelled',
                    'einvois_cancelled_at' => now(),
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('e-Invois cancellation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate invoice format before submission
     */
    public function validateForEInvois(Invoice $invoice): array
    {
        $errors = [];
        $warnings = [];

        // Check required fields
        if (!$invoice->customer_tax_id) {
            $errors[] = 'Customer tax ID is required for e-Invois';
        }

        if (!$invoice->customer_name) {
            $errors[] = 'Customer name is required';
        }

        if (!$invoice->customer_email) {
            $warnings[] = 'Customer email is recommended for e-Invois delivery';
        }

        if (!$invoice->issue_date || !$invoice->due_date) {
            $errors[] = 'Issue date and due date are required';
        }

        if ($invoice->items->count() === 0) {
            $errors[] = 'Invoice must have at least one line item';
        }

        // Check tax rates (must be 0%, 6%, 8%, or 10% for Malaysia)
        foreach ($invoice->items as $item) {
            $taxRate = $item->tax_percentage ?? 0;
            if (!in_array($taxRate, [0, 6, 8, 10])) {
                $warnings[] = "Item '{$item->product_name}' has non-standard tax rate: {$taxRate}%";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
