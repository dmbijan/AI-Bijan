<?php

namespace App\Modules\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Scheduling\Models\SchedulingLink;
use App\Modules\Scheduling\Services\SchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchedulingController extends Controller
{
    public function __construct(private SchedulingService $scheduling) {}

    /**
     * Senarai pautan penjadualan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchedulingLink::query()->orderByDesc('created_at');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
            'meta' => ['strategi' => SchedulingLink::strategi()],
        ]);
    }

    /**
     * Cipta pautan penjadualan.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer'],
            'provider_ids' => ['nullable', 'array'],
            'provider_ids.*' => ['integer'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'interval_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'buffer_before_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'buffer_after_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'minimum_notice_minutes' => ['nullable', 'integer', 'min:0', 'max:20160'],
            'max_booking_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'availability_rules' => ['nullable', 'array'],
            'routing_strategy' => ['nullable', 'string', 'in:round_robin,least_loaded,random,skill_match'],
            'max_bookings_per_day' => ['nullable', 'integer', 'min:1', 'max:50'],
            'requires_confirmation' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'require_phone' => ['nullable', 'boolean'],
            'require_name' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $pautan = $this->scheduling->createLink($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pautan penjadualan berjaya dicipta.',
            'data' => $pautan,
            'meta' => ['url' => url('/book/'.$pautan->slug)],
        ], 201);
    }

    /**
     * Kemas kini pautan.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pautan = SchedulingLink::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'service_ids' => ['nullable', 'array'],
            'provider_ids' => ['nullable', 'array'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'interval_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'minimum_notice_minutes' => ['nullable', 'integer', 'min:0', 'max:20160'],
            'availability_rules' => ['nullable', 'array'],
            'routing_strategy' => ['nullable', 'string', 'in:round_robin,least_loaded,random,skill_match'],
            'max_bookings_per_day' => ['nullable', 'integer', 'min:1', 'max:50'],
            'requires_confirmation' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'require_phone' => ['nullable', 'boolean'],
            'require_name' => ['nullable', 'boolean'],
            'hide_booking_details' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $pautan->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pautan penjadualan berjaya dikemas kini.',
            'data' => $pautan->fresh(),
        ]);
    }

    /**
     * Buang pautan.
     */
    public function destroy(int $id): JsonResponse
    {
        SchedulingLink::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pautan penjadualan berjaya dibuang.',
        ]);
    }

    /**
     * Slot tersedia untuk satu tarikh.
     */
    public function slots(Request $request, int $id): JsonResponse
    {
        $pautan = SchedulingLink::findOrFail($id);

        $v = Validator::make($request->all(), [
            'date' => ['required', 'date'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $slot = $this->scheduling->availableSlots($pautan, $v->validated()['date']);

        return response()->json([
            'success' => true,
            'data' => $slot,
            'meta' => [
                'tarikh' => $v->validated()['date'],
                'jumlah' => $slot->count(),
                'timezone' => $pautan->timezone,
            ],
        ]);
    }

    /**
     * Tempah slot melalui pautan.
     */
    public function book(Request $request, int $id): JsonResponse
    {
        $pautan = SchedulingLink::findOrFail($id);

        if (! $pautan->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Pautan penjadualan ini telah dinyahaktifkan.',
            ], 403);
        }

        $v = Validator::make($request->all(), [
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'customer_id' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $tempahan = $this->scheduling->tempah($pautan, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tempahan berjaya dibuat.',
            'data' => [
                'appointment_no' => $tempahan->appointment_no,
                'status' => $tempahan->status,
                'start_time' => $tempahan->start_time?->toIso8601String(),
                'end_time' => $tempahan->end_time?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Jadual pasukan.
     */
    public function teamCalendar(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'provider_ids' => ['required', 'array', 'min:1'],
            'provider_ids.*' => ['integer'],
            'dari' => ['required', 'date'],
            'hingga' => ['required', 'date', 'after_or_equal:dari'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        return response()->json([
            'success' => true,
            'data' => $this->scheduling->teamCalendar(
                $data['provider_ids'],
                $data['dari'],
                $data['hingga']
            ),
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
