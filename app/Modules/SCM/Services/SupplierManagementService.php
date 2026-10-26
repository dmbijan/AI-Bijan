<?php

namespace App\Modules\SCM\Services;

use App\Modules\SCM\Models\Supplier;

class SupplierManagementService
{
    /**
     * Create a new supplier.
     */
    public function createSupplier(array $data): Supplier
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $supplierCode = Supplier::generateSupplierCode();
            
            $supplier = Supplier::create([
                'tenant_id' => $data['tenant_id'],
                'supplier_code' => $supplierCode,
                'company_name' => $data['company_name'],
                'company_name_bm' => $data['company_name_bm'] ?? null,
                'ssm_number' => $data['ssm_number'] ?? null,
                'gst_number' => $data['gst_number'] ?? null,
                'sst_number' => $data['sst_number'] ?? null,
                'tax_identification' => $data['tax_identification'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'category' => $data['category'] ?? 'other',
                'status' => 'active',
                'payment_terms_days' => $data['payment_terms_days'] ?? 30,
                'currency' => $data['currency'] ?? 'MYR',
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'withholding_tax_rate' => $data['withholding_tax_rate'] ?? 0,
            ]);
            
            // Create primary contact if provided
            if (!empty($data['primary_contact'])) {
                $pc = $data['primary_contact'];
                $supplier->contacts()->create([
                    'name' => $pc['name'] ?? $data['contact_person'],
                    'position' => $pc['position'] ?? null,
                    'email' => $pc['email'] ?? $data['contact_email'],
                    'phone' => $pc['phone'] ?? $data['contact_phone'],
                    'is_primary' => true,
                ]);
            }
            
            return $supplier->fresh()->load('contacts');
        });
    }

    /**
     * Update supplier details.
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update(array_filter($data, fn($v) => $v !== null));
        
        return $supplier->fresh()->load('contacts');
    }

    /**
     * Approve a supplier for purchasing.
     */
    public function approveSupplier(Supplier $supplier): Supplier
    {
        $supplier->is_approved = true;
        $supplier->approved_by = auth()->id();
        $supplier->approved_at = now();
        $supplier->save();
        
        return $supplier->fresh();
    }

    /**
     * Record delivery performance.
     */
    public function recordDeliveryPerformance(int $supplierId, bool $onTime): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        if ($onTime) {
            $supplier->increment('on_time_delivery_count');
        } else {
            $supplier->increment('late_delivery_count');
        }
        
        $supplier->save();
    }

    /**
     * Record quality issue.
     */
    public function recordQualityIssue(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->increment('quality_issues_count');
        $supplier->save();
    }
}
