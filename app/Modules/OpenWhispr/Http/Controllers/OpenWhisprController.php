<?php

namespace App\Modules\OpenWhispr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OpenWhispr\Models\DictationSession;
use App\Modules\OpenWhispr\Models\DictationVocabulary;
use App\Modules\OpenWhispr\Services\DictationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OpenWhisprController extends Controller
{
    public function __construct(private DictationService $dictation) {}

    /**
     * Senarai sesi diktasi.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DictationSession::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    /**
     * Muat naik audio dan transkripsi.
     */
    public function transcribe(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'audio' => ['required', 'file', 'mimes:wav,mp3,m4a,ogg,webm,flac', 'max:'.((int) config('open_whispr.limits.max_audio_mb', 100) * 1024)],
            'language' => ['nullable', 'string', 'max:5'],
            'engine' => ['nullable', 'string', 'max:40'],
            'model' => ['nullable', 'string', 'max:60'],
            'title' => ['nullable', 'string', 'max:150'],
            'initial_prompt' => ['nullable', 'string', 'max:1000'],
            'target_type' => ['nullable', 'string', 'max:60'],
            'target_id' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422);
        }

        $file = $request->file('audio');
        $tenantId = (int) $this->dictation->tenantId();

        $path = $file->store("dictation/{$tenantId}", 'public');

        $hasil = $this->dictation->transcribe($path, $request->all());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
                'meta' => ['session_id' => $hasil['session']?->id],
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transkripsi berjaya.',
            'data' => [
                'session_id' => $hasil['session']->id,
                'uuid' => $hasil['session']->uuid,
                'text' => $hasil['text'],
                'raw_text' => $hasil['session']->raw_text,
                'confidence' => $hasil['session']->confidence,
                'word_count' => $hasil['session']->word_count,
                'processing_ms' => $hasil['session']->processing_ms,
            ],
        ]);
    }

    /**
     * Butiran satu sesi.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DictationSession::findOrFail($id),
        ]);
    }

    /**
     * Buang sesi diktasi dan fail audionya.
     */
    public function destroy(int $id): JsonResponse
    {
        $session = DictationSession::findOrFail($id);

        if ($session->audio_path) {
            Storage::disk('public')->delete($session->audio_path);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi diktasi berjaya dibuang.',
        ]);
    }

    /**
     * Senarai kamus tersuai.
     */
    public function vocabulary(Request $request): JsonResponse
    {
        $query = DictationVocabulary::query()->orderByDesc('weight')->orderBy('word');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 200))->get(),
        ]);
    }

    /**
     * Tambah perkataan ke kamus.
     */
    public function storeVocabulary(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'word' => ['required', 'string', 'max:80'],
            'aliases' => ['nullable', 'array'],
            'aliases.*' => ['string', 'max:80'],
            'category' => ['nullable', 'string', 'max:60'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422);
        }

        $item = DictationVocabulary::create($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Perkataan berjaya ditambah ke kamus.',
            'data' => $item,
        ], 201);
    }

    /**
     * Kemas kini atau buang entri kamus.
     */
    public function updateVocabulary(Request $request, int $id): JsonResponse
    {
        $item = DictationVocabulary::findOrFail($id);

        if ($request->input('_method') === 'DELETE' || $request->boolean('_delete')) {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Entri kamus berjaya dibuang.',
            ]);
        }

        $v = Validator::make($request->all(), [
            'word' => ['sometimes', 'string', 'max:80'],
            'aliases' => ['nullable', 'array'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'category' => ['nullable', 'string', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422);
        }

        $item->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Entri kamus berjaya dikemas kini.',
            'data' => $item->fresh(),
        ]);
    }

    /**
     * Ringkasan penggunaan diktasi.
     */
    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_sesi' => DictationSession::count(),
                'jumlah_perkataan' => (int) DictationSession::sum('word_count'),
                'jumlah_menit' => round((float) DictationSession::sum('duration_seconds') / 60, 2),
                'purata_kepastian' => round((float) DictationSession::avg('confidence'), 4),
                'bilangan_kamus' => DictationVocabulary::count(),
            ],
        ]);
    }

    /**
     * Tetapan klien (hotkey, bahasa).
     */
    public function tetapan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'client' => config('open_whispr.client'),
                'models' => config('open_whispr.models'),
                'default_language' => config('open_whispr.default_language'),
                'default_model' => config('open_whispr.default_model'),
                'engines' => array_keys(config('open_whispr.engines', [])),
            ],
        ]);
    }

    /**
     * Jana versi narasi daripada transkripsi (VoiceStudio TTS).
     */
    public function generateNarration(Request $request, int $id): JsonResponse
    {
        $sesi = DictationSession::findOrFail($id);

        $v = Validator::make($request->all(), [
            'voice_profile_id' => ['nullable', 'integer', 'exists:voice_profiles,id'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422);
        }

        $hasil = $this->dictation->janaNarasi($sesi, $v->validated()['voice_profile_id'] ?? null);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => [
                'track_id' => $hasil['track_id'],
                'audio_url' => $hasil['audio_url'],
                'session' => $sesi->fresh(),
            ],
        ], $hasil['success'] ? 200 : 422);
    }
}
