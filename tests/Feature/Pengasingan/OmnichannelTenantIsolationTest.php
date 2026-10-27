<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Omnichannel\Models\Channel;
use App\Modules\Omnichannel\Models\Conversation;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Omnichannel (saluran, perbualan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Perbualan mengandungi mesej pelanggan dan
 * kredensial saluran (`config`, `webhook_secret`) — kebocoran di sini
 * bermakna tenant lain boleh membaca rahsia webhook dan mesej pelanggan.
 */
class OmnichannelTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Omnichannel';

    protected array $model = [
        Channel::class,
        Conversation::class,
    ];

    protected ?string $laluanApi = '/api/v1/omnichannel/channels';

    protected ?string $namaRahsia = 'Saluran Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $saluran = Channel::create([
            'name' => 'Saluran Rahsia Tenant Satu ' . $n,
            'type' => 'whatsapp',
            'is_active' => true,
        ]);

        if ($this->rekodSemasa === Conversation::class) {
            return [
                'channel_id' => $saluran->id,
                'source_contact_identifier' => '6012345678' . $n,
                'status' => 'open',
            ];
        }

        return [
            'name' => 'Saluran Rahsia Tenant Satu ' . $n,
            'type' => 'whatsapp',
            'is_active' => true,
        ];
    }
}
