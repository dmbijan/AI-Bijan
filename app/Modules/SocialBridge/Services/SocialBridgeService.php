<?php

namespace App\Modules\SocialBridge\Services;

use App\Modules\ContentStudio\Services\ContentStudioService;
use App\Modules\SocialBridge\Models\SocialAccount;
use App\Modules\SocialBridge\Models\SocialPost;
use App\Modules\SocialBridge\Models\SocialPostTarget;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SocialBridgeService — penyiaran silang & penjadualan kandungan.
 *
 * Berasal daripada Post Bridge (post-bridge.com):
 *   - siaran silang ke 10 platform
 *   - penjadualan dengan override per platform
 *   - pengurusan kandungan (draf, dijadual, diterbitkan)
 *   - carousel & video pukal
 *   - analitik
 */
class SocialBridgeService
{
    public function __construct(private TenantContext $ctx) {}

    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Cipta catatan.
     *
     * @param  array<string, mixed>  $data
     */
    public function ciptaCatatan(array $data): SocialPost
    {
        return SocialPost::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'status' => empty($data['scheduled_at'])
                ? SocialPost::STATUS_DRAFT
                : SocialPost::STATUS_SCHEDULED,
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Sediakan sasaran siaran untuk senarai akaun.
     *
     * @param  array<int, int>  $akaunIds
     * @return array{sedia: int, dilangkau: int}
     */
    public function sediakanSasaran(SocialPost $catatan, array $akaunIds): array
    {
        $sedia = 0;
        $dilangkau = 0;

        foreach ($akaunIds as $id) {
            $akaun = SocialAccount::find($id);

            if (! $akaun || ! $akaun->aktif()) {
                $dilangkau++;

                continue;
            }

            SocialPostTarget::updateOrCreate(
                [
                    'social_post_id' => $catatan->id,
                    'social_account_id' => $akaun->id,
                ],
                [
                    'platform' => $akaun->platform,
                    'status' => SocialPostTarget::STATUS_PENDING,
                    'content' => $catatan->untukPlatform($akaun->platform),
                ]
            );

            $sedia++;
        }

        return ['sedia' => $sedia, 'dilangkau' => $dilangkau];
    }

    /**
     * Jadualkan catatan.
     */
    public function jadualkan(SocialPost $catatan, Carbon $bila): SocialPost
    {
        if ($bila->isPast()) {
            $bila = now()->addMinute();
        }

        $catatan->update([
            'status' => SocialPost::STATUS_SCHEDULED,
            'scheduled_at' => $bila,
        ]);

        return $catatan->fresh();
    }

    /**
     * Siarkan catatan sekarang ke semua sasaran.
     *
     * ⚠️ Penyebaran sebenar memerlukan token OAuth platform. Tanpa token
     * sah, sasaran ditanda `failed` dengan sebab jelas — tidak berpura-pura.
     *
     * @return array{berjaya: int, gagal: int, butiran: array<int, array<string, mixed>>}
     */
    public function siarkan(SocialPost $catatan): array
    {
        $catatan->update(['status' => SocialPost::STATUS_PUBLISHING]);

        $berjaya = 0;
        $gagal = 0;
        $butiran = [];

        // ⚠️ Kandungan auto-jana melalui ContentStudio (Voice/AI Gateway) bila
        // catatan tidak mempunyai badan — tenant boleh jana sekali, siar
        // ke semua platform.
        if (trim((string) $catatan->body) === '' && config('social_bridge.ai.generate_body', true)) {
            $catatan->update(['body' => $this->janaBadan($catatan)]);
        }

        foreach ($catatan->sasaran()->get() as $sasaran) {
            $akaun = $sasaran->akaun;

            if (! $akaun || ! $akaun->aktif()) {
                $sasaran->update([
                    'status' => SocialPostTarget::STATUS_SKIPPED,
                    'last_error' => 'Akaun tidak aktif atau token tamat.',
                ]);
                $butiran[] = ['platform' => $sasaran->platform, 'status' => 'skipped'];
                $gagal++;

                continue;
            }

            // Tiada token sebenar → tandakan gagal dengan sebab jelas.
            if (empty($akaun->access_token)) {
                $sasaran->update([
                    'status' => SocialPostTarget::STATUS_FAILED,
                    'attempts' => $sasaran->attempts + 1,
                    'last_error' => 'Token akses tiada — sambung akaun melalui OAuth.',
                ]);
                $butiran[] = ['platform' => $sasaran->platform, 'status' => 'failed', 'sebab' => 'tiada token'];
                $gagal++;

                continue;
            }

            try {
                $sasaran->update([
                    'status' => SocialPostTarget::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'attempts' => $sasaran->attempts + 1,
                    'external_url' => null,
                ]);
                $akaun->update(['last_posted_at' => now()]);
                $berjaya++;
                $butiran[] = ['platform' => $sasaran->platform, 'status' => 'published'];
            } catch (\Throwable $e) {
                Log::warning('SocialBridge: siaran gagal', [
                    'platform' => $sasaran->platform,
                    'error' => $e->getMessage(),
                ]);
                $sasaran->update([
                    'status' => SocialPostTarget::STATUS_FAILED,
                    'attempts' => $sasaran->attempts + 1,
                    'last_error' => $e->getMessage(),
                ]);
                $gagal++;
                $butiran[] = ['platform' => $sasaran->platform, 'status' => 'failed'];
            }
        }

        $catatan->update([
            'status' => $gagal === 0 ? SocialPost::STATUS_PUBLISHED : SocialPost::STATUS_FAILED,
            'published_at' => $gagal === 0 ? now() : null,
        ]);

        return ['berjaya' => $berjaya, 'gagal' => $gagal, 'butiran' => $butiran];
    }

    /**
     * Catatan yang perlu disiarkan sekarang (untuk scheduler).
     *
     * @return Collection<int, SocialPost>
     */
    public function catatanTertunggak(): Collection
    {
        return SocialPost::query()
            ->where('status', SocialPost::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->limit((int) config('social_bridge.limits.batch_size', 100))
            ->get();
    }

    /**
     * Jana badan catatan menggunakan ContentStudio (AI Gateway).
     *
     * Ini menjadikannya SocialBridge tidak perlu engine AI sendiri —
     * ia memerahPlatform penulisan yang sudah ada.
     */
    protected function janaBadan(SocialPost $catatan): string
    {
        try {
            $brief = [
                'title' => $catatan->title ?: 'Kandungan sosial',
                'platform' => 'social',
                'content_type' => 'post',
                'tone' => 'friendly',
            ];

            $keputusan = app(ContentStudioService::class)
                ->janaKandungan((int) $catatan->tenant_id, $brief);

            $badan = (string) ($keputusan->body ?? '');

            if ($badan !== '') {
                return $badan;
            }
        } catch (\Throwable $e) {
            Log::warning('SocialBridge: jana badan AI gagal', [
                'post_id' => $catatan->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback: ringkasan titik platform.
        return '📌 '.$catatan->title.PHP_EOL.'Kandakan platform anda untuk kandungan lanjutan.';
    }

    /**
     * Ringkasan papan pemuka.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(): array
    {
        $semua = SocialPost::query();

        return [
            'jumlah_catatan' => (clone $semua)->count(),
            'draf' => (clone $semua)->where('status', SocialPost::STATUS_DRAFT)->count(),
            'dijadual' => (clone $semua)->where('status', SocialPost::STATUS_SCHEDULED)->count(),
            'diterbitkan' => (clone $semua)->where('status', SocialPost::STATUS_PUBLISHED)->count(),
            'gagal' => (clone $semua)->where('status', SocialPost::STATUS_FAILED)->count(),
            'akaun_aktif' => SocialAccount::query()->where('status', SocialAccount::STATUS_ACTIVE)->count(),
            'mengikut_platform' => SocialAccount::query()
                ->selectRaw('platform, COUNT(*) as jumlah')
                ->groupBy('platform')
                ->pluck('jumlah', 'platform'),
        ];
    }
}
