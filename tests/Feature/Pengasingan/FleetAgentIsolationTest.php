<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Fleet\Models\FleetVehicle;
use App\Modules\AIAgents\Models\Agent;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Fleet (kenderaan) + AIAgents (ejen AI).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • Fleet: nombor plat + pemandu = maklumat operasi dan lokasi.
 * • AIAgents: `system_prompt` dan akses data ejen — kebocoran bermakna tenant
 *   lain boleh membaca prompt proprietari dan skop data yang dibenarkan.
 */
class FleetAgentIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Fleet';

    protected array $model = [
        FleetVehicle::class,
        Agent::class,
    ];

    protected ?string $laluanApi = '/api/v1/fleet/vehicles';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Agent::class) {
            return [
                'agent_key' => 'ejen-rahsia-' . $n,
                'name' => 'Ejen Rahsia Tenant Satu ' . $n,
                'role' => 'pembantu',
                'status' => 'active',
            ];
        }

        return [
            'plate_number' => 'WXY ' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'type' => 'van',
            'status' => 'active',
        ];
    }
}
