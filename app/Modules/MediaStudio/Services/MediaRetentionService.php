<?php

namespace App\Modules\MediaStudio\Services;

use App\Modules\MediaStudio\Models\MediaAsset;
use App\Modules\MediaStudio\Models\MediaJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * MediaRetentionService — purge aset yang tamat tempoh.
 *
 * Dijalankan oleh scheduler harian (`media-retention-purge`).
 */
class MediaRetentionService
{
    /**
     * Purge aset yang retention_expires_at < now.
     *
     * @return array{purged: int, errors: int}
     */
    public function purgeExpired(?int $tenantId = null): array
    {
        $batch = (int) config('media_studio.retention.purge_batch_size', 100);

        $query = MediaAsset::query()
            ->whereNotNull('retention_expires_at')
            ->where('retention_expires_at', '<', now())
            ->whereNull('deleted_at');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $assets = $query->limit($batch)->get();

        $purged = 0;
        $errors = 0;

        foreach ($assets as $asset) {
            try {
                // Padam fail fizikal dahulu
                Storage::disk($asset->storage_disk)->delete($asset->storage_path);

                // Tandakan dipadam (soft delete — jejak audit dikekalkan)
                $asset->update([
                    'deleted_at' => now(),
                    'meta' => array_merge((array) $asset->meta, ['purged' => true]),
                ]);

                $purged++;
            } catch (\Throwable $e) {
                $errors++;
                Log::warning('MediaStudio: purge aset gagal', [
                    'asset_id' => $asset->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($purged > 0) {
            Log::info('MediaStudio: purge retensi selesai', [
                'purged' => $purged,
                'errors' => $errors,
            ]);
        }

        return ['purged' => $purged, 'errors' => $errors];
    }

    /**
     * Tetapkan tarikh luput bagi satu aset.
     */
    public function setRetention(MediaAsset $asset, int $days): MediaAsset
    {
        $asset->update([
            'retention_expires_at' => now()->addDays($days),
        ]);

        return $asset;
    }
}
