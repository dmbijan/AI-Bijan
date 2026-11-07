<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Scheduling\Models\SchedulingLink;
use App\Modules\Scheduling\Models\SchedulingSlot;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Scheduling (penjadualan tempahan).
 *
 * Ditambah 8 Nov 2026. Pautan penjadualan mendedahkan jadual staff
 * dan slot masa — data operasi syarikat.
 */
class SchedulingTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Scheduling';

    protected array $model = [
        SchedulingLink::class,
        SchedulingSlot::class,
    ];

    protected ?string $laluanApi = '/api/v1/scheduling/links';

    protected ?string $namaRahsia = 'Pautan Jadwal Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === SchedulingLink::class) {
            return [
                'title' => 'Pautan Jadwal Rahsia Tenant Satu '.$n,
                'slug' => 'jadwal-rahsia-'.$n,
                'duration_minutes' => 30,
                'interval_minutes' => 15,
                'routing_strategy' => 'round_robin',
                'is_active' => true,
                'is_public' => true,
            ];
        }

        $pautan = SchedulingLink::create([
            'title' => 'Pautan Slot '.$n,
            'slug' => 'slot-'.$n,
            'duration_minutes' => 30,
        ]);

        return [
            'scheduling_link_id' => $pautan->id,
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(9, 30),
            'is_available' => true,
            'is_booked' => false,
        ];
    }
}
