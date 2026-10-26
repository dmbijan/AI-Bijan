<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * List all appointments.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'provider_id', 'start_date', 'end_date', 'search']);

        return response()->json($this->appointmentService->listAppointments($request->tenant_id, $filters));
    }

    /**
     * Show a single appointment.
     */
    public function show(Request $request, int $id)
    {
        $appointment = $this->appointmentService->getAppointment($id, $request->tenant_id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        return response()->json($appointment);
    }

    /**
     * Create a new appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:booking_services,id',
            'provider_id' => 'nullable|exists:booking_service_providers,id',
            'slot_id' => 'nullable|exists:booking_slots,id',
            'customer_id' => 'nullable|exists:crm_clients,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,pending,confirmed,in_progress,completed,cancelled,no_show,rescheduled',
        ]);

        try {
            $appointment = $this->appointmentService->createAppointment(
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($appointment, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update an appointment.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'service_id' => 'sometimes|exists:booking_services,id',
            'provider_id' => 'sometimes|exists:booking_service_providers,id',
            'slot_id' => 'nullable|exists:booking_slots,id',
            'customer_id' => 'sometimes|exists:crm_clients,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,pending,confirmed,in_progress,completed,cancelled,no_show,rescheduled',
        ]);

        try {
            $appointment = $this->appointmentService->updateAppointment($id, $validated, $request->tenant_id);

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Confirm an appointment.
     */
    public function confirm(Request $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->confirmAppointment($id, $request->tenant_id);

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Start an appointment.
     */
    public function start(Request $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->startAppointment($id, $request->tenant_id);

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Complete an appointment.
     */
    public function complete(Request $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->completeAppointment($id, $request->tenant_id);

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        try {
            $appointment = $this->appointmentService->cancelAppointment(
                $id,
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark no-show.
     */
    public function markNoShow(Request $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->markNoShow($id, $request->tenant_id);

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reschedule an appointment.
     */
    public function reschedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'slot_id' => 'nullable|exists:booking_slots,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $appointment = $this->appointmentService->rescheduleAppointment(
                $id,
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found.'], 404);
            }

            return response()->json($appointment);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get appointment summary statistics.
     */
    public function summary(Request $request)
    {
        return response()->json($this->appointmentService->getSummary(
            $request->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        ));
    }
}
