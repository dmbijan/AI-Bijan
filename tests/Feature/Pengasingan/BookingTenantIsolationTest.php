<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Booking (temujanji, servis).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Jadual temujanji mendedahkan siapa pelanggan
 * dan bila mereka datang — data peribadi + corak operasi.
 */
class BookingTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Booking';

    protected array $model = [
        Service::class,
        Appointment::class,
    ];

    protected ?string $laluanApi = '/api/v1/booking/appointments';

    protected ?string $namaRahsia = 'Temujanji Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Service::class) {
            return [
                'name' => 'Servis Rahsia Tenant Satu ' . $n,
                'slug' => 'servis-rahsia-' . $n,
                'duration_minutes' => 60,
                'price' => 100,
                'is_active' => true,
            ];
        }

        $servis = Service::create([
            'name' => 'Servis Rahsia Tenant Satu ' . $n,
            'slug' => 'servis-rahsia-' . $n,
            'duration_minutes' => 60,
            'price' => 100,
            'is_active' => true,
        ]);

        return [
            'appointment_no' => 'APT-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'service_id' => $servis->id,
            'status' => 'draft',
            'start_time' => '2026-11-02 09:00:00',
            'end_time' => '2026-11-02 10:00:00',
            'notes' => 'Temujanji Rahsia Tenant Satu',
        ];
    }
}
