<?php

namespace App\Modules\Fleet\Services;

use App\Modules\Fleet\Models\FleetAlert;
use App\Modules\Fleet\Models\FleetTrip;
use App\Modules\Fleet\Models\FleetVehicle;
use App\Modules\Fleet\Models\FleetVehicleLocation;
use Illuminate\Support\Facades\Log;

/**
 * FleetService — pengurusan armada, GPS, perjalanan & amaran keselamatan.
 *
 * ✅ IDEA #10 (BAB 84): ServiceTitan "Fleet Pro" — "75% reduction in accident risk".
 * Corak yang ditiru: **amaran tingkah laku memandu** (harsh braking/acceleration/
 * cornering, speeding) yang memerlukan pengesahan pemandu.
 */
class FleetService
{
    /**
     * Ambil lokasi semasa kenderaan.
     */
    public function lokasiTerkini(int $tenantId, int $vehicleId): ?FleetVehicleLocation
    {
        return FleetVehicleLocation::query()
            ->where('tenant_id', $tenantId)
            ->where('vehicle_id', $vehicleId)
            ->first();
    }

    /**
     * Senarai lokasi terkini semua kenderaan (peta).
     */
    public function petaKota(int $tenantId): array
    {
        $locations = FleetVehicleLocation::query()
            ->where('tenant_id', $tenantId)
            ->with('vehicle:id,plate_number,type,status')
            ->get();

        return $locations->map(fn ($l) => [
            'vehicle_id' => $l->vehicle_id,
            'plate_number' => $l->vehicle?->plate_number,
            'type' => $l->vehicle?->type,
            'latitude' => (float) $l->latitude,
            'longitude' => (float) $l->longitude,
            'speed_kmh' => (float) $l->speed_kmh,
            'heading' => (float) $l->heading,
            'recorded_at' => optional($l->recorded_at)->toIso8601String(),
            'segar' => $l->segar(),
        ])->toArray();
    }

    /**
     * Rekod lokasi GPS + kesan tingkah laku memandu.
     *
     * Ini corak "Fleet Pro" — setiap pantulan boleh mencetuskan amaran.
     */
    public function rekodLokasi(int $tenantId, int $vehicleId, array $data): array
    {
        $vehicle = FleetVehicle::query()
            ->where('tenant_id', $tenantId)
            ->find($vehicleId);

        if (! $vehicle) {
            return ['ok' => false, 'reason' => 'vehicle_not_found'];
        }

        $recordedAt = $data['recorded_at'] ?? now();

        // Ambil lokasi sebelumnya untuk kira perubahan
        $sebelum = FleetVehicleLocation::query()
            ->where('tenant_id', $tenantId)
            ->where('vehicle_id', $vehicleId)
            ->first();

        $lokasi = FleetVehicleLocation::updateOrCreate(
            ['tenant_id' => $tenantId, 'vehicle_id' => $vehicleId],
            [
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'speed_kmh' => $data['speed_kmh'] ?? null,
                'heading' => $data['heading'] ?? null,
                'battery_percent' => $data['battery_percent'] ?? null,
                'ignition_on' => $data['ignition_on'] ?? null,
                'recorded_at' => $recordedAt,
            ]
        );

        // Deteksi tingkah laku memandu
        $tingkah = $this->deteksiTingkahLaku($sebelum, $data, $tenantId);

        // Simpan sejarah
        if ($data['latitude'] !== null && $data['longitude'] !== null) {
            \App\Modules\Fleet\Models\FleetLocationHistory::create([
                'tenant_id' => $tenantId,
                'vehicle_id' => $vehicleId,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'speed_kmh' => $data['speed_kmh'] ?? null,
                'harsh_braking' => $tingkah['harsh_braking'],
                'harsh_acceleration' => $tingkah['harsh_acceleration'],
                'harsh_cornering' => $tingkah['harsh_cornering'],
                'recorded_at' => $recordedAt,
            ]);
        }

        // Kemas kini bahan api
        if (isset($data['fuel_level_percent'])) {
            $vehicle->update(['fuel_level_percent' => $data['fuel_level_percent']]);

            if ((float) $data['fuel_level_percent'] <= (float) config('fleet.alerts.low_fuel_percent', 15)) {
                $this->cetusAmaran($tenantId, [
                    'vehicle_id' => $vehicleId,
                    'type' => 'low_fuel',
                    'severity' => (float) $data['fuel_level_percent'] <= 8 ? 'high' : 'medium',
                    'title' => 'Bahan api rendah: '.$vehicle->plate_number,
                    'message' => sprintf('Tahap bahan api %.0f%%', (float) $data['fuel_level_percent']),
                    'context' => ['fuel_level_percent' => $data['fuel_level_percent']],
                ]);
            }
        }

        // Kemas kini odometer
        if (isset($data['odometer_km'])) {
            $vehicle->update(['odometer_km' => $data['odometer_km']]);
        }

        return [
            'ok' => true,
            'location' => $lokasi,
            'alerts' => $tingkah['alerts'],
        ];
    }

    /**
     * Deteksi tingkah laku memandu dari perubahan lokasi.
     *
     * @return array{harsh_braking: bool, harsh_acceleration: bool, harsh_cornering: bool, alerts: array}
     */
    private function deteksiTingkahLaku(?FleetVehicleLocation $sebelum, array $data, int $tenantId): array
    {
        $hasil = [
            'harsh_braking' => false,
            'harsh_acceleration' => false,
            'harsh_cornering' => false,
            'alerts' => [],
        ];

        if (! $sebelum || ! isset($data['speed_kmh']) || $sebelum->speed_kmh === null) {
            return $hasil;
        }

        $perubahan = (float) $data['speed_kmh'] - (float) $sebelum->speed_kmh;
        $masaDetik = $sebelum->recorded_at && $data['recorded_at']
            ? max(1, $data['recorded_at']->diffInSeconds($sebelum->recorded_at))
            : 1;

        // Pecut / brek mendadak (km/h per saat)
        $pecutan = $perubahan / $masaDetik;

        $ambangBrek = (float) config('fleet.alerts.harsh_braking_threshold', 4.0);
        $ambangPecut = (float) config('fleet.alerts.harsh_acceleration_threshold', 3.5);

        if ($pecutan <= -$ambangBrek) {
            $hasil['harsh_braking'] = true;
            $hasil['alerts'][] = $this->cetusAmaran($tenantId, [
                'vehicle_id' => $sebelum->vehicle_id,
                'driver_id' => $sebelum->vehicle?->assigned_driver_id,
                'type' => 'harsh_braking',
                'severity' => 'medium',
                'title' => 'Pengebrekan mendadak dikesan',
                'message' => sprintf('Penurunan %.1f km/j, %.0f km/h → %.0f km/h', abs($pecutan), (float) $sebelum->speed_kmh, (float) $data['speed_kmh']),
                'context' => ['speed_from' => (float) $sebelum->speed_kmh, 'speed_to' => (float) $data['speed_kmh']],
            ]);
        } elseif ($pecutan >= $ambangPecut) {
            $hasil['harsh_acceleration'] = true;
            $hasil['alerts'][] = $this->cetusAmaran($tenantId, [
                'vehicle_id' => $sebelum->vehicle_id,
                'driver_id' => $sebelum->vehicle?->assigned_driver_id,
                'type' => 'harsh_acceleration',
                'severity' => 'low',
                'title' => 'Pecutan mendadak dikesan',
                'message' => sprintf('Peningkatan %.1f km/j', $pecutan),
                'context' => ['speed_from' => (float) $sebelum->speed_kmh, 'speed_to' => (float) $data['speed_kmh']],
            ]);
        }

        // Memandu melebihi had laju
        $hadLaju = (float) config('fleet.alerts.speed_limit_kmh', 110);
        if ((float) $data['speed_kmh'] > $hadLaju) {
            $hasil['alerts'][] = $this->cetusAmaran($tenantId, [
                'vehicle_id' => $sebelum->vehicle_id,
                'driver_id' => $sebelum->vehicle?->assigned_driver_id,
                'type' => 'speeding',
                'severity' => 'high',
                'title' => 'Melebihi had laju',
                'message' => sprintf('%.0f km/h (had %.0f km/h)', (float) $data['speed_kmh'], $hadLaju),
                'context' => ['speed_kmh' => (float) $data['speed_kmh'], 'limit' => $hadLaju],
            ]);
        }

        return $hasil;
    }

    /**
     * Cetus amaran (cegah duplikasi berulang untuk jenis sama dalam 30 minit).
     */
    public function cetusAmaran(int $tenantId, array $data): ?FleetAlert
    {
        $ada = FleetAlert::query()
            ->where('tenant_id', $tenantId)
            ->where('type', $data['type'])
            ->where('vehicle_id', $data['vehicle_id'] ?? null)
            ->where('resolved_at', null)
            ->where('triggered_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($ada) {
            return null;
        }

        return FleetAlert::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'triggered_at' => now(),
        ]));
    }

    /**
     * Mula perjalanan.
     */
    public function mulaPerjalanan(int $tenantId, int $vehicleId, array $data = []): FleetTrip
    {
        return FleetTrip::create([
            'tenant_id' => $tenantId,
            'vehicle_id' => $vehicleId,
            'driver_id' => $data['driver_id'] ?? FleetVehicle::find($vehicleId)?->assigned_driver_id,
            'shipment_id' => $data['shipment_id'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'status' => FleetTrip::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_points' => $data['route_points'] ?? null,
            'meta' => $data['meta'] ?? null,
        ]);
    }

    /**
     * Tamat perjalanan + kira jarak.
     */
    public function tamatPerjalanan(int $tenantId, int $tripId, array $data = []): FleetTrip
    {
        $trip = FleetTrip::query()
            ->where('tenant_id', $tenantId)
            ->find($tripId);

        if (! $trip) {
            return new FleetTrip(['tenant_id' => $tenantId]);
        }

        $endedAt = $data['ended_at'] ?? now();

        $trip->update([
            'status' => FleetTrip::STATUS_COMPLETED,
            'ended_at' => $endedAt,
            'distance_km' => $data['distance_km'] ?? $trip->distance_km,
            'duration_minutes' => $trip->started_at
                ? (int) $trip->started_at->diffInMinutes($endedAt)
                : null,
        ]);

        return $trip->fresh();
    }

    /**
     * Statistik armada.
     */
    public function statistik(int $tenantId): array
    {
        $kenderaan = FleetVehicle::where('tenant_id', $tenantId);

        return [
            'total_kenderaan' => (clone $kenderaan)->count(),
            'aktif' => (clone $kenderaan)->where('status', 'active')->count(),
            'dalam_penyelenggaraan' => (clone $kenderaan)->where('status', 'maintenance')->count(),
            'pemandu' => \App\Modules\Fleet\Models\FleetDriver::where('tenant_id', $tenantId)->count(),
            'perjalanan_aktif' => FleetTrip::where('tenant_id', $tenantId)
                ->where('status', 'in_progress')->count(),
            'perjalanan_bulan_ini' => FleetTrip::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->where('started_at', '>=', now()->startOfMonth())->count(),
            'jarak_bulan_ini_km' => round((float) FleetTrip::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->where('started_at', '>=', now()->startOfMonth())
                ->sum('distance_km'), 2),
            'amaran_belum_selesai' => FleetAlert::where('tenant_id', $tenantId)
                ->whereNull('resolved_at')->count(),
            'amaran_kritikal' => FleetAlert::where('tenant_id', $tenantId)
                ->whereNull('resolved_at')
                ->where('severity', 'critical')->count(),
            'purata_kadar_kutipan' => round((float) FleetTrip::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->where('started_at', '>=', now()->startOfMonth())
                ->avg('duration_minutes'), 1),
        ];
    }

    /**
     * Skor keselamatan pemandu (berdasarkan tingkah laku).
     */
    public function kiraSkorKeselamatan(int $tenantId, int $driverId): float
    {
        $sejak = now()->subDays(30);

        $jumlah = \App\Modules\Fleet\Models\FleetLocationHistory::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('vehicle', fn ($q) => $q->where('assigned_driver_id', $driverId))
            ->where('recorded_at', '>=', $sejak)
            ->count();

        if ($jumlah === 0) {
            return 100.0;
        }

        $pelanggaran = \App\Modules\Fleet\Models\FleetLocationHistory::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('vehicle', fn ($q) => $q->where('assigned_driver_id', $driverId))
            ->where('recorded_at', '>=', $sejak)
            ->where(function ($q) {
                $q->where('harsh_braking', true)
                    ->orWhere('harsh_acceleration', true)
                    ->orWhere('harsh_cornering', true);
            })
            ->count();

        $peratus = ($pelanggaran / $jumlah) * 100;

        // Setiap 1% pelanggaran kurangkan 2 mata (minimum 0)
        $skor = max(0, round(100 - ($peratus * 2), 2));

        \App\Modules\Fleet\Models\FleetDriver::where('tenant_id', $tenantId)
            ->where('id', $driverId)
            ->update(['safety_score' => $skor]);

        return $skor;
    }
}
