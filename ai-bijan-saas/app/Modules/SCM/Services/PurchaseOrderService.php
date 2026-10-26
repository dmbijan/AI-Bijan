<?php

namespace App\Modules\SCM\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\SCM\Models\PurchaseOrder;
use App\Modules\SCM\Models\PurchaseOrderLine;
use App\Modules\SCM\Models\GoodsReceivedNote;
use App\Modules\SCM\Models\GoodsReceivedNoteLine;

class PurchaseOrderService
{
    /**
     * Create a new purchase order.
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $poNumber = PurchaseOrder::generatePONumber();
            
            $po = PurchaseOrder::create([
                'tenant_id' => $data['tenant_id'],
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => 0,
                'currency' => $data['currency'] ?? 'MYR',
                'payment_terms_days' => $data['payment_terms_days'] ?? 30,
                'internal_notes' => $data['internal_notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'requested_by' => $data['requested_by'] ?? auth()->id(),
            ]);
            
            // Create line items
            foreach ($data['lines'] as $lineData) {
                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $lineData['item_id'] ?? null,
                    'item_code' => $lineData['item_code'] ?? null,
                    'item_name' => $lineData['item_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity_ordered' => $lineData['quantity_ordered'],
                    'unit_price' => $lineData['unit_price'],
                    'uom' => $lineData['uom'] ?? 'pcs',
                    'quantity_received' => 0,
                    'line_total' => ($lineData['quantity_ordered'] * $lineData['unit_price']),
                    'is_taxable' => $lineData['is_taxable'] ?? false,
                    'tax_rate' => $lineData['tax_rate'] ?? 0,
                    'tax_amount' => 0,
                    'delivery_status' => 0,
                ]);
            }
            
            // Recalculate totals
            $this->recalculateTotals($po);
            
            return $po->fresh()->load(['lines', 'supplier']);
        });
    }

    /**
     * Submit PO for approval (change status from draft to pending_approval).
     */
    public function submitForApproval(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== 'draft') {
            throw new \InvalidArgumentException("Only draft POs can be submitted for approval");
        }
        
        if ($po->lines()->sum('quantity_ordered') === 0) {
            throw new \InvalidArgumentException("PO must have at least one line item with quantity");
        }
        
        $po->status = 'pending_approval';
        $po->save();
        
        return $po->fresh()->load(['lines', 'supplier']);
    }

    /**
     * Approve a pending PO.
     */
    public function approvePurchaseOrder(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== 'pending_approval') {
            throw new \InvalidArgumentException("PO must be in pending_approval status");
        }
        
        $po->status = 'approved';
        $po->approved_by = auth()->id();
        $po->approved_at = now();
        $po->save();
        
        // Auto-create journal entry for accounting
        // Dr: Inventory/Purchase Expense
        // Cr: Accounts Payable
        
        return $po->fresh()->load(['lines', 'supplier', 'approver']);
    }

    /**
     * Reject a pending PO.
     */
    public function rejectPurchaseOrder(PurchaseOrder $po, string $reason = ''): PurchaseOrder
    {
        if ($po->status !== 'pending_approval') {
            throw new \InvalidArgumentException("PO must be in pending_approval status");
        }
        
        $po->status = 'draft';
        $po->save();
        
        return $po->fresh()->load(['lines', 'supplier']);
    }

    /**
     * Send PO to supplier.
     */
    public function sendToSupplier(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== 'approved') {
            throw new \InvalidArgumentException("Only approved POs can be sent to supplier");
        }
        
        $po->status = 'sent';
        $po->tracking_number = $po->tracking_number ?? null;
        $po->save();
        
        return $po->fresh()->load(['lines', 'supplier']);
    }

    /**
     * Receive goods against a PO (create GRN).
     */
    public function receiveGoods(PurchaseOrder $po, array $receivedItems): GoodsReceivedNote
    {
        return DB::transaction(function () use ($po, $receivedItems) {
            if (!in_array($po->status, ['sent', 'partially_received'])) {
                throw new \InvalidArgumentException("Can only receive goods for sent or partially received POs");
            }
            
            // Create GRN
            $grn = GoodsReceivedNote::create([
                'tenant_id' => $po->tenant_id,
                'purchase_order_id' => $po->id,
                'grn_number' => GoodsReceivedNote::generateGRNNumber(),
                'received_date' => now(),
                'condition' => 'good',
                'received_by' => auth()->id(),
            ]);
            
            // Process each received item
            foreach ($receivedItems as $item) {
                $poLine = $po->lines()->find($item['purchase_order_line_id']);
                
                if (!$poLine) {
                    continue;
                }
                
                $remaining = $poLine->quantity_ordered - $poLine->quantity_received;
                $received = min($item['quantity_received'], $remaining);
                $rejected = $item['quantity_rejected'] ?? 0;
                
                // Create GRN line
                GoodsReceivedNoteLine::create([
                    'goods_received_note_id' => $grn->id,
                    'purchase_order_line_id' => $poLine->id,
                    'item_code' => $poLine->item_code,
                    'item_name' => $poLine->item_name,
                    'ordered_quantity' => $poLine->quantity_ordered,
                    'received_quantity' => $received,
                    'rejected_quantity' => $rejected,
                    'uom' => $poLine->uom,
                ]);
                
                // Update PO line
                $poLine->quantity_received += $received;
                $poLine->delivery_status = ($poLine->quantity_received >= $poLine->quantity_ordered) ? 2 : 1;
                $poLine->save();
            }
            
            // Update PO status
            if ($po->isFullyReceived()) {
                $po->status = 'fully_received';
                $po->actual_delivery_date = now();
            } else {
                $po->status = 'partially_received';
            }
            $po->save();
            
            return $grn->fresh()->load(['lines.poLine']);
        });
    }

    /**
     * Cancel a PO.
     */
    public function cancelPurchaseOrder(PurchaseOrder $po): void
    {
        if (!in_array($po->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            throw new \InvalidArgumentException("Cannot cancel a {$po->status} PO");
        }
        
        $po->status = 'cancelled';
        $po->save();
    }

    /**
     * Recalculate PO totals.
     */
    protected function recalculateTotals(PurchaseOrder $po): void
    {
        $subtotal = $po->lines()->sum('line_total');
        $taxAmount = $po->lines()->sum('tax_amount');
        $shippingCost = $po->shipping_cost ?? 0;
        
        $po->subtotal = round($subtotal, 2);
        $po->tax_amount = round($taxAmount, 2);
        $po->total_amount = round($subtotal + $taxAmount + $shippingCost, 2);
        $po->save();
    }
}
