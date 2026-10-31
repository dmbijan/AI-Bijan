<?php

namespace App\Modules\POS\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    /**
     * List all POS registers.
     */
    public function index(Request $request)
    {
        return response()->json($this->registerService->listRegisters($this->tenantId()));
    }

    /**
     * Show a single register.
     */
    public function show(Request $request, int $id)
    {
        $register = $this->registerService->getRegister($id, $this->tenantId());

        if (!$register) {
            return response()->json(['message' => 'Register not found.'], 404);
        }

        return response()->json($register);
    }

    /**
     * Create a new POS register.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'warehouse_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('warehouses', 'id')->where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())],
            'is_active' => 'boolean',
        ]);

        try {
            $register = $this->registerService->createRegister(
                $validated,
                $this->tenantId(),
                $request->user()->id
            );

            return response()->json($register, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a register.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'warehouse_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('warehouses', 'id')->where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())],
            'is_active' => 'boolean',
        ]);

        $result = $this->registerService->updateRegister($id, $validated, $this->tenantId());

        if (!$result) {
            return response()->json(['message' => 'Register not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Delete a register.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $success = $this->registerService->deleteRegister($id, $this->tenantId());

            if (!$success) {
                return response()->json(['message' => 'Register not found.'], 404);
            }

            return response()->json(['message' => 'Register deleted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Toggle register active status.
     */
    public function toggleActive(Request $request, int $id)
    {
        $result = $this->registerService->toggleActive($id, $this->tenantId());

        if (!$result) {
            return response()->json(['message' => 'Register not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Get register sales summary.
     */
    public function salesSummary(Request $request, int $id)
    {
        $summary = $this->registerService->getRegisterSalesSummary(
            $id,
            $this->tenantId(),
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($summary);
    }
}
