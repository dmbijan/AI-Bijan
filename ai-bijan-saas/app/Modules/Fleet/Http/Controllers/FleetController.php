<?php

namespace App\Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fleet\Models\FleetAlert;
use App\Modules\Fleet\Models\FleetDriver;
use App\Modules\Fleet\Models\FleetTrip;
use App\Modules\Fleet\Models\FleetVehicle;
use App\Modules\Fleet\Services\FleetService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function __construct(private FleetService $fleet) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai kenderaan.
     */
    public function vehicles(Request $request): JsonResponse
    {
        $query = FleetVehicle::query()->orderBy('plate_number');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $vehicles = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => ['total' => $vehicles->total()],
        ]);
    }

    /**
     * Tambah kenderaan.
     */
    public function storeVehicle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:20'],
            'make' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:40'],
            'fuel_type' => ['nullable', 'string', 'max:30'],
            'assigned_driver_id' => ['nullable', 'integer'],
        ]);

        $vehicle = FleetVehicle::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        return response()->json(['success' => true, 'data' => $vehicle], 201);
    }

    /**
     * Kemas kini kenderaan.
     */
    public function updateVehicle(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'plate_number' => ['sometimes', 'string', 'max:20'],
            'make' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'in:active,maintenance,idle,retired'],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'fuel_level_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_driver_id' => ['nullable', 'integer'],
        ]);

        $vehicle = FleetVehicle::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $vehicle) {
            return response()->json(['success' => false, 'message' => 'Kenderaan tidak dijumpai'], 404);
        }

        $vehicle->fill($validated)->save();

        return response()->json(['success' => true, 'data' => $vehicle->fresh()]);
    }

    /**
     * Rekod lokasi GPS (dari peranti atau provider).
     */
    public function rekodLokasi(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'battery_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fuel_level_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ignition_on' => ['nullable', 'boolean'],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        if (! empty($validated['recorded_at'])) {
            $validated['recorded_at'] = \Carbon\Carbon::parse($validated['recorded_at']);
        }

        $hasil = $this->fleet->rekodLokasi($this->tenantId(), $id, $validated);

        if (! $hasil['ok']) {
            return response()->json(['success' => false, 'message' => 'Kenderaan tidak dijumpai'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $hasil['location'],
            'alerts' => $hasil['alerts'],
        ]);
    }

    /**
     * Peta lokasi terkini semua kenderaan.
     */
    public function peta(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->fleet->petaKota($this->tenantId()),
        ]);
    }

    /**
     * Senarai pemandu.
     */
    public function drivers(): JsonResponse
    {
        $drivers = FleetDriver::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $drivers]);
    }

    /**
     * Tambah pemandu.
     */
    public function storeDriver(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['required', 'string', 'max:30'],
            'license_no' => ['nullable', 'string', 'max:40'],
            'license_class' => ['nullable', 'string', 'max:20'],
            'license_expiry' => ['nullable', 'date'],
        ]);

        $driver = FleetDriver::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        return response()->json(['success' => true, 'data' => $driver], 201);
    }

    /**
     * Skor keselamatan pemandu.
     */
    public function skorKeselamatan(Request $request, int $id): JsonResponse
    {
        $skor = $this->fleet->kiraSkorKeselamatan($this->tenantId(), $id);

        return response()->json([
            'success' => true,
            'data' => [
                'driver_id' => $id,
                'safety_score' => $skor,
                'periode_hari' => 30,
            ],
        ]);
    }

    /**
     * Mula perjalanan.
     */
    public function mulaPerjalanan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'driver_id' => ['nullable', 'integer'],
            'shipment_id' => ['nullable', 'integer'],
            'reference_no' => ['nullable', 'string', 'max:60'],
            'route_points' => ['nullable', 'array'],
        ]);

        $trip = $this->fleet->mulaPerjalanan($this->tenantId(), $id, $validated);

        return response()->json(['success' => true, 'data' => $trip], 201);
    }

    /**
     * Tamat perjalanan.
     */
    public function tamatPerjalanan(Request $request, int $tripId): JsonResponse
    {
        $validated = $request->validate([
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $trip = $this->fleet->tamatPerjalanan($this->tenantId(), $tripId, $validated);

        return response()->json(['success' => true, 'data' => $trip]);
    }

    /**
     * Senarai amaran.
     */
    public function alerts(Request $request): JsonResponse
    {
        $query = FleetAlert::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('triggered_at');

        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity')->toString());
        }

        if ($request->filled('unresolved')) {
            $query->whereNull('resolved_at');
        }

        $alerts = $query->limit(min((int) $request->integer('limit', 50), 200))->get();

        return response()->json(['success' => true, 'data' => $alerts]);
    }

    /**
     * Selesaikan amaran.
     */
    public function resolveAlert(Request $request, int $id): JsonResponse
    {
        $alert = FleetAlert::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $alert) {
            return response()->json(['success' => false, 'message' => 'Amaran tidak dijumpai'], 404);
        }

        $alert->update([
            'resolved_at' => now(),
            'acknowledged_at' => $alert->acknowledged_at ?? now(),
            'acknowledged_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $alert->fresh()]);
    }

    /**
     * Statistik armada.
     */
    public function statistik(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->fleet->statistik($this->tenantId()),
        ]);
    }
}
