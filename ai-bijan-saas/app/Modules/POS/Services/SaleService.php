<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\CashShift;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleItem;
use App\Modules\POS\Models\POSSalePayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * List sales with filters.
     */
    public function listSales(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        $query = POSSale::forTenant($tenantId)
            ->with(['register', 'cashier', 'customer', 'items']);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['register_id'])) {
            $query->where('register_id', $filters['register_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('sale_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('sale_date', '<=', $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sale_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Get a single sale with all details.
     */
    public function getSale(int $saleId, int $tenantId): ?POSSale
    {
        return POSSale::forTenant($tenantId)
            ->with(['register', 'cashier', 'customer', 'items', 'payments', 'discounts'])
            ->find($saleId);
    }

    /**
     * Create a draft sale.
     */
    public function createDraft(array $data, int $tenantId, int $registerId, int $userId): POSSale
    {
        return DB::transaction(function () use ($data, $tenantId, $registerId, $userId) {
            $sale = POSSale::create([
                'tenant_id' => $tenantId,
                'register_id' => $registerId,
                'user_id' => $userId,
                'sale_no' => POSSale::generateSaleNo(),
                'status' => 'draft',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'amount_paid' => 0,
                'change_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
            ]);

            return $sale;
        });
    }

    /**
     * Add item to a draft sale.
     */
    public function addItem(int $saleId, array $data, int $tenantId): POSSaleItem
    {
        $sale = $this->getSale($saleId, $tenantId);

        if (! $sale || $sale->status !== 'draft') {
            throw ValidationException::withMessages([
                'sale_id' => ['Cannot add items to a non-draft sale.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $data) {
            // Calculate line totals
            $unitPrice = $data['unit_price'] ?? 0;
            $quantity = $data['quantity'] ?? 1;
            $discountPct = $data['discount_percentage'] ?? 0;
            $discountAmt = $data['discount_amount'] ?? 0;
            $taxRate = $data['tax_rate'] ?? 0;

            $lineSubtotal = $unitPrice * $quantity;
            $lineDiscount = max($discountAmt, $lineSubtotal * ($discountPct / 100));
            $lineTaxable = $lineSubtotal - $lineDiscount;
            $lineTax = $lineTaxable * ($taxRate / 100);
            $lineTotal = $lineTaxable + $lineTax;

            $item = POSSaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $data['product_id'] ?? null,
                'product_name' => $data['product_name'] ?? 'Unknown Product',
                'product_sku' => $data['product_sku'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percentage' => $discountPct,
                'discount_amount' => $lineDiscount,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ]);

            // Recalculate sale totals
            $this->recalculateSale($sale);

            return $item;
        });
    }

    /**
     * Remove item from a draft sale.
     */
    public function removeItem(int $itemId, int $tenantId): bool
    {
        $item = POSSaleItem::find($itemId);

        if (! $item) {
            return false;
        }

        $sale = $this->getSale($item->sale_id, $item->sale->tenant_id);
        $deleted = $item->delete();

        if ($sale && $sale->status === 'draft') {
            $this->recalculateSale($sale);
        }

        return $deleted;
    }

    /**
     * Update an item in a draft sale.
     */
    public function updateItem(int $itemId, array $data, int $tenantId): ?POSSaleItem
    {
        $item = POSSaleItem::find($itemId);

        if (! $item) {
            return null;
        }

        $sale = $this->getSale($item->sale_id, $item->sale->tenant_id);
        if ($sale && $sale->status !== 'draft') {
            throw ValidationException::withMessages([
                'base' => ['Cannot update items in a non-draft sale.'],
            ]);
        }

        $item->update($data);

        if ($sale) {
            $this->recalculateSale($sale);
        }

        return $item;
    }

    /**
     * Complete a sale (finalize transaction).
     */
    public function completeSale(int $saleId, array $completionData, int $tenantId): POSSale
    {
        $sale = $this->getSale($saleId, $tenantId);

        if (! $sale || $sale->status !== 'draft') {
            throw ValidationException::withMessages([
                'sale_id' => ['Sale not found or not in draft status.'],
            ]);
        }

        if ($sale->items()->count() === 0) {
            throw ValidationException::withMessages([
                'base' => ['Cannot complete a sale without items.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $completionData) {
            // Set completion data
            $sale->status = 'completed';
            $sale->completed_at = now();
            $sale->sale_date = now()->format('Y-m-d');

            // Handle payments
            $payments = $completionData['payments'] ?? [];
            $totalPaid = 0;

            foreach ($payments as $payment) {
                POSSalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference_number' => $payment['reference'] ?? null,
                    'payment_details' => $payment['details'] ?? null,
                ]);

                $totalPaid += $payment['amount'];
            }

            $sale->amount_paid = $totalPaid;
            $sale->change_amount = $sale->calculateChange();

            // Auto-set cash shift if register has open shift
            if (! $sale->cash_shift_id) {
                $openShift = CashShift::forTenant($sale->tenant_id)
                    ->where('register_id', $sale->register_id)
                    ->where('status', 'open')
                    ->first();

                if ($openShift) {
                    $sale->cash_shift_id = $openShift->id;
                }
            }

            $sale->save();

            // Deduct inventory if products linked
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $this->deductInventory($item->product_id, $item->quantity);
                }
            }

            // Fire event
            event('pos.sale.completed', [$sale]);

            return $sale;
        });
    }

    /**
     * Checkout satu jalan: draf + item + selesai dalam satu transaksi.
     * Dipakai kaunter (termasuk segerak jualan luar talian); `sold_at` = masa jualan asal di peranti.
     */
    public function checkout(array $data, int $tenantId, int $userId): POSSale
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            $sale = $this->createDraft($data, $tenantId, (int) $data['register_id'], $userId);

            foreach ($data['items'] as $item) {
                $this->addItem($sale->id, $item, $tenantId);
            }

            $sale = $this->completeSale($sale->id, $data, $tenantId);

            if (! empty($data['sold_at'])) {
                $soldAt = Carbon::parse($data['sold_at']);
                $sale->completed_at = $soldAt;
                $sale->sale_date = $soldAt->format('Y-m-d');
                $sale->save();
            }

            return $sale->fresh(['items', 'payments']);
        });
    }

    /**
     * Cancel a draft sale.
     */
    public function cancelSale(int $saleId, int $tenantId): bool
    {
        $sale = $this->getSale($saleId, $tenantId);

        if (! $sale || $sale->status !== 'draft') {
            return false;
        }

        return $sale->update(['status' => 'cancelled']);
    }

    /**
     * Refund a completed sale.
     */
    public function refundSale(int $saleId, array $refundData, int $tenantId, int $userId): POSSaleReturn
    {
        $sale = $this->getSale($saleId, $tenantId);

        if (! $sale || $sale->status !== 'completed') {
            throw ValidationException::withMessages([
                'sale_id' => ['Only completed sales can be refunded.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $refundData, $userId) {
            $return = POSSaleReturn::create([
                'tenant_id' => $tenantId,
                'sale_id' => $sale->id,
                'returned_by' => $userId,
                'return_no' => POSSaleReturn::generateReturnNo(),
                'status' => 'pending',
                'return_amount' => $refundData['amount'] ?? $sale->total_amount,
                'reason' => $refundData['reason'] ?? 'Customer return',
            ]);

            // Update sale status
            $sale->update([
                'status' => 'partially_refunded',
            ]);

            return $return;
        });
    }

    /**
     * Recalculate sale totals.
     */
    protected function recalculateSale(POSSale $sale): void
    {
        $items = $sale->items()->get(); // baca semula; relasi yang dimuat awal tak ada item baharu

        $subtotal = $items->sum(fn ($item) => $item->quantity * $item->unit_price);
        $totalDiscount = $items->sum('discount_amount');
        $totalTax = $items->sum('tax_amount');
        $totalAmount = $subtotal - $totalDiscount + $totalTax;

        $sale->subtotal = $subtotal;
        $sale->discount_amount = $totalDiscount;
        $sale->tax_amount = $totalTax;
        $sale->total_amount = $totalAmount;

        $sale->save();
    }

    /**
     * Deduct inventory for a product.
     */
    protected function deductInventory(int $productId, int $quantity): void
    {
        // Integration with Inventory module
        // This would call InventoryService to reduce stock
    }

    /**
     * Get POS summary statistics.
     */
    public function getSummary(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = POSSale::forTenant($tenantId)->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('sale_date', '<=', $endDate);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_discounts' => $sales->sum('discount_amount'),
            'total_tax' => $sales->sum('tax_amount'),
            'avg_transaction' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'by_payment_method' => POSSalePayment::join('pos_sales', 'pos_sale_payments.sale_id', '=', 'pos_sales.id')
                ->where('pos_sales.tenant_id', $tenantId)
                ->where('pos_sales.status', 'completed')
                ->when($startDate, fn ($q) => $q->where('pos_sales.sale_date', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('pos_sales.sale_date', '<=', $endDate))
                ->selectRaw('pos_sale_payments.payment_method, sum(pos_sale_payments.amount) as total')
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method'),
            'daily_breakdown' => $sales->groupBy('sale_date')
                ->map(fn ($day) => [
                    'count' => $day->count(),
                    'revenue' => $day->sum('total_amount'),
                ]),
        ];
    }
}
