<?php

namespace App\Modules\ScreenRecorder\Services;

use App\Modules\ScreenRecorder\Models\RecordingComment;
use App\Modules\ScreenRecorder\Models\ScreenRecording;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ScreenRecorderService — rakaman skrin & perkongsian.
 *
 * inspired oleh https://github.com/CapSoftware/Cap
 *
 * Menyediakan:
 *   - rakaman skrin + audio yang dimuat naik ke storan
 *   - pautan perkongsian dengan token (public / unlisted / private)
 *   - komen bercapah masa pada timeline
 *   - sokongan integrasi dengan modul `MediaEditor` untuk pemotongan
 */
class ScreenRecorderService
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
     * Daftar rakaman baharu selepas dimuat naik.
     *
     * @param  array<string, mixed>  $data
     */
    public function registerRecording(array $data): ScreenRecording
    {
        return ScreenRecording::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'share_token' => Str::random(32),
            'status' => ScreenRecording::STATUS_UPLOADING,
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Selesaikan prosesan rakaman.
     *
     * @param  array<string, mixed>  $data
     */
    public function completeRecording(ScreenRecording $recording, array $data): ScreenRecording
    {
        $recording->update(array_merge([
            'status' => ScreenRecording::STATUS_READY,
            'completed_at' => now(),
        ], $data));

        return $recording->fresh();
    }

    /**
     * Tandakan rakaman gagal.
     */
    public function failRecording(ScreenRecording $recording, string $mesej): ScreenRecording
    {
        $recording->update([
            'status' => ScreenRecording::STATUS_FAILED,
            'error_message' => $mesej,
            'completed_at' => now(),
        ]);

        return $recording->fresh();
    }

    /**
     * Simpan fail rakaman ke storan awam.
     */
    public function storeFile(ScreenRecording $recording, string $sumberPath, string $extension = 'mp4'): ?string
    {
        if (! is_file($sumberPath)) {
            return null;
        }

        $path = sprintf('recordings/%d/%s.%s', $this->tenantId(), $recording->uuid, $extension);

        Storage::disk('public')->put($path, file_get_contents($sumberPath));

        return $path;
    }

    /**
     * Kumpul pautan perkongsian untuk satu rakaman.
     *
     * @return array<string, mixed>
     */
    public function shareLink(ScreenRecording $recording): array
    {
        $base = rtrim((string) config('app.url'), '/');

        return [
            'token' => $recording->share_token,
            'url' => $base.'/share/recording/'.$recording->share_token,
            'embed_url' => $base.'/embed/recording/'.$recording->share_token,
            'privacy' => $recording->privacy,
            'boleh_kongsi' => $recording->bolehKongsi(),
            'view_count' => $recording->view_count,
            'comment_count' => $recording->comment_count,
        ];
    }

    /**
     * Cari rakaman melalui token perkongsian (untuk halaman awam).
     */
    public function findByShareToken(string $token): ?ScreenRecording
    {
        $recording = ScreenRecording::query()
            ->where('share_token', $token)
            ->where('privacy', '!=', ScreenRecording::PRIVACY_PRIVATE)
            ->first();

        if ($recording) {
            $recording->increment('view_count');
        }

        return $recording;
    }

    /**
     * Tambah komen pada masa tertentu dalam rakaman.
     *
     * @param  array<string, mixed>  $data
     */
    public function addComment(ScreenRecording $recording, array $data): RecordingComment
    {
        $komen = RecordingComment::create([
            'recording_id' => $recording->id,
            'body' => $data['body'] ?? '',
            'timestamp_seconds' => (float) ($data['timestamp_seconds'] ?? 0),
            'x_position' => (float) ($data['x_position'] ?? 0),
            'y_position' => (float) ($data['y_position'] ?? 0),
            'created_by' => $data['created_by'] ?? auth()->id(),
            'created_by_name' => $data['created_by_name'] ?? auth()->user()?->name,
        ]);

        $recording->increment('comment_count');

        return $komen;
    }

    /**
     * Senarai komen mengikut masa.
     */
    public function comments(ScreenRecording $recording): Collection
    {
        return $recording->comments()->orderBy('timestamp_seconds')->get();
    }

    /**
     * Ubah tahap privasi rakaman.
     */
    public function setPrivacy(ScreenRecording $recording, string $privacy): ScreenRecording
    {
        $privacy = in_array($privacy, array_keys(ScreenRecording::privasi()), true)
            ? $privacy
            : ScreenRecording::PRIVACY_PRIVATE;

        $recording->update(['privacy' => $privacy]);

        return $recording->fresh();
    }

    /**
     * Jana semula token perkongsian (pautan lama tidak lagi berfungsi).
     */
    public function regenerateToken(ScreenRecording $recording): ScreenRecording
    {
        $recording->update(['share_token' => Str::random(32)]);

        return $recording->fresh();
    }

    /**
     * Buang rakaman dan failnya.
     */
    public function deleteRecording(ScreenRecording $recording): void
    {
        foreach ([$recording->recording_path, $recording->thumbnail_path] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $recording->delete();
    }
}
