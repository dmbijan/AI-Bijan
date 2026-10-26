<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * List all appointments for a tenant.
     */
    public function listAppointments(int $tenantId, array $filters = []): array
    {
        $query = Appointment::where('tenant_id', $tenantId)
            ->with(['service', 'provider', 'customer']);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('appointment_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cust) use ($search) {
                      $cust->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->orderBy('start_time', 'desc')->get();

        return $appointments->map(function ($apt) {
            return [
                'id' => $apt->id,
                'appointment_no' => $apt->appointment_no,
                'service' => $apt->service ? [
                    'id' => $apt->service->id,
                    'name' => $apt->service->name,
                    'duration_minutes' => $apt->service->duration_minutes,
                ] : null,
                'provider' => $apt->provider ? [
                    'id' => $apt->provider->id,
                    'name' => $apt->provider->name,
                ] : null,
                'customer' => $apt->customer ? [
                    'id' => $apt->customer->id,
                    'name' => $apt->customer->name,
                    'phone' => $apt->customer->phone,
                ] : null,
                'status' => $apt->status,
                'start_time' => $apt->start_time,
                'end_time' => $apt->end_time,
                'notes' => $apt->notes,
                'reminder_sent' => $apt->reminder_sent,
                'is_upcoming' => $apt->isUpcoming(),
                'is_overdue' => $apt->isOverdue(),
            ];
        })->toArray();
    }

    /**
     * Get a single appointment with full details.
     */
    public function getAppointment(int $appointmentId, int $tenantId): ?array
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->with(['service', 'provider', 'customer', 'user', 'reminders'])
            ->first();

        if (!$appointment) {
            return null;
        }

        return [
            'id' => $appointment->id,
            'appointment_no' => $appointment->appointment_no,
            'service' => $appointment->service ? [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'duration_minutes' => $appointment->service->duration_minutes,
                'price' => $appointment->service->price,
            ] : null,
            'provider' => $appointment->provider ? [
                'id' => $appointment->provider->id,
                'name' => $appointment->provider->name,
                'specialization' => $appointment->provider->specialization,
            ] : null,
            'customer' => $appointment->customer ? [
                'id' => $appointment->customer->id,
                'name' => $appointment->customer->name,
                'phone' => $appointment->customer->phone,
                'email' => $appointment->customer->email,
            ] : null,
            'status' => $appointment->status,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'actual_start_time' => $appointment->actual_start_time,
            'actual_end_time' => $appointment->actual_end_time,
            'notes' => $appointment->notes,
            'internal_notes' => $appointment->internal_notes,
            'reminder_sent' => $appointment->reminder_sent,
            'cancelled_at' => $appointment->cancelled_at,
            'cancellation_reason' => $appointment->cancellation_reason,
        ];
    }

    /**
     * Create a new appointment.
     */
    public function createAppointment(array $data, int $tenantId, int $userId): Appointment
    {
        // Validate slot availability if provided
        if (isset($data['slot_id'])) {
            $slot = \App\Modules\Booking\Models\BookingSlot::where('id', $data['slot_id'])
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$slot || !$slot->isAvailable()) {
                throw ValidationException::withMessages([
                    'slot_id' => 'Selected slot is not available.',
                ]);
            }
        }

        // Generate appointment number
        $data['appointment_no'] = Appointment::generateAppointmentNo();
        $data['tenant_id'] = $tenantId;
        $data['user_id'] = $userId;
        $data['status'] = $data['status'] ?? 'pending';

        $appointment = Appointment::create($data);

        // Book the slot if provided
        if (isset($data['slot_id'])) {
            app(SlotService::class)->bookSlot($data['slot_id'], $tenantId);
        }

        // Schedule reminders
        $this->scheduleReminders($appointment, $tenantId);

        return $appointment;
    }

    /**
     * Update an appointment.
     */
    public function updateAppointment(int $appointmentId, array $data, int $tenantId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        // Prevent updating completed/cancelled appointments
        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            throw ValidationException::withMessages([
                'status' => 'Cannot update a completed or cancelled appointment.',
            ]);
        }

        $appointment->update($data);

        return $appointment;
    }

    /**
     * Confirm an appointment.
     */
    public function confirmAppointment(int $appointmentId, int $tenantId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (!in_array($appointment->status, ['pending', 'draft'])) {
            throw ValidationException::withMessages([
                'status' => 'Only pending or draft appointments can be confirmed.',
            ]);
        }

        $appointment->status = 'confirmed';
        $appointment->save();

        // Send confirmation reminder
        $this->sendReminder($appointment, 'confirmed');

        return $appointment;
    }

    /**
     * Start an appointment.
     */
    public function startAppointment(int $appointmentId, int $tenantId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (!in_array($appointment->status, ['confirmed', 'in_progress'])) {
            throw ValidationException::withMessages([
                'status' => 'Only confirmed appointments can be started.',
            ]);
        }

        $appointment->status = 'in_progress';
        $appointment->actual_start_time = now();
        $appointment->save();

        return $appointment;
    }

    /**
     * Complete an appointment.
     */
    public function completeAppointment(int $appointmentId, int $tenantId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (!in_array($appointment->status, ['confirmed', 'in_progress'])) {
            throw ValidationException::withMessages([
                'status' => 'Only confirmed or in-progress appointments can be completed.',
            ]);
        }

        $appointment->status = 'completed';
        $appointment->actual_end_time = now();
        $appointment->save();

        return $appointment;
    }

    /**
     * Cancel an appointment.
     */
    public function cancelAppointment(int $appointmentId, array $data, int $tenantId, int $userId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => 'Cannot cancel a completed or cancelled appointment.',
            ]);
        }

        $appointment->status = 'cancelled';
        $appointment->cancelled_at = now();
        $appointment->cancelled_by = $userId;
        $appointment->cancellation_reason = $data['reason'] ?? null;
        $appointment->save();

        // Free up the slot if exists
        if ($appointment->slot_id) {
            app(SlotService::class)->cancelSlot($appointment->slot_id, $tenantId);
        }

        return $appointment;
    }

    /**
     * Mark no-show.
     */
    public function markNoShow(int $appointmentId, int $tenantId): ?Appointment
    {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (!in_array($appointment->status, ['confirmed', 'pending'])) {
            throw ValidationException::withMessages([
                'status' => 'Only confirmed or pending appointments can be marked as no-show.',
            ]);
        }

        $appointment->status = 'no_show';
        $appointment->actual_end_time = now();
        $appointment->save();

        return $appointment;
    }

    /**
     * Reschedule an appointment.
     */
    public function rescheduleAppointment(
        int $appointmentId,
        array $data,
        int $tenantId,
        int $userId
    ): ?Appointment {
        $appointment = Appointment::where('id', $appointmentId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$appointment) {
            return null;
        }

        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => 'Cannot reschedule a completed or cancelled appointment.',
            ]);
        }

        // Save old slot if exists
        if ($appointment->slot_id) {
            app(SlotService::class)->cancelSlot($appointment->slot_id, $tenantId);
        }

        // Book new slot if provided
        $newSlotId = $data['slot_id'] ?? null;
        if ($newSlotId) {
            app(SlotService::class)->bookSlot($newSlotId, $tenantId);
        }

        $appointment->status = 'rescheduled';
        $appointment->rescheduled_from = $appointment->id;
        $appointment->start_time = $data['start_time'];
        $appointment->end_time = $data['end_time'];
        if ($newSlotId) {
            $appointment->slot_id = $newSlotId;
        }
        $appointment->save();

        // Create new appointment with new details
        $newAppointment = Appointment::create([
            'tenant_id' => $tenantId,
            'appointment_no' => Appointment::generateAppointmentNo(),
            'service_id' => $appointment->service_id,
            'provider_id' => $appointment->provider_id,
            'slot_id' => $newSlotId,
            'customer_id' => $appointment->customer_id,
            'user_id' => $userId,
            'status' => 'pending',
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'notes' => $appointment->notes,
        ]);

        // Send reschedule notification
        $this->sendReminder($newAppointment, 'rescheduled');

        return $newAppointment;
    }

    /**
     * Get appointment summary statistics.
     */
    public function getSummary(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Appointment::where('tenant_id', $tenantId);

        if ($startDate && $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }

        $total = $query->count();
        $byStatus = Appointment::where('tenant_id', $tenantId)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_time', [$startDate, $endDate]);
            })
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Today's stats
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayCount = Appointment::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->count();

        return [
            'total_appointments' => $total,
            'by_status' => $byStatus,
            'today_count' => $todayCount,
            'upcoming_count' => Appointment::where('tenant_id', $tenantId)
                ->where('start_time', '>', now())
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'completion_rate' => $total > 0 
                ? round((Appointment::where('tenant_id', $tenantId)
                    ->where('status', 'completed')
                    ->count() / $total) * 100, 2)
                : 0,
        ];
    }

    /**
     * Schedule reminders for an appointment.
     */
    protected function scheduleReminders(Appointment $appointment, int $tenantId): void
    {
        if (!config('booking.reminders.enabled', true)) {
            return;
        }

        $channels = config('booking.reminders.channels', ['whatsapp']);
        $schedule = config('booking.reminders.schedule', [
            '24h' => '24 hours before',
            '2h' => '2 hours before',
        ]);

        foreach ($channels as $channel) {
            foreach ($schedule as $offset => $label) {
                // Calculate reminder time based on offset
                $scheduledAt = clone $appointment->start_time;
                
                if (str_contains($offset, '24h')) {
                    $scheduledAt->subHours(24);
                } elseif (str_contains($offset, '2h')) {
                    $scheduledAt->subHours(2);
                } elseif (str_contains($offset, '30m')) {
                    $scheduledAt->subMinutes(30);
                }

                \App\Modules\Booking\Models\BookingReminder::create([
                    'tenant_id' => $tenantId,
                    'appointment_id' => $appointment->id,
                    'channel' => $channel,
                    'scheduled_at' => $scheduledAt,
                    'status' => 'pending',
                    'template' => 'appointment_reminder',
                ]);
            }
        }
    }

    /**
     * Send a reminder notification.
     */
    protected function sendReminder(Appointment $appointment, string $type): void
    {
        // TODO: Implement actual notification sending (SMS, WhatsApp, Email)
        // This would integrate with notification services
        
        $appointment->reminder_sent = true;
        $appointment->reminder_sent_at = now();
        $appointment->save();
    }
}
