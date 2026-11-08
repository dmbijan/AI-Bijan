<?php

namespace App\Modules\Scheduling\Services;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\BookingSlot;
use App\Modules\Booking\Models\Service;
use App\Modules\Scheduling\Models\SchedulingLink;
use App\Modules\Scheduling\Models\SchedulingSlot;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * SchedulingService — penjadualan tempahan.
 *
 * inspired oleh https://github.com/calcom/cal.diy
 *
 * Menyediakan:
 *   - pautan penjadualan awam dengan token
 *   - pengiraan slot tersedia (availability) mengikut jadual,
 *     tempoh minimum notifikasi, buffer dan konflik tempahan sedia ada
 *   - routing round-robin / beban-terendah antara provider
 *   - jadual pasukan (team calendar)
 */
class SchedulingService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * ID tenant semasa.
     */
    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Cip pautan penjadualan.
     *
     * @param  array<string, mixed>  $data
     */
    public function createLink(array $data): SchedulingLink
    {
        return SchedulingLink::create(array_merge($data, [
            'slug' => $data['slug'] ?? Str::random(24),
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Kiraan slot tersedia untuk satu pautan.
     *
     * @param  array<string, mixed>  $option
     * @return Collection<int, array{starts_at: string, ends_at: string, provider_id: ?int}>
     */
    public function availableSlots(SchedulingLink $pautan, string $tarikh, array $option = []): Collection
    {
        $tarikhCarbon = Carbon::parse($tarikh)->startOfDay();
        $zaman = $pautan->timezone ?: config('app.timezone', 'Asia/Kuala_Lumpur');

        if ($pautan->minimum_notice_minutes > 0) {
            $terkawal = Carbon::now($zaman)->addMinutes($pautan->minimum_notice_minutes);

            if ($terkawal->greaterThan($tarikhCarbon)) {
                $tarikhCarbon = $terkawal->startOfHour();
            }
        }

        $akhir = $tarikhCarbon->copy()->endOfDay();

        // Julat tempoh tempahan sedia ada yang bertindih.
        $tempahanBusy = Appointment::query()
            ->whereIn('status', ['confirmed', 'pending', 'in_progress'])
            ->whereBetween('start_time', [$tarikhCarbon->copy()->startOfDay(), $akhir])
            ->get(['provider_id', 'start_time', 'end_time']);

        // Slot booking yang telah ditempah (modul Booking).
        $slotBusy = BookingSlot::query()
            ->where('is_booked', true)
            ->whereBetween('slot_date', [$tarikhCarbon->toDateString(), $akhir->toDateString()])
            ->get(['provider_id', 'start_time', 'end_time']);

        // Garis masa presence yang dijana sebelum ini.
        $slotTersimpan = SchedulingSlot::query()
            ->where('scheduling_link_id', $pautan->id)
            ->whereBetween('starts_at', [$tarikhCarbon->copy()->startOfDay(), $akhir])
            ->get(['provider_id', 'starts_at', 'ends_at', 'is_available', 'is_booked']);

        $providerIds = $pautan->provider_ids ?: [];
        $slotBaru = [];

        foreach ($pautan->availability_rules ?? [] as $peraturan) {
            if (($peraturan['day_of_week'] ?? null) !== $tarikhCarbon->dayOfWeek) {
                continue;
            }

            $mulaHari = $peraturan['start_time'] ?? '09:00';
            $tamatHari = $peraturan['end_time'] ?? '17:00';

            $mula = Carbon::parse($tarikhCarbon->toDateString().' '.$mulaHari, $zaman);
            $tamat = Carbon::parse($tarikhCarbon->toDateString().' '.$tamatHari, $zaman);

            // Pecah kepada slot mengikut selang.
            $slotMula = $mula->copy();
            $selang = max(5, (int) $pautan->interval_minutes);

            while ($slotMula->copy()->addMinutes($pautan->duration_minutes) <= $tamat) {
                $slotTamat = $slotMula->copy()->addMinutes($pautan->duration_minutes);

                // Buffer sebelum/selepas.
                $mulaEfektif = $slotMula->copy()->subMinutes($pautan->buffer_before_minutes);
                $tamatEfektif = $slotTamat->copy()->addMinutes($pautan->buffer_after_minutes);

                if ($mulaEfektif->greaterThanOrEqualTo(Carbon::now($zaman))) {
                    $conflict = $this->adaKonflik($providerIds, $mulaEfektif, $tamatEfektif, $tempahanBusy, $slotBusy, $slotTersimpan);

                    if (! $conflict) {
                        $slotBaru[] = [
                            'starts_at' => $slotMula->toIso8601String(),
                            'ends_at' => $slotTamat->toIso8601String(),
                            'provider_id' => null,
                            '_start' => $slotMula,
                            '_end' => $slotTamat,
                        ];
                    }
                }

                $slotMula->addMinutes($selang);
            }
        }

        // Buang slot tersimpan yang tidak tersedia (dikunci oleh pengguna lain).
        $tersedia = collect($slotBaru)->reject(function ($slot) use ($slotTersimpan) {
            foreach ($slotTersimpan as $simpan) {
                if (! $simpan->is_available) {
                    if (Carbon::parse($simpan->starts_at)->between($slot['_start'], $slot['_end'])) {
                        return true;
                    }
                }
            }

            return false;
        });

        return $tersimpan->values()->map(fn ($slot) => [
            'starts_at' => $slot['starts_at'],
            'ends_at' => $slot['ends_at'],
            'provider_id' => $slot['provider_id'],
        ]);
    }

    /**
     * Adakah ada konflik tempahan?
     *
     * @param  array<int, int>  $providerIds
     * @param  Collection<int, Appointment>  $tempahanBusy
     * @param  Collection<int, BookingSlot>  $slotBusy
     * @param  Collection<int, SchedulingSlot>  $slotTersimpan
     */
    protected function adaKonflik(
        array $providerIds,
        Carbon $mula,
        Carbon $tamat,
        Collection $tempahanBusy,
        Collection $slotBusy,
        Collection $slotTersimpan
    ): bool {
        foreach ($tempahanBusy as $tempahan) {
            // Provider khusus: hanya conflict jika provider sama.
            if ($providerIds !== [] && ! in_array($tempahan->provider_id, $providerIds, true)) {
                continue;
            }

            if ($mula->lt(Carbon::parse($tempahan->end_time)) && $tamat->gt(Carbon::parse($tempahan->start_time))) {
                return true;
            }
        }

        foreach ($slotBusy as $slot) {
            if ($providerIds !== [] && ! in_array($slot->provider_id, $providerIds, true)) {
                continue;
            }

            $slotMula = Carbon::parse($slot->start_time);
            $slotTamat = Carbon::parse($slot->end_time);

            if ($mula->lt($slotTamat) && $tamat->gt($slotMula)) {
                return true;
            }
        }

        foreach ($slotTersimpan as $simpan) {
            if ($simpan->is_booked) {
                $simpanMula = Carbon::parse($simpan->starts_at);
                $simpanTamat = Carbon::parse($simpan->ends_at);

                if ($mula->lt($simpanTamat) && $tamat->gt($simpanMula)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Pilih provider terbaik mengikut strategi routing.
     *
     * @param  array<int, int>  $providerIds
     * @return array{provider_id: ?int, strategy: string}
     */
    public function pilihProvider(SchedulingLink $pautan, array $providerIds, string $mula, string $tamat): array
    {
        $strategi = $pautan->routing_strategy ?: 'round_robin';

        if ($providerIds === []) {
            return ['provider_id' => null, 'strategy' => $strategi];
        }

        if ($strategi === 'random') {
            return ['provider_id' => collect($providerIds)->random(), 'strategy' => $strategi];
        }

        if ($strategi === 'least_loaded') {
            $beban = $this->bebanProvider($providerIds, $mula, $tamat);
            $palingRendah = collect($beban)->sortBy(fn ($b) => $b['count'])->first();

            return ['provider_id' => $palingRendah['provider_id'] ?? null, 'strategy' => $strategi];
        }

        if ($strategi === 'skill_match') {
            // Provider dengan kemahiran paling banyak dipadankan dengan servis.
            $servisIds = $pautan->service_ids ?: [];

            if ($servisIds !== []) {
                $servis = Service::query()->whereIn('id', $servisIds)->get();
                $terbaik = null;
                $terbaikSkor = -1;

                foreach ($providerIds as $providerId) {
                    $skor = $servis->where('provider_id', $providerId)->count();

                    if ($skor > $terbaikSkor) {
                        $terbaikSkor = $skor;
                        $terbaik = $providerId;
                    }
                }

                if ($terbaik !== null) {
                    return ['provider_id' => $terbaik, 'strategy' => $strategi];
                }
            }
        }

        // Round-robin: giliran mengikut kiraan tempahan terkini.
        $terakhir = Appointment::query()
            ->whereIn('provider_id', $providerIds)
            ->orderByDesc('created_at')
            ->first();

        $seterusnya = collect($providerIds)->first();

        if ($terakhir && in_array($terakhir->provider_id, $providerIds, true)) {
            $indeks = array_search($terakhir->provider_id, $providerIds, true);
            $seterusnya = $providerIds[($indeks + 1) % count($providerIds)];
        }

        return ['provider_id' => $seterusnya, 'strategy' => $strategi];
    }

    /**
     * Beban tempahan setiap provider dalam julat masa.
     *
     * @param  array<int, int>  $providerIds
     * @return array<int, array{provider_id: int, count: int}>
     */
    protected function bebanProvider(array $providerIds, string $mula, string $tamat): array
    {
        $jumlah = Appointment::query()
            ->whereIn('provider_id', $providerIds)
            ->whereIn('status', ['confirmed', 'pending', 'in_progress'])
            ->whereBetween('start_time', [$mula, $tamat])
            ->selectRaw('provider_id, COUNT(*) as jumlah')
            ->groupBy('provider_id')
            ->pluck('jumlah', 'provider_id')
            ->all();

        return array_map(fn ($id) => [
            'provider_id' => $id,
            'count' => (int) ($jumlah[$id] ?? 0),
        ], $providerIds);
    }

    /**
     * Tempah slot untuk satu pautan.
     *
     * @param  array<string, mixed>  $data
     */
    public function tempah(SchedulingLink $pautan, array $data): Appointment
    {
        $mula = Carbon::parse($data['starts_at']);
        $tamat = Carbon::parse($data['ends_at'] ?? $mula->copy()->addMinutes($pautan->duration_minutes));

        $routing = $this->pilihProvider(
            $pautan,
            $pautan->provider_ids ?: [],
            $mula->toIso8601String(),
            $tamat->toIso8601String()
        );

        $servisId = $pautan->service_ids[0] ?? null;

        $tempahan = Appointment::create([
            'appointment_no' => 'APT-'.str_pad((string) ($pautan->booking_count + 1), 6, '0', STR_PAD_LEFT),
            'service_id' => $servisId,
            'provider_id' => $routing['provider_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'status' => $pautan->requires_confirmation ? 'pending' : 'confirmed',
            'start_time' => $mula,
            'end_time' => $tamat,
            'notes' => $data['notes'] ?? null,
        ]);

        // Simpan slot presence supaya tidak ditempah dua kali.
        SchedulingSlot::create([
            'scheduling_link_id' => $pautan->id,
            'provider_id' => $routing['provider_id'],
            'starts_at' => $mula,
            'ends_at' => $tamat,
            'is_available' => false,
            'is_booked' => true,
            'booking_id' => $tempahan->id,
        ]);

        $pautan->increment('booking_count');

        return $tempahan;
    }

    /**
     * Jadual pasukan — tempahan semua provider dalam julat masa.
     *
     * @return array<string, mixed>
     */
    public function teamCalendar(array $providerIds, string $dari, string $hingga): array
    {
        $tempahan = Appointment::query()
            ->whereIn('provider_id', $providerIds)
            ->whereBetween('start_time', [$dari, $hingga])
            ->orderBy('start_time')
            ->get();

        $mengikutHari = $tempahan->groupBy(fn ($t) => Carbon::parse($t->start_time)->toDateString());

        return [
            'dari' => $dari,
            'hingga' => $hingga,
            'jumlah' => $tempahan->count(),
            'mengikut_penyedia' => $tempahan->groupBy('provider_id')->map(fn ($senarai) => [
                'provider_id' => $senarai->first()->provider_id,
                'jumlah' => $senarai->count(),
                'tempahan' => $senarai,
            ])->values(),
            'mengikut_hari' => $mengikutHari->map(fn ($senarai, $hari) => [
                'tarikh' => $hari,
                'jumlah' => $senarai->count(),
                'tempahan' => $senarai,
            ])->values(),
        ];
    }
}
