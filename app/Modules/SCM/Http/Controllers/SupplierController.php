<?php

namespace App\Modules\SCM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SCM\Services\SupplierManagementService;
use Illuminate\Http\Request;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    protected $supplierService;

    /**
     * ID tenant semasa — MESTI dari konteks, BUKAN header klien.
     * ⚠️ 26 Okt 2026: kod lama guna `$request->header('X-Tenant-ID')` → IDOR.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    public function __construct(SupplierManagementService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * List suppliers.
     * GET /api/scm/suppliers
     */
    public function index(Request $request)
    {
        $query = DB::table('suppliers')
            ->where('tenant_id', $this->tenantId())
            ->with(['contacts'])
            ->orderBy('company_name');
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('approved_only')) {
            $query->where('is_approved', true);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('company_name', 'like', "%{$request->search}%")
                  ->orWhere('supplier_code', 'like', "%{$request->search}%")
                  ->orWhere('ssm_number', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->paginate(20));
    }

    /**
     * Get single supplier.
     * GET /api/scm/suppliers/{id}
     */
    public function show($id, Request $request)
    {
        $supplier = DB::table('suppliers')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->with(['contacts', 'purchaseOrders'])
            ->first();
        
        if (! $supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        
        return response()->json($supplier);
    }

    /**
     * Create new supplier.
     * POST /api/scm/suppliers
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_name_bm' => 'nullable|string|max:255',
            'ssm_number' => 'nullable|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'sst_number' => 'nullable|string|max:50',
            'tax_identification' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:20',
            'category' => 'nullable|in:raw_materials,services,equipment,office_supplies,logistics,other',
            'payment_terms_days' => 'nullable|integer|min:1',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'primary_contact' => 'nullable|array',
            'primary_contact.name' => 'nullable|string|max:255',
            'primary_contact.email' => 'nullable|email|max:255',
            'primary_contact.phone' => 'nullable|string|max:50',
        ]);
        
        $validated['tenant_id'] = $this->tenantId();
        
        try {
            $supplier = $this->supplierService->createSupplier($validated);
            return response()->json($supplier, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update supplier.
     * PUT /api/scm/suppliers/{id}
     */
    public function update(Request $request, $id)
    {
        $supplier = DB::table('suppliers')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();
        
        if (! $supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'company_name_bm' => 'nullable|string|max:255',
            'ssm_number' => 'nullable|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'sst_number' => 'nullable|string|max:50',
            'tax_identification' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:20',
            'category' => 'nullable|in:raw_materials,services,equipment,office_supplies,logistics,other',
            'status' => 'nullable|in:active,inactive,blacklisted',
            'payment_terms_days' => 'nullable|integer|min:1',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        
        try {
            $updated = $this->supplierService->updateSupplier(\App\Modules\SCM\Models\Supplier::find($id), $validated);
            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve a supplier.
     * POST /api/scm/suppliers/{id}/approve
     */
    public function approve($id, Request $request)
    {
        $supplier = \App\Modules\SCM\Models\Supplier::find($id);
        
        if (! $supplier || $supplier->tenant_id !== $this->tenantId()) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        
        try {
            $approved = $this->supplierService->approveSupplier($supplier);
            return response()->json($approved);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
