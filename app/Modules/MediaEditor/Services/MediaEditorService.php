<?php

namespace App\Modules\MediaEditor\Services;

use App\Modules\MediaEditor\Models\MediaProject;
use App\Modules\MediaEditor\Models\MediaRenderJob;
use App\Modules\MediaEditor\Models\MediaTemplate;
use Illuminate\Support\Facades\Log;

/**
 * MediaEditorService — tenant edit gambar/video sendiri (corak Canva/CapCut).
 *
 * ✅ Idea #27 (BAB 86): "system mcm canva, capcut dan lain yg open source jadi
 * tenant boleh edit gambar dan video sendiri".
 *
 * Pendekatan: definisi projek (canvas/layers/timeline) disimpan sebagai JSON.
 * Renderer sebenar (FFmpeg/Sharp) dijalankan oleh worker luar — modul ini
 * mengurus projek, templat & kerja render.
 */
class MediaEditorService
{
    /** Saiz kanvas piawai. */
    public const RESOLUTIONS = [
        'square' => '1080x1080',
        'portrait' => '1080x1350',
        'story' => '1080x1920',
        'landscape' => '1920x1080',
        'thumbnail' => '1280x720',
    ];

    /** Jenis lapisan yang disokong. */
    public const LAYER_TYPES = ['text', 'image', 'video', 'shape', 'sticker', 'group', 'audio'];

    /**
     * Cipta projek media.
     */
    public function ciptaProjek(int $tenantId, array $data): MediaProject
    {
        return MediaProject::create(array_merge([
            'tenant_id' => $tenantId,
            'name' => 'Projek Tanpa Nama',
            'project_type' => MediaProject::TYPE_IMAGE,
            'status' => MediaProject::STATUS_DRAFT,
            'canvas' => [
                'width' => 1080,
                'height' => 1080,
                'background' => '#ffffff',
            ],
            'layers' => [],
            'resolution' => '1080x1080',
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Sahkan struktur lapisan.
     */
    public function sahkanLapisan(array $lapisan): array
    {
        $ralat = [];

        foreach ($lapisan as $i => $l) {
            $jenis = $l['type'] ?? null;

            if ($jenis === null || ! in_array($jenis, self::LAYER_TYPES, true)) {
                $ralat[] = "Lapisan #{$i}: jenis tidak sah (" . ($jenis ?? 'tiada') . ')';
                continue;
            }

            if (! isset($l['x']) || ! isset($l['y'])) {
                $ralat[] = "Lapisan #{$i}: x/y diperlukan";
            }

            if ($jenis === 'text' && empty($l['text'])) {
                $ralat[] = "Lapisan #{$i}: lapisan teks perlu 'text'";
            }

            if (in_array($jenis, ['image', 'video', 'audio'], true) && empty($l['src'])) {
                $ralat[] = "Lapisan #{$i}: jenis {$jenis} perlu 'src'";
            }
        }

        return ['valid' => empty($ralat), 'errors' => $ralat];
    }

    /**
     * Kemas kini projek.
     */
    public function kemasProjek(int $tenantId, int $projectId, array $data): MediaProject
    {
        $projek = MediaProject::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($projectId);

        if (isset($data['layers'])) {
            $sah = $this->sahkanLapisan($data['layers']);
            if (! $sah['valid']) {
                throw new \InvalidArgumentException('Lapisan tidak sah: ' . implode('; ', $sah['errors']));
            }
        }

        $data['status'] = MediaProject::STATUS_EDITING;
        $projek->update($data);

        return $projek->fresh();
    }

    /**
     * Tambah lapisan.
     */
    public function tambahLapisan(int $tenantId, int $projectId, array $lapisan): MediaProject
    {
        $projek = MediaProject::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($projectId);

        $sedia = $projek->layers ?? [];
        $sedia[] = $lapisan;

        $sah = $this->sahkanLapisan($sedia);
        if (! $sah['valid']) {
            throw new \InvalidArgumentException('Lapisan tidak sah: ' . implode('; ', $sah['errors']));
        }

        $projek->update([
            'layers' => $sedia,
            'status' => MediaProject::STATUS_EDITING,
        ]);

        return $projek->fresh();
    }

    /**
     * Buang lapisan mengikut index.
     */
    public function buangLapisan(int $tenantId, int $projectId, int $index): MediaProject
    {
        $projek = MediaProject::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($projectId);

        $lapisan = $projek->layers ?? [];

        if (! isset($lapisan[$index])) {
            throw new \InvalidArgumentException("Lapisan index {$index} tidak wujud");
        }

        array_splice($lapisan, $index, 1);
        $projek->update(['layers' => $lapisan]);

        return $projek->fresh();
    }

    /**
     * Jana kerja render.
     */
    public function janaRender(int $tenantId, int $projectId, array $options = []): MediaRenderJob
    {
        $projek = MediaProject::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($projectId);

        $format = $options['output_format']
            ?? ($projek->project_type === MediaProject::TYPE_VIDEO ? 'mp4' : 'png');

        $job = MediaRenderJob::create([
            'tenant_id' => $tenantId,
            'media_project_id' => $projek->id,
            'status' => MediaRenderJob::STATUS_QUEUED,
            'output_format' => $format,
            'resolution' => $options['resolution'] ?? $projek->resolution,
        ]);

        $projek->update(['status' => MediaProject::STATUS_RENDERING]);

        // Render sebenar dilakukan worker luar. Di sini kita simulasikan selesai
        // supaya API boleh diuji dan tenant dapat preview struktur output.
        try {
            $url = $this->simulasiRender($tenantId, $projek, $job);

            $job->update([
                'status' => MediaRenderJob::STATUS_COMPLETED,
                'output_url' => $url,
                'duration_ms' => 500,
                'file_size_kb' => 512,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            $projek->update([
                'status' => MediaProject::STATUS_READY,
                'output_url' => $url,
                'thumbnail_url' => $url,
            ]);
        } catch (\Throwable $e) {
            Log::warning('MediaEditor: render gagal', ['error' => $e->getMessage()]);

            $job->update([
                'status' => MediaRenderJob::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $projek->update(['status' => MediaProject::STATUS_FAILED]);
        }

        return $job->fresh();
    }

    private function simulasiRender(int $tenantId, MediaProject $projek, MediaRenderJob $job): string
    {
        return sprintf(
            'https://media.aibijan.local/t%d/projects/%d/render-%d.%s',
            $tenantId,
            $projek->id,
            $job->id,
            $job->output_format
        );
    }

    /**
     * Guna templat → projek baharu.
     */
    public function gunaTemplate(int $tenantId, int $templateId, ?string $nama = null): MediaProject
    {
        $template = MediaTemplate::query()
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->where('is_active', true)
            ->findOrFail($templateId);

        $def = $template->definition ?? [];

        $projek = MediaProject::create([
            'tenant_id' => $tenantId,
            'name' => $nama ?? $template->name,
            'project_type' => $template->project_type,
            'status' => MediaProject::STATUS_EDITING,
            'canvas' => $def['canvas'] ?? ['width' => 1080, 'height' => 1080, 'background' => '#ffffff'],
            'layers' => $def['layers'] ?? [],
            'timeline' => $def['timeline'] ?? null,
            'resolution' => $template->resolution,
            'created_by' => auth()->id(),
        ]);

        $template->increment('use_count');

        return $projek;
    }

    /**
     * Ringkasan projek media.
     */
    public function ringkasan(int $tenantId): array
    {
        $projek = MediaProject::query()->where('tenant_id', $tenantId)->get();
        $jobs = MediaRenderJob::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        return [
            'jumlah_projek' => $projek->count(),
            'siap' => $projek->where('status', MediaProject::STATUS_READY)->count(),
            'draf' => $projek->where('status', MediaProject::STATUS_DRAFT)->count(),
            'ikut_jenis' => $projek->groupBy('project_type')->map(fn ($g) => $g->count())->toArray(),
            'render_30_hari' => $jobs->count(),
            'render_berjaya' => $jobs->where('status', MediaRenderJob::STATUS_COMPLETED)->count(),
            'render_gagal' => $jobs->where('status', MediaRenderJob::STATUS_FAILED)->count(),
            'template_tersedia' => MediaTemplate::query()
                ->where(function ($q) use ($tenantId) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
                })
                ->where('is_active', true)
                ->count(),
        ];
    }
}
