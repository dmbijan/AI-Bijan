<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SlotController extends Controller
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
    protected SlotService $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * List available slots for a service/provider on a date.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:booking_services,id',
            'provider_id' => 'required|exists:booking_service_providers,id',
            'date' => 'required|date',
        ]);

        $slots = $this->slotService->getAvailableSlots(
            $this->tenantId(),
            $validated['service_id'],
            $validated['provider_id'],
            $validated['date']
        );

        return response()->json($slots);
    }

    /**
     * Generate slots for a service/provider on a date.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:booking_services,id',
            'provider_id' => 'required|exists:booking_service_providers,id',
            'date' => 'required|date',
            'business_hours' => 'nullable|array',
            'buffer_minutes' => 'nullable|integer|min:0|max:60',
        ]);

        try {
            $slots = $this->slotService->generateSlots(
                $this->tenantId(),
                $validated['service_id'],
                $validated['provider_id'],
                $validated['date'],
                $validated['business_hours'] ?? config('booking.business_hours'),
                $validated['buffer_minutes'] ?? config('booking.buffer_time_minutes', 15)
            );

            return response()->json(['generated' => count($slots)], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Book a slot.
     */
    public function book(Request $request, int $id)
    {
        try {
            $slot = $this->slotService->bookSlot($id, $this->tenantId());

            if (!$slot) {
                return response()->json(['message' => 'Slot not found.'], 404);
            }

            return response()->json($slot);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a slot booking.
     */
    public function cancel(Request $request, int $id)
    {
        $slot = $this->slotService->cancelSlot($id, $this->tenantId());

        if (!$slot) {
            return response()->json(['message' => 'Slot not found.'], 404);
        }

        return response()->json($slot);
    }

    /**
     * Block a slot.
     */
    public function block(Request $request, int $id)
    {
        $slot = $this->slotService->blockSlot($id, $this->tenantId());

        if (!$slot) {
            return response()->json(['message' => 'Slot not found.'], 404);
        }

        return response()->json($slot);
    }

    /**
     * Get slots by date range.
     */
    public function byDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'provider_id' => 'nullable|exists:booking_service_providers,id',
        ]);

        $slots = $this->slotService->getSlotsByDateRange(
            $this->tenantId(),
            $validated['start_date'],
            $validated['end_date'],
            $validated['provider_id'] ?? null
        );

        return response()->json($slots);
    }
}
