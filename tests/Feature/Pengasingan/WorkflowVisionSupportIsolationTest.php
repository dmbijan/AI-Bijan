<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\LiveSupport\Models\LiveSupportCall;
use App\Modules\LiveSupport\Models\LiveSupportChannel;
use App\Modules\WorkflowStudio\Models\Workflow;
use App\Modules\Vision\Models\CctvCamera;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul WorkflowStudio (aliran kerja), Vision (kamera
 * CCTV) + LiveSupport (panggilan sokongan).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • Workflow: definisi automasi ialah konfigurasi operasi sensitif.
 * • Vision: `rtsp_url_encrypted` ialah kredensial kamera — kebocoran
 *   bermakna tenant lain boleh menonton CCTV premis pesaing.
 * • LiveSupport: nombor pemanggil dan transkrip panggilan — data peribadi.
 */
class WorkflowVisionSupportIsolationTest extends UjianPengasingan
{
    protected string $modul = 'WorkflowStudio';

    protected array $model = [
        Workflow::class,
        CctvCamera::class,
        LiveSupportCall::class,
        LiveSupportChannel::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan memerlukan peranan/entitlemen khas';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === CctvCamera::class) {
            return [
                'name' => 'Kamera Rahsia Tenant Satu ' . $n,
                'location' => 'Pejabat',
            ];
        }

        if ($this->rekodSemasa === LiveSupportChannel::class) {
            return [
                'name' => 'Saluran Rahsia Tenant Satu ' . $n,
                'channel_type' => 'voice',
            ];
        }

        if ($this->rekodSemasa === LiveSupportCall::class) {
            $saluran = LiveSupportChannel::create([
                'name' => 'Saluran Rahsia Tenant Satu ' . $n,
                'channel_type' => 'voice',
            ]);

            return [
                'live_support_channel_id' => $saluran->id,
                'direction' => 'inbound',
                'caller_number' => '6012345' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'caller_name' => 'Rahsia Tenant Satu ' . $n,
            ];
        }

        return [
            'name' => 'Aliran Rahsia Tenant Satu ' . $n,
            'definition' => json_encode(['steps' => []]),
            'trigger_type' => 'manual',
        ];
    }
}
