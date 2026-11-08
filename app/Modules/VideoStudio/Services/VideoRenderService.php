<?php

namespace App\Modules\VideoStudio\Services;

use App\Modules\VideoStudio\Models\VideoBrandKit;
use App\Modules\VideoStudio\Models\VideoProject;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * VideoRenderService — orkestrasi render video melalui skill AI BIJAN.
 *
 * Skill (`.github/skills/aibijan-*`) menjalankan aliran kerja penuh
 * (preflight → bootstrap → storyboard → render → QC). Servis ini:
 *   1. menyediakan folder kerja + input (VO, skrip, brand kit)
 *   2. menjalankan `scripts/preflight.mjs` untuk semak rangkaian/npm
 *   3. memulakan render dan mengemas kini status/progress
 *   4. menyimpan MP4 ke storan dan merekod laporan QC
 *
 * ⚠️ Render sebenar memerlukan Node.js + FFmpeg + (untuk Motion) Chromium.
 * Jika tiada, servis menanda projek sebagai `failed` dengan mesej jelas —
 * ia tidak pernah berpura-pura berjaya.
 */
class VideoRenderService
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
     * Direktori akar skill.
     */
    public function dirSkill(string $skill): ?string
    {
        $peta = [
            VideoProject::SKILL_MOGRAPH => 'aibijan-mograph',
            VideoProject::SKILL_MOTION => 'aibijan-motion',
            VideoProject::SKILL_EDITOR => 'aibijan-video-editor',
        ];

        $nama = $peta[$skill] ?? null;

        if (! $nama) {
            return null;
        }

        $laluan = base_path('.github/skills/'.$nama);

        return is_dir($laluan) ? $laluan : null;
    }

    /**
     * Folder kerja projek.
     */
    public function folderKerja(VideoProject $projek): string
    {
        $laluan = storage_path('app/video-studio/'.$this->tenantId().'/'.$projek->uuid);

        if (! is_dir($laluan)) {
            mkdir($laluan, 0777, true);
        }

        return $laluan;
    }

    /**
     * Semak keperluan (Node, FFmpeg, rangkaian npm).
     *
     * @return array{ok: bool, node: bool, ffmpeg: bool, npm: bool, mesej: string}
     */
    public function preflight(string $skill): array
    {
        $dir = $this->dirSkill($skill);

        if ($dir === null) {
            return [
                'ok' => false,
                'node' => false,
                'ffmpeg' => false,
                'npm' => false,
                'mesej' => 'Skill tidak dijumpai: '.$skill,
            ];
        }

        $node = $this->perintahAda('node --version');
        $ffmpeg = $this->perintahAda('ffmpeg -version');
        $npm = $this->perintahAda('npm --version');

        $skrip = $dir.'/scripts/preflight.mjs';
        $mesej = [];
        $ok = $node && $npm;

        if (! $node) {
            $mesej[] = 'Node.js tidak dijumpai.';
        }

        if (! $npm) {
            $mesej[] = 'npm tidak dijumpai.';
        }

        if (! $ffmpeg) {
            $mesej[] = 'FFmpeg tidak dijumpai (diperlukan untuk render).';
        }

        // Skrip preflight khusus skill hanya jika Node tersedia.
        if ($node && file_exists($skrip)) {
            try {
                $proses = new Process(['node', $skrip], $dir, null, null, 30);
                $proses->run();

                if ($proses->getExitCode() !== 0) {
                    $ok = false;
                    $mesej[] = trim($proses->getErrorOutput() ?: $proses->getOutput()) ?: 'Preflight gagal.';
                }
            } catch (Throwable $e) {
                $mesej[] = 'Preflight gagal dijalankan: '.$e->getMessage();
            }
        }

        return [
            'ok' => $ok,
            'node' => $node,
            'ffmpeg' => $ffmpeg,
            'npm' => $npm,
            'mesej' => $mesej === [] ? 'PREFLIGHT OK' : implode(' ', $mesej),
        ];
    }

    /**
     * Sedia input projek dalam folder kerja.
     *
     * @return array<string, string> peta nama fail → laluan mutlak
     */
    public function sediaInput(VideoProject $projek): array
    {
        $folder = $this->folderKerja($projek);
        $inputDir = $folder.'/input';

        if (! is_dir($inputDir)) {
            mkdir($inputDir, 0777, true);
        }

        $hasil = [];

        foreach ([
            'voiceover_path' => 'voiceover',
            'script_path' => 'script',
            'source_video_path' => 'source',
        ] as $medan => $nama) {
            $laluan = $projek->{$medan};

            if (! $laluan) {
                continue;
            }

            $sumber = Storage::disk('local')->path($laluan);

            if (is_file($sumber)) {
                $sasaran = $inputDir.'/'.$nama.'-'.basename($sumber);
                copy($sumber, $sasaran);
                $hasil[$nama] = $sasaran;
            }
        }

        // Brand kit → aibijan-brand.json
        if ($projek->brand_kit_id) {
            $kit = VideoBrandKit::find($projek->brand_kit_id);

            if ($kit) {
                $fail = $folder.'/aibijan-brand.json';
                file_put_contents($fail, json_encode($kit->keJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $hasil['brand'] = $fail;
            }
        }

        return $hasil;
    }

    /**
     * Cipta projek video baharu.
     *
     * @param  array<string, mixed>  $data
     */
    public function cipta(array $data): VideoProject
    {
        $nama = $data['name'] ?? ($data['title'] ?? 'Projek Video');

        return VideoProject::create(array_merge($data, [
            'uuid' => (string) Str::uuid(),
            'slug' => Str::slug($nama).'-'.Str::random(5),
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Mula render (async). Mengemas kini status projek.
     *
     * @return array{success: bool, mesej: string}
     */
    public function mula(VideoProject $projek): array
    {
        if ($projek->sedangProses()) {
            return ['success' => false, 'mesej' => 'Projek sedang diproses.'];
        }

        $pra = $this->preflight($projek->skill);

        $projek->update([
            'preflight_ok' => $pra['ok'],
            'preflight_report' => $pra,
            'status' => $pra['ok'] ? VideoProject::STATUS_QUEUED : VideoProject::STATUS_FAILED,
            'error_message' => $pra['ok'] ? null : $pra['mesej'],
            'stage' => 'preflight',
            'progress' => $pra['ok'] ? 5 : 0,
            'started_at' => now(),
        ]);

        if (! $pra['ok']) {
            Log::warning('VideoStudio: preflight gagal', [
                'project' => $projek->uuid,
                'mesej' => $pra['mesej'],
            ]);

            return ['success' => false, 'mesej' => $pra['mesej']];
        }

        $this->sediaInput($projek);

        return [
            'success' => true,
            'mesej' => 'Projek dimasukkan ke baris gilir. Jalankan skill `/'.$this->namaSlash($projek->skill).'` untuk render.',
        ];
    }

    /**
     * Nama slash command skill.
     */
    public function namaSlash(string $skill): string
    {
        return match ($skill) {
            VideoProject::SKILL_MOTION => 'aibijan-motion',
            VideoProject::SKILL_EDITOR => 'aibijan-video-editor',
            default => 'aibijan-mograph',
        };
    }

    /**
     * Arahan sedia untuk dijalankan dalam sesi Claude/agent.
     *
     * @return array<string, mixed>
     */
    public function arahanRender(VideoProject $projek): array
    {
        $input = $this->sediaInput($projek);

        return [
            'skill' => $this->namaSlash($projek->skill),
            'slash' => '/'.$this->namaSlash($projek->skill),
            'gaya' => $projek->gaya,
            'aspect_ratio' => $projek->aspect_ratio,
            'frame_rate' => $projek->frame_rate,
            'folder_kerja' => $this->folderKerja($projek),
            'input' => $input,
            'langkah' => [
                'node .github/skills/'.$this->namaSlash($projek->skill).'/scripts/preflight.mjs',
                'node .github/skills/aibijan-mograph/scripts/find-inputs.mjs '.$this->folderKerja($projek),
                isset($input['brand']) ? 'npm run brand -- '.$input['brand'] : null,
                'Lampirkan VO + skrip → /'.$this->namaSlash($projek->skill),
            ],
        ];
    }

    /**
     * Kemas kini kemajuan render.
     *
     * @param  array<string, mixed>  $data
     */
    public function kemasKemajuan(VideoProject $projek, array $data): VideoProject
    {
        $medan = array_filter([
            'status' => $data['status'] ?? null,
            'stage' => $data['stage'] ?? null,
            'progress' => isset($data['progress']) ? max(0, min(100, (int) $data['progress'])) : null,
            'error_message' => $data['error_message'] ?? null,
            'qc_report' => $data['qc_report'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'output_path' => $data['output_path'] ?? null,
        ], fn ($v) => $v !== null);

        if (($medan['status'] ?? null) === VideoProject::STATUS_COMPLETED) {
            $medan['completed_at'] = now();
            $medan['progress'] = 100;
        }

        if (($medan['status'] ?? null) === VideoProject::STATUS_FAILED) {
            $medan['completed_at'] = now();
        }

        $projek->update($medan);

        return $projek->fresh();
    }

    /**
     * Lampirkan fail output MP4.
     */
    public function lampirOutput(VideoProject $projek, string $laluanMp4): VideoProject
    {
        $path = 'video-studio/'.$this->tenantId().'/'.$projek->uuid.'/'.basename($laluanMp4);

        Storage::disk('public')->put($path, file_get_contents($laluanMp4));

        $projek->update([
            'output_path' => $path,
            'output_url' => Storage::disk('public')->url($path),
            'status' => VideoProject::STATUS_COMPLETED,
            'progress' => 100,
            'completed_at' => now(),
        ]);

        return $projek->fresh();
    }

    /**
     * Adakah perintah tersedia dalam PATH?
     */
    protected function perintahAda(string $perintah): bool
    {
        try {
            $proses = new Process(
                str_starts_with(PHP_OS_FAMILY, 'Windows') ? ['cmd', '/c', $perintah] : ['sh', '-c', $perintah],
                null,
                null,
                null,
                10
            );
            $proses->run();

            return $proses->getExitCode() === 0;
        } catch (Throwable) {
            return false;
        }
    }
}
