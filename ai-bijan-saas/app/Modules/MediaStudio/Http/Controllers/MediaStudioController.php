<?php

namespace App\Modules\MediaStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MediaStudio\Models\MediaAsset;
use App\Modules\MediaStudio\Models\MediaJob;
use App\Modules\MediaStudio\Models\MediaPersona;
use App\Modules\MediaStudio\Services\ConsentGateService;
use App\Modules\MediaStudio\Services\MediaModerationService;
use App\Modules\MediaStudio\Services\MediaRetentionService;
use App\Modules\MediaStudio\Services\VideoUnderstandingService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaStudioController extends Controller
{
    public function __construct(
        private VideoUnderstandingService $understanding,
        private MediaModerationService $moderation,
        private ConsentGateService $consentGate,
        private MediaRetentionService $retention
    ) {
    }

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai aset media.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MediaAsset::query()
            ->where('tenant_id', $this->tenantId())
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($request->filled('kind')) {
            $query->where('kind', $request->string('kind')->toString());
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source')->toString());
        }

        $assets = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $assets->items(),
            'meta' => ['total' => $assets->total()],
        ]);
    }

    /**
     * Muat naik fail media.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.(int) config('media_studio.limits.max_upload_mb', 500),
                'mimes:jpg,jpeg,png,webp,mp4,mov,avi,mkv,mp3,wav,pdf,doc,docx',
            ],
            'kind' => ['nullable', 'in:image,video,audio,document'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $tenantId = $this->tenantId();
        $file = $request->file('file');

        $mime = $file->getMimeType();
        $kind = $request->input('kind') ?: $this->tekaKind($mime);

        // Path wajib tenant-isolated
        $path = $file->store("{$tenantId}/".now()->format('Y/m'), config('media_studio.disk', 'media'));

        $retentionDays = (int) $request->input(
            'retention_days',
            config('media_studio.retention.default_days', 90)
        );

        $asset = MediaAsset::create([
            'tenant_id' => $tenantId,
            'kind' => $kind,
            'storage_disk' => config('media_studio.disk', 'media'),
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'source' => MediaAsset::SOURCE_UPLOAD,
            'retention_expires_at' => now()->addDays($retentionDays),
        ]);

        return response()->json(['success' => true, 'data' => $asset], 201);
    }

    /**
     * Jalankan pemahaman video.
     */
    public function understand(Request $request, int $id): JsonResponse
    {
        $asset = MediaAsset::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak dijumpai'], 404);
        }

        $job = $this->understanding->fahami($asset, $this->tenantId(), auth()->id());

        return response()->json(['success' => true, 'data' => $job]);
    }

    /**
     * Tanya-jawab atas video.
     */
    public function qa(Request $request, int $id): JsonResponse
    {
        $asset = MediaAsset::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak dijumpai'], 404);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $understanding = $asset->understanding;

        // Cuba jqf matching QA pairs yang sedia ada
        if ($understanding && $understanding->qa_pairs) {
            $soalan = mb_strtolower($validated['question']);
            foreach ($understanding->qa_pairs as $pair) {
                $q = mb_strtolower((string) ($pair['question'] ?? ''));
                if ($q !== '' && str_contains($soalan, $q)) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'question' => $pair['question'],
                            'answer' => $pair['answer'],
                            'source' => 'qa_pairs',
                            'confidence' => 0.9,
                        ],
                    ]);
                }
            }
        }

        // Fallback — cari dalam transkripsi
        $transcript = $asset->transcript;
        $answer = null;

        if ($transcript && $transcript->text) {
            $keywords = $this->kataKunci($validated['question']);
            $answer = $this->cariDalamTeks((string) $transcript->text, $keywords);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'question' => $validated['question'],
                'answer' => $answer ?: 'Tiada jawapanejazi dijumpai dalam transkripsi video ini.',
                'source' => $answer ? 'transcript' : 'not_found',
            ],
        ]);
    }

    /**
     * Butiran aset dengan pemahaman.
     */
    public function show(int $id): JsonResponse
    {
        $asset = MediaAsset::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak dijumpai'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $asset,
            'understanding' => $asset->understanding,
            'transcript' => $asset->transcript ? [
                'language' => $asset->transcript->language,
                'confidence' => $asset->transcript->confidence,
                'text' => mb_substr((string) $asset->transcript->text, 0, 2000),
            ] : null,
        ]);
    }

    /**
     * Jana video (stub provider — memerlukan konfigurasi provider).
     */
    public function generateVideo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'script' => ['required', 'string', 'max:5000'],
            'persona_id' => ['required', 'integer', 'exists:media_personas,id'],
            'media_asset_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:150'],
        ]);

        $tenantId = $this->tenantId();

        $persona = MediaPersona::query()
            ->where('tenant_id', $tenantId)
            ->find($validated['persona_id']);

        // PAGAR: persona mesti disahkan
        $semak = $this->consentGate->semakPersona($persona);

        if (! $semak['ok']) {
            return response()->json([
                'success' => false,
                'message' => 'Persona tidak layak: '.$semak['reason'],
            ], 403);
        }

        // Moderasi skrip
        $moderation = $this->moderation->semakOutput($validated['script']);

        if (! $moderation['passed']) {
            return response()->json([
                'success' => false,
                'message' => 'Skrip gagal moderasi',
                'violations' => $moderation['violations'],
            ], 422);
        }

        // Tambah watermark
        $skrip = $this->moderation->denganWatermark($validated['script']);

        $provider = config('media_studio.providers.'.config('media_studio.generation.default_provider', 'heygen'));

        if (! ($provider['enabled'] ?? false) || ! ($provider['key'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider penjanaan video tidak dikonfigurasi. Job disimpan sebagai draf.',
            ], 422);
        }

        $job = MediaJob::create([
            'tenant_id' => $tenantId,
            'kind' => MediaJob::KIND_GENERATION,
            'status' => MediaJob::STATUS_QUEUED,
            'persona_id' => $persona->id,
            'media_asset_id' => $validated['media_asset_id'] ?? null,
            'params' => [
                'script' => $skrip,
                'name' => $validated['name'] ?? null,
                'watermark' => $moderation['watermark'],
            ],
            'approval_required' => true,
            'autonomy_level' => 0,
            'requested_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $job], 201);
    }

    /**
     * Status job media.
     */
    public function job(Request $request, int $id): JsonResponse
    {
        $job = MediaJob::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'message' => 'Job tidak dijumpai'], 404);
        }

        return response()->json(['success' => true, 'data' => $job]);
    }

    /**
     * Lulus job media.
     */
    public function approveJob(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $job = MediaJob::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'message' => 'Job tidak dijumpai'], 404);
        }

        $job->update([
            'status' => $validated['decision'] === 'approved'
                ? MediaJob::STATUS_QUEUED
                : MediaJob::STATUS_FAILED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'error' => $validated['decision'] === 'rejected'
                ? ($validated['reason'] ?? 'Ditolak oleh pengguna')
                : null,
        ]);

        return response()->json(['success' => true, 'data' => $job->fresh()]);
    }

    /**
     * Senarai persona.
     */
    public function personas(): JsonResponse
    {
        $personas = MediaPersona::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $personas]);
    }

    /**
     * Daftar persona baharu (perlu pengesahan sebelum boleh guna).
     */
    public function storePersona(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar_ref' => ['nullable', 'string', 'max:190'],
            'voice_ref' => ['nullable', 'string', 'max:190'],
            'language' => ['nullable', 'string', 'max:8'],
            'consent_consent_id' => ['nullable', 'integer'],
        ]);

        $persona = MediaPersona::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'avatar_ref' => $validated['avatar_ref'] ?? null,
            'voice_ref' => $validated['voice_ref'] ?? null,
            'language' => $validated['language'] ?? 'ms',
            'consent_consent_id' => $validated['consent_consent_id'] ?? null,
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $persona], 201);
    }

    /**
     * Sahkan persona (selepas semakan manual).
     */
    public function verifyPersona(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $persona = MediaPersona::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $persona) {
            return response()->json(['success' => false, 'message' => 'Persona tidak dijumpai'], 404);
        }

        $persona->update([
            'verification_status' => $validated['approved'] ? 'verified' : 'rejected',
            'verified_at' => $validated['approved'] ? now() : null,
            'is_active' => $validated['approved'],
        ]);

        return response()->json(['success' => true, 'data' => $persona->fresh()]);
    }

    /**
     * Tangkapan skrin dengan masking.
     */
    public function screenCapture(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:20480'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenantId = $this->tenantId();
        $file = $request->file('image');

        $path = $file->store("{$tenantId}/screenshots/".now()->format('Y/m'), config('media_studio.disk', 'media'));

        $asset = MediaAsset::create([
            'tenant_id' => $tenantId,
            'kind' => MediaAsset::KIND_IMAGE,
            'storage_disk' => config('media_studio.disk', 'media'),
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'source' => MediaAsset::SOURCE_SCREEN,
            'meta' => ['note' => $request->input('note')],
            'retention_expires_at' => now()->addDays((int) config('media_studio.retention.default_days', 90)),
        ]);

        return response()->json(['success' => true, 'data' => $asset], 201);
    }

    /**
     * Teka jenis media daripada MIME.
     */
    private function tekaKind(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'video/') => MediaAsset::KIND_VIDEO,
            str_starts_with($mime, 'audio/') => MediaAsset::KIND_AUDIO,
            str_starts_with($mime, 'image/') => MediaAsset::KIND_IMAGE,
            default => MediaAsset::KIND_DOCUMENT,
        };
    }

    /**
     * Kata kunci daripada soalan.
     */
    private function kataKunci(string $soalan): array
    {
        $kata = preg_split('/\s+/u', mb_strtolower($soalan), -1, PREG_SPLIT_NO_EMPTY);

        // Buang kata henti
        $henti = ['apa', 'apakah', 'yang', 'untuk', 'dalam', 'pada', 'dengan', 'adalah', 'itu', 'ini', 'the', 'a', 'of', 'in', 'on', 'is', 'what', 'how', 'when', 'where'];

        return array_values(array_filter($kata, fn ($k) => mb_strlen($k) > 3 && ! in_array($k, $henti, true)));
    }

    /**
     * Cari ayat paling relevan dalam transkripsi.
     */
    private function cariDalamTeks(string $teks, array $keywords): ?string
    {
        if (empty($keywords)) {
            return null;
        }

        $ayat = preg_split('/(?<=[.!?])\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY);
        $terbaik = null;
        $skorTerbaik = 0;

        foreach ($ayat as $a) {
            $skor = 0;
            foreach ($keywords as $k) {
                if (str_contains(mb_strtolower($a), $k)) {
                    $skor++;
                }
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $a;
            }
        }

        return $terbaik;
    }
}
