<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterService
{
    /**
     * List all POS registers for a tenant.
     */
    public function listRegisters(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return POSRegister::forTenant($tenantId)
            ->withCount(['sales', 'cashShifts'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a single register by ID.
     */
    public function getRegister(int $registerId, int $tenantId): ?POSRegister
    {
        return POSRegister::forTenant($tenantId)
            ->with(['cashShifts', 'sales'])
            ->find($registerId);
    }

    /**
     * Create a new POS register.
     */
    public function createRegister(array $data, int $tenantId, int $userId): POSRegister
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            $data['tenant_id'] = $tenantId;
            $data['code'] = POSRegister::generateCode();
            $data['created_by'] = $userId;
            $data['is_active'] = $data['is_active'] ?? true;

            return POSRegister::create($data);
        });
    }

    /**
     * Update a register.
     */
    public function updateRegister(int $registerId, array $data, int $tenantId): ?POSRegister
    {
        $register = $this->getRegister($registerId, $tenantId);

        if (!$register) {
            return null;
        }

        $register->update($data);

        return $register;
    }

    /**
     * Delete a register (only if no sales have been made).
     */
    public function deleteRegister(int $registerId, int $tenantId): bool
    {
        $register = $this->getRegister($registerId, $tenantId);

        if (!$register) {
            return false;
        }

        // Cannot delete if there are sales
        if ($register->sales()->count() > 0) {
            throw ValidationException::withMessages([
                'base' => ['Cannot delete a register that has sales records.'],
            ]);
        }

        return $register->delete();
    }

    /**
     * Toggle register active status.
     */
    public function toggleActive(int $registerId, int $tenantId): ?POSRegister
    {
        $register = $this->getRegister($registerId, $tenantId);

        if (!$register) {
            return null;
        }

        $register->update(['is_active' => !$register->is_active]);

        return $register;
    }

    /**
     * Get register sales summary.
     */
    public function getRegisterSalesSummary(int $registerId, int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $register = $this->getRegister($registerId, $tenantId);

        if (!$register) {
            return [];
        }

        $query = \App\Modules\POS\Models\POSSale::where('register_id', $registerId)
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('sale_date', '<=', $endDate);
        }

        $sales = $query->get();

        return [
            'register' => $register,
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_discounts' => $sales->sum('discount_amount'),
            'avg_transaction' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
        ];
    }
}
