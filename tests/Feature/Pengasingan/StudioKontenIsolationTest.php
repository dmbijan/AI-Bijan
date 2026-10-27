<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\ContentStudio\Models\ContentPiece;
use App\Modules\VirtualOffice\Models\Board;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul ContentStudio + VirtualOffice (draf kandungan,
 * papan kerja dalaman).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Draf kandungan dan kad papan boleh
 * mengandungi strategi belum terbit dan nota dalaman — bocor = kehilangan
 * kelebihan daya saing, bukan sekadar pepijat data.
 *
 * Dua modul digabung dalam satu kelas kerana keduanya ringan (satu model
 * teras) — pola sama seperti `ModulAiTenantIsolationTest`.
 */
class StudioKontenIsolationTest extends UjianPengasingan
{
    protected string $modul = 'ContentStudio,VirtualOffice';

    protected array $model = [
        ContentPiece::class,
        Board::class,
    ];

    protected ?string $laluanApi = '/api/v1/content/pieces';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Board::class) {
            return [
                'name' => 'Papan Rahsia Tenant Satu ' . $n,
                'visibility' => 'team',
                'created_by' => $this->u->id,
            ];
        }

        return [
            'title' => 'Kandungan Rahsia Tenant Satu ' . $n,
            'body' => 'Draf belum terbit.',
            'content_type' => 'post',
            'status' => 'draft',
        ];
    }
}
