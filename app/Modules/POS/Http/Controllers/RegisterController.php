<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
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
        return response()->json($this->registerService->listRegisters($request->tenant_id));
    }

    /**
     * Show a single register.
     */
    public function show(Request $request, int $id)
    {
        $register = $this->registerService->getRegister($id, $request->tenant_id);

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
            'is_active' => 'boolean',
        ]);

        try {
            $register = $this->registerService->createRegister(
                $validated,
                $request->tenant_id,
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
            'is_active' => 'boolean',
        ]);

        $result = $this->registerService->updateRegister($id, $validated, $request->tenant_id);

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
            $success = $this->registerService->deleteRegister($id, $request->tenant_id);

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
        $result = $this->registerService->toggleActive($id, $request->tenant_id);

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
            $request->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($summary);
    }
}
