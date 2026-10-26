<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    /**
     * Get all vouchers for a tenant.
     */
    public function getVouchers(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Voucher::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['active_only'])) {
            $query->active();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a voucher by ID.
     */
    public function getVoucherById(int $voucherId, int $tenantId): ?Voucher
    {
        return Voucher::where('id', $voucherId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a voucher by code.
     */
    public function getVoucherByCode(string $code, int $tenantId): ?Voucher
    {
        return Voucher::where('code', $code)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new voucher.
     */
    public function createVoucher(array $data): Voucher
    {
        if (empty($data['code'])) {
            $data['code'] = Voucher::generateCode();
        }

        return DB::transaction(function () use ($data) {
            return Voucher::create($data);
        });
    }

    /**
     * Update a voucher.
     */
    public function updateVoucher(Voucher $voucher, array $data): Voucher
    {
        return DB::transaction(function () use ($voucher, $data) {
            $voucher->update($data);
            return $voucher->fresh();
        });
    }

    /**
     * Delete a voucher.
     */
    public function deleteVoucher(Voucher $voucher): bool
    {
        return $voucher->delete();
    }

    /**
     * Activate a voucher.
     */
    public function activateVoucher(Voucher $voucher): Voucher
    {
        return $voucher->update(['status' => 'active']) ? $voucher->fresh() : null;
    }

    /**
     * Deactivate a voucher.
     */
    public function deactivateVoucher(Voucher $voucher): Voucher
    {
        return $voucher->update(['status' => 'paused']) ? $voucher->fresh() : null;
    }

    /**
     * Validate a voucher code.
     */
    public function validateVoucher(string $code, float $purchaseAmount, ?array $productIds = null): array
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Invalid voucher code.'];
        }

        if (!$voucher->isValid()) {
            return ['valid' => false, 'message' => 'Voucher is not active or has expired.'];
        }

        if ($voucher->min_purchase && $purchaseAmount < $voucher->min_purchase) {
            return [
                'valid' => false,
                'message' => sprintf('Minimum purchase of $%.2f required.', $voucher->min_purchase),
            ];
        }

        // Check product applicability
        if (!empty($voucher->applicable_products) && !empty($productIds)) {
            $matchingProducts = array_intersect($voucher->applicable_products, $productIds);
            if (empty($matchingProducts)) {
                return ['valid' => false, 'message' => 'Voucher not applicable to items in cart.'];
            }
        }

        // Calculate discount
        $discount = 0;
        switch ($voucher->type) {
            case 'percentage':
                $discount = ($purchaseAmount * $voucher->value) / 100;
                if ($voucher->max_discount && $discount > $voucher->max_discount) {
                    $discount = $voucher->max_discount;
                }
                break;
            case 'fixed':
                $discount = $voucher->value;
                if ($discount > $purchaseAmount) {
                    $discount = $purchaseAmount;
                }
                break;
            case 'free_shipping':
                $discount = 0; // Handle shipping separately
                break;
        }

        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount' => round($discount, 2),
            'message' => 'Voucher applied successfully.',
        ];
    }

    /**
     * Use a voucher.
     */
    public function useVoucher(Voucher $voucher, int $contactId, int $orderId, float $amountUsed): void
    {
        DB::transaction(function () use ($voucher, $contactId, $orderId, $amountUsed) {
            $voucher->incrementUsage();

            $voucher->contacts()->attach($contactId, [
                'used_at' => now(),
                'order_id' => $orderId,
                'amount_used' => $amountUsed,
            ]);
        });
    }

    /**
     * Generate multiple codes.
     */
    public function generateMultipleCodes(int $count): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = self::generateCode();
        }
        return $codes;
    }

    /**
     * Get voucher usage statistics.
     */
    public function getVoucherStats(int $tenantId): array
    {
        $totalVouchers = Voucher::where('tenant_id', $tenantId)->count();
        $activeVouchers = Voucher::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();
        $totalUsage = Voucher::where('tenant_id', $tenantId)
            ->sum('used_count');

        return [
            'total' => $totalVouchers,
            'active' => $activeVouchers,
            'total_usage' => $totalUsage,
        ];
    }
}
