<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\PersonalAssistant\Models\MemoryItem;
use App\Modules\PersonalAssistant\Models\PersonalTask;
use App\Modules\PersonalAssistant\Models\UserPreference;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — PersonalAssistant (tugas AI, memori, keutamaan pengguna).
 *
 * Ditambah 28 Okt 2026 (T-111). Memori AI ialah data peribadi paling sensitif
 * dalam sistem: "Rahsia Tenant Satu" di sini meniru fakta peribadi pekerja.
 * Sebelum ini modul ini dikira diliputi hanya melalui import dalam fail bergabung.
 */
class PersonalAssistantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'PersonalAssistant';

    protected array $model = [
        PersonalTask::class,
        MemoryItem::class,
        UserPreference::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === PersonalTask::class) {
            return [
                'user_id' => $this->u->id,
                'objective' => 'Rahsia Tenant Satu — siapkan laporan ' . $n,
                'assistant_type' => 'general',
                'status' => 'pending',
            ];
        }

        if ($this->rekodSemasa === MemoryItem::class) {
            return [
                'user_id' => $this->u->id,
                'memory_type' => MemoryItem::TYPE_CONTEXT,
                'category' => 'rahsia',
                'content_text' => 'Rahsia Tenant Satu — pelanggan utama ialah Syarikat ' . $n,
            ];
        }

        return [
            'user_id' => $this->u->id,
            'language' => 'ms',
            'tone' => 'formal',
            'report_format' => 'ringkas',
        ];
    }
}
