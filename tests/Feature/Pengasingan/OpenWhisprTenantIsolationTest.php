<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\OpenWhispr\Models\DictationSession;
use App\Modules\OpenWhispr\Models\DictationVocabulary;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul OpenWhispr (diktasi suara ke teks).
 *
 * Ditambah 8 Nov 2026. Sesi diktasi menyimpan transkripsi yang boleh
 * mengandungi rahsia perniagaan; kamus mendedahkan istilah syarikat.
 */
class OpenWhisprTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'OpenWhispr';

    protected array $model = [
        DictationSession::class,
        DictationVocabulary::class,
    ];

    protected ?string $laluanApi = '/api/v1/open-whispr/vocabulary';

    protected ?string $namaRahsia = 'Istilah Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === DictationSession::class) {
            return [
                'uuid' => 'dict-uuid-'.$n,
                'title' => 'Sesi Rahsia Tenant Satu '.$n,
                'language' => 'ms',
                'engine' => 'whisper_local',
                'status' => DictationSession::STATUS_RECORDING,
                'raw_text' => 'Transkripsi Rahsia Tenant Satu '.$n,
                'processed_text' => 'Transkripsi Rahsia Tenant Satu '.$n,
            ];
        }

        return [
            'word' => 'Istilah Rahsia Tenant Satu '.$n,
            'aliases' => ['istilah-'.$n],
            'category' => 'produk',
            'weight' => 1.5,
            'is_active' => true,
        ];
    }
}
