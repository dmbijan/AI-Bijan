<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Quotation;
use App\Modules\CRM\Models\QuotationLine;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * Create a new quotation.
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quotationNumber = Quotation::generateQuotationNumber();
            
            $quotationDate = $data['quotation_date'] ?? now()->format('Y-m-d');
            $validUntil = $data['valid_until'] ?? now()->addDays(30)->format('Y-m-d');
            
            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'tenant_id' => $data['tenant_id'],
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'quotation_date' => $quotationDate,
                'valid_until' => $validUntil,
                'status' => 'draft',
                'currency' => $data['currency'] ?? 'MYR',
                'payment_terms_days' => $data['payment_terms_days'] ?? 30,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_method' => $data['shipping_method'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add line items
            if (isset($data['lines']) && is_array($data['lines'])) {
                foreach ($data['lines'] as $lineData) {
                    $this->createLine($quotation, $lineData);
                }
                
                $quotation->recalculateTotals();
            }
            
            return $quotation;
        });
    }

    /**
     * Create a quotation line item.
     */
    protected function createLine(Quotation $quotation, array $data): QuotationLine
    {
        $quantity = $data['quantity'] ?? 1;
        $unitPrice = $data['unit_price'] ?? 0;
        $discountPercentage = $data['discount_percentage'] ?? 0;
        
        $baseTotal = $quantity * $unitPrice;
        $discountAmount = $baseTotal * ($discountPercentage / 100);
        $lineTotal = $baseTotal - $discountAmount;
        
        return QuotationLine::create([
            'quotation_id' => $quotation->id,
            'item_id' => $data['item_id'] ?? null,
            'item_code' => $data['item_code'] ?? null,
            'item_name' => $data['item_name'],
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage,
            'line_total' => $lineTotal,
            'uom' => $data['uom'] ?? 'pcs',
        ]);
    }

    /**
     * Update quotation and recalculate totals.
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data) {
            $fillable = [
                'valid_until',
                'payment_terms_days',
                'shipping_address',
                'shipping_method',
                'terms_conditions',
                'notes',
                'discount_percentage',
            ];
            
            foreach ($fillable as $field) {
                if (isset($data[$field])) {
                    $quotation->$field = $data[$field];
                }
            }
            
            // Update lines if provided
            if (isset($data['lines']) && is_array($data['lines'])) {
                // Clear existing lines
                $quotation->lines()->delete();
                
                // Add new lines
                foreach ($data['lines'] as $lineData) {
                    $this->createLine($quotation, $lineData);
                }
            }
            
            $quotation->recalculateTotals();
            $quotation->save();
            
            return $quotation;
        });
    }

    /**
     * Submit quotation for approval.
     */
    public function submitForApproval(Quotation $quotation): Quotation
    {
        $quotation->status = 'sent';
        $quotation->save();
        
        return $quotation;
    }

    /**
     * Approve quotation.
     */
    public function approveQuotation(Quotation $quotation, int $userId): Quotation
    {
        $quotation->approved_by = $userId;
        $quotation->approved_at = now();
        $quotation->status = 'sent';
        $quotation->save();
        
        return $quotation;
    }

    /**
     * Convert quotation to sales order.
     */
    public function convertToOrder(Quotation $quotation): mixed
    {
        return DB::transaction(function () use ($quotation) {
            // TODO: Create sales order from accepted quotation
            // For now, just mark as converted
            $quotation->status = 'accepted';
            $quotation->save();
            
            return $quotation->id;
        });
    }

    /**
     * Get quotation statistics.
     */
    public function getQuotationStatistics(int $tenantId): array
    {
        $query = \App\Modules\CRM\Models\Quotation::where('tenant_id', $tenantId);
        
        return [
            'total_quotations' => (clone $query)->count(),
            'draft_quotations' => (clone $query)->byStatus('draft')->count(),
            'sent_quotations' => (clone $query)->byStatus('sent')->count(),
            'accepted_quotations' => (clone $query)->byStatus('accepted')->count(),
            'rejected_quotations' => (clone $query)->byStatus('rejected')->count(),
            'conversion_rate' => function () use ($query) {
                $total = (clone $query)->whereNotIn('status', ['draft', 'expired'])->count();
                $accepted = (clone $query)->byStatus('accepted')->count();
                
                return $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
            },
            'total_value' => (clone $query)->whereNotIn('status', ['draft', 'rejected'])->sum('total_amount'),
        ];
    }
}
