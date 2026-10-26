<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Models\BookingSlot;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SlotService
{
    /**
     * Generate slots for a service and provider on a specific date.
     */
    public function generateSlots(
        int $tenantId,
        int $serviceId,
        int $providerId,
        string $date,
        array $businessHours = [],
        int $bufferMinutes = 15
    ): array {
        $slotDuration = config('booking.slot_durations.30', 30); // Default 30 min slots
        
        $startTime = Carbon::parse($date . ' ' . ($businessHours['start'] ?? '09:00'));
        $endTime = Carbon::parse($date . ' ' . ($businessHours['end'] ?? '18:00'));
        
        $slots = [];
        $currentTime = clone $startTime;
        
        while ($currentTime->copy()->addMinutes($slotDuration)->lte($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDuration + $bufferMinutes);
            
            if ($slotEnd->gt($endTime)) {
                break;
            }
            
            $slots[] = BookingSlot::create([
                'tenant_id' => $tenantId,
                'service_id' => $serviceId,
                'provider_id' => $providerId,
                'slot_date' => $date,
                'start_time' => $currentTime->format('Y-m-d H:i:s'),
                'end_time' => $slotEnd->format('Y-m-d H:i:s'),
                'status' => 'available',
                'capacity' => 1,
                'booked_count' => 0,
            ]);
            
            $currentTime = $slotEnd;
        }
        
        return $slots;
    }

    /**
     * List available slots for a service/provider on a date.
     */
    public function getAvailableSlots(
        int $tenantId,
        int $serviceId,
        int $providerId,
        string $date
    ): array {
        $slots = BookingSlot::where('tenant_id', $tenantId)
            ->where('service_id', $serviceId)
            ->where('provider_id', $providerId)
            ->onDate($date)
            ->byStatus('available')
            ->get();

        return $slots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'booked_count' => $slot->booked_count,
                'is_available' => $slot->isAvailable(),
            ];
        })->toArray();
    }

    /**
     * Book a slot.
     */
    public function bookSlot(int $slotId, int $tenantId): ?BookingSlot
    {
        $slot = BookingSlot::where('id', $slotId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$slot) {
            return null;
        }

        if (!$slot->isAvailable()) {
            throw ValidationException::withMessages([
                'slot' => 'This slot is no longer available.',
            ]);
        }

        $slot->incrementBooked();
        $slot->status = 'booked';
        $slot->save();

        return $slot;
    }

    /**
     * Cancel a slot booking.
     */
    public function cancelSlot(int $slotId, int $tenantId): ?BookingSlot
    {
        $slot = BookingSlot::where('id', $slotId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$slot) {
            return null;
        }

        $slot->decrementBooked();
        
        if ($slot->booked_count <= 0) {
            $slot->booked_count = 0;
            $slot->status = 'available';
        }
        
        $slot->save();

        return $slot;
    }

    /**
     * Block a slot (for maintenance or break).
     */
    public function blockSlot(int $slotId, int $tenantId): ?BookingSlot
    {
        $slot = BookingSlot::where('id', $slotId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$slot) {
            return null;
        }

        $slot->status = 'blocked';
        $slot->save();

        return $slot;
    }

    /**
     * Get slots by date range.
     */
    public function getSlotsByDateRange(
        int $tenantId,
        string $startDate,
        string $endDate,
        ?int $providerId = null
    ): array {
        $query = BookingSlot::where('tenant_id', $tenantId)
            ->where('slot_date', '>=', $startDate)
            ->where('slot_date', '<=', $endDate);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        $slots = $query->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        return $slots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'service_id' => $slot->service_id,
                'provider_id' => $slot->provider_id,
                'slot_date' => $slot->slot_date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => $slot->status,
                'capacity' => $slot->capacity,
                'booked_count' => $slot->booked_count,
            ];
        })->toArray();
    }
}
