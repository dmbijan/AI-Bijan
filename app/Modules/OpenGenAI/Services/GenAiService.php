<?php

namespace App\Modules\OpenGenAI\Services;

use App\Modules\OpenGenAI\Models\GenAiJob;
use App\Modules\OpenGenAI\Models\GenAiProvider;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GenAiService — orkestrasi penjanaan generatif.
 *
 * Aliran:
 *   1. Pilih provider (tetap / lalai automatik / ikut keupayaan)
 *   2. Direka dalam `gen_ai_jobs` (jejak audit + kos)
 *   3. Hantar melalui ProviderAdapter
 *   4. Kemas kini status, token, kos dan output
 *
 * invaded by design: mana-mana modul AI BIJAN boleh memanggil
 * `GenAiService::text()` dan mendapat output seragam.
 */
class GenAiService
{
    public function __construct(
        private ProviderAdapter $adapter,
        private TenantContext $ctx,
    ) {}

    /**
     * Jana teks.
     *
     * @param  array<string, mixed>  $option
     */
    public function text(string $prompt, array $option = []): array
    {
        return $this->jalankan(GenAiJob::KEMAHRIRAN_TEXT, $prompt, $option);
    }

    /**
     * Jana imej (Replicate / OpenAI images).
     *
     * @param  array<string, mixed>  $option
     */
    public function imej(string $prompt, array $option = []): array
    {
        $option['capability'] = GenAiJob::KEMAHRIRAN_IMAGE;

        return $this->jalankan(GenAiJob::KEMAHRIRAN_IMAGE, $prompt, $option);
    }

    /**
     * Jana audio / suara — disambungkan ke modul VoiceStudio.
     *
     * @param  array<string, mixed>  $option
     */
    public function audio(string $prompt, array $option = []): array
    {
        $option['capability'] = GenAiJob::KEMAHRIRAN_AUDIO;

        return $this->jalankan(GenAiJob::KEMAHRIRAN_AUDIO, $prompt, $option);
    }

    /**
     * Jana video.
     *
     * @param  array<string, mixed>  $option
     */
    public function video(string $prompt, array $option = []): array
    {
        $option['capability'] = GenAiJob::KEMAHRIRAN_VIDEO;

        return $this->jalankan(GenAiJob::KEMAHRIRAN_VIDEO, $prompt, $option);
    }

    /**
     * Jalankan satu penjanaan dan rekod jejaknya.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, job: GenAiJob, output: ?string, url: ?string, error: ?string}
     */
    public function jalankan(string $kemahiran, string $prompt, array $option = []): array
    {
        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return $this->gagalWithoutJob('Tiada tenant aktif untuk penjanaan AI.');
        }

        $prompt = trim($prompt);

        if ($prompt === '') {
            return $this->gagalWithoutJob('Prompt tidak boleh kosong.');
        }

        $provider = $this->pilihProvider($kemahiran, $option);

        if (! $provider) {
            return $this->gagalWithoutJob('Tiada provider AI aktif yang menyokong kemahiran "'.$kemahiran.'".');
        }

        $job = GenAiJob::create([
            'uuid' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'model' => $option['model'] ?? $provider->default_model,
            'capability' => $kemahiran,
            'task_type' => $option['task_type'] ?? 'general',
            'status' => GenAiJob::STATUS_RUNNING,
            'prompt' => $prompt,
            'system_prompt' => $option['system_prompt'] ?? null,
            'parameters' => $this->sanitizeParameter($option),
            'correlation_id' => $option['correlation_id'] ?? (string) Str::ulid(),
            'created_by' => $option['user_id'] ?? auth()->id(),
            'started_at' => now(),
        ]);

        $mula = microtime(true);

        $hasil = $this->adapter->hantar($provider, $prompt, $option);
        $durasi = (int) ((microtime(true) - $mula) * 1000);

        $usage = $hasil['meta']['usage'] ?? [];
        $promptTokens = (int) ($usage['prompt_tokens'] ?? $usage['input_tokens'] ?? $usage['prompt_eval_count'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? $usage['output_tokens'] ?? $usage['eval_count'] ?? 0);

        $job->update([
            'status' => $hasil['success'] ? GenAiJob::STATUS_SUCCEEDED : GenAiJob::STATUS_FAILED,
            'output_text' => $hasil['text'],
            'output_url' => $hasil['url'],
            'output_meta' => $hasil['meta'],
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_rm' => $this->kirakanKos($promptTokens, $completionTokens, $kemahiran),
            'duration_ms' => $durasi,
            'error_message' => $hasil['error'],
            'completed_at' => now(),
        ]);

        if (! $hasil['success']) {
            Log::warning('OpenGenAI: penjanaan gagal', [
                'job_id' => $job->id,
                'provider_id' => $provider->id,
                'error' => $hasil['error'],
            ]);

            $provider->update([
                'last_error' => $hasil['error'],
                'last_checked_at' => now(),
            ]);
        }

        return [
            'success' => $hasil['success'],
            'job' => $job->fresh(),
            'output' => $hasil['text'],
            'url' => $hasil['url'],
            'error' => $hasil['error'],
        ];
    }

    /**
     * Pilih provider mengikut keutamaan.
     *
     * @param  array<string, mixed>  $option
     */
    public function pilihProvider(string $kemahiran, array $option = []): ?GenAiProvider
    {
        // 1. Provider khusus daripada pemanggil
        if (! empty($option['provider_id'])) {
            $pilih = GenAiProvider::find((int) $option['provider_id']);

            if ($pilih && $pilih->is_active) {
                return $pilih;
            }
        }

        // 2. Provider lalai tenant
        $lalai = GenAiProvider::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($lalai && $lalai->menyokong($kemahiran)) {
            return $lalai;
        }

        // 3. Mana-mana provider aktif yang menyokong kemahiran
        $calon = GenAiProvider::query()
            ->where('is_active', true)
            ->get();

        foreach ($calon as $p) {
            if ($p->menyokong($kemahiran)) {
                return $p;
            }
        }

        // 4. Fallback: provider lalai walaupun keupayaan tidak dinyatakan
        return $lalai ?? $calon->first();
    }

    /**
     * Senarai keupayaan yang disokong (untuk UI).
     *
     * @return array<int, string>
     */
    public static function senaraiKemahiran(): array
    {
        return [
            GenAiJob::KEMAHRIRAN_TEXT,
            GenAiJob::KEMAHRIRAN_IMAGE,
            GenAiJob::KEMAHRIRAN_VIDEO,
            GenAiJob::KEMAHRIRAN_AUDIO,
            GenAiJob::KEMAHRIRAN_CODE,
        ];
    }

    /**
     * Types provider yang disokong (untuk UI).
     *
     * @return array<string, string>
     */
    public static function jenisProvider(): array
    {
        return [
            GenAiProvider::KIND_OPENAI => 'OpenAI (serasi)',
            GenAiProvider::KIND_ANTHROPIC => 'Anthropic Claude',
            GenAiProvider::KIND_GEMINI => 'Google Gemini',
            GenAiProvider::KIND_GROQ => 'Groq',
            GenAiProvider::KIND_OLLAMA => 'Ollama (tempatan)',
            GenAiProvider::KIND_REPLICATE => 'Replicate',
            GenAiProvider::KIND_CUSTOM => 'Lain-lain (OpenAI serasi)',
        ];
    }

    /**
     * Buang parameter tidak selamat sebelum disimpan.
     *
     * @param  array<string, mixed>  $option
     * @return array<string, mixed>
     */
    protected function sanitizeParameter(array $option): array
    {
        $buang = ['api_key', 'provider', 'user_id', 'correlation_id'];

        return array_diff_key($option, array_flip($buang));
    }

    /**
     * Kirakan kos dalam RM — kadar asas mengikut kemahiran.
     */
    protected function kirakanKos(int $promptTokens, int $completionTokens, string $kemahiran): float
    {
        $kadar = (float) config("open_gen_ai.cost_rates.{$kemahiran}", 0.0001);

        return round((($promptTokens + $completionTokens) * $kadar), 6);
    }

    /**
     * Kegagalan sebelum job dicipta.
     *
     * @return array{success: bool, job: null, output: null, url: null, error: string}
     */
    protected function gagalWithoutJob(string $mesej): array
    {
        return [
            'success' => false,
            'job' => null,
            'url' => null,
            'output' => null,
            'error' => $mesej,
        ];
    }
}
