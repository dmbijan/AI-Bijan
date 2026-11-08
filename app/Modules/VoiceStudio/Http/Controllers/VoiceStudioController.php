<?php

namespace App\Modules\VoiceStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\VoiceStudio\Models\VoiceProfile;
use App\Modules\VoiceStudio\Models\VoiceTrack;
use App\Modules\VoiceStudio\Services\VoiceEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VoiceStudioController extends Controller
{
    public function __construct(private VoiceEngineService $voice) {}

    /**
     * Senarai profil suara.
     */
    public function index(Request $request): JsonResponse
    {
        $query = VoiceProfile::query()->orderByDesc('is_default')->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
            'meta' => [
                'bahasa' => VoiceProfile::bahasa(),
                'gaya' => VoiceProfile::gaya(),
            ],
        ]);
    }

    /**
     * Cip profil suara baharu.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'language' => ['nullable', 'string', 'max:5'],
            'locale' => ['nullable', 'string', 'max:10'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age_range' => ['nullable', 'string', 'max:30'],
            'pitch' => ['nullable', 'numeric', 'min:0.5', 'max:2.0'],
            'speed' => ['nullable', 'numeric', 'min:0.25', 'max:2.0'],
            'timbre' => ['nullable', 'string', 'max:60'],
            'emotion' => ['nullable', 'string', 'max:40'],
            'engine' => ['nullable', 'string', 'max:40'],
            'engine_voice_id' => ['nullable', 'string', 'max:120'],
            'sample_path' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);

        if (! empty($data['is_default'])) {
            VoiceProfile::query()->update(['is_default' => false]);
        }

        $profile = VoiceProfile::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil suara berjaya dicipta.',
            'data' => $profile,
        ], 201);
    }

    /**
     * Kemas kini profil suara.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $profile = VoiceProfile::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'language' => ['nullable', 'string', 'max:5'],
            'pitch' => ['nullable', 'numeric', 'min:0.5', 'max:2.0'],
            'speed' => ['nullable', 'numeric', 'min:0.25', 'max:2.0'],
            'timbre' => ['nullable', 'string', 'max:60'],
            'emotion' => ['nullable', 'string', 'max:40'],
            'engine' => ['nullable', 'string', 'max:40'],
            'engine_voice_id' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:draft,active,archived'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        if (! empty($v->validated()['is_default'])) {
            VoiceProfile::query()->where('id', '!=', $profile->id)->update(['is_default' => false]);
        }

        $profile->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profil suara berjaya dikemas kini.',
            'data' => $profile->fresh(),
        ]);
    }

    /**
     * Buang profil suara.
     */
    public function destroy(int $id): JsonResponse
    {
        $profile = VoiceProfile::findOrFail($id);
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profil suara berjaya dibuang.',
        ]);
    }

    /**
     * Sintesis suara daripada teks.
     */
    public function synthesize(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'voice_profile_id' => ['required', 'integer', 'exists:voice_profiles,id'],
            'text' => ['required', 'string', 'min:1', 'max:'.config('voice_studio.limits.max_characters', 5000)],
            'engine' => ['nullable', 'string', 'max:40'],
            'format' => ['nullable', 'string', 'in:mp3,wav'],
            'model_id' => ['nullable', 'string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $profile = VoiceProfile::findOrFail($v->validated()['voice_profile_id']);

        $hasil = $this->voice->synthesize($v->validated()['text'], $profile, array_filter($v->validated()));

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
                'meta' => ['track_id' => $hasil['track']?->id],
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sintesis suara berjaya.',
            'data' => [
                'track_id' => $hasil['track']->id,
                'uuid' => $hasil['track']->uuid,
                'audio_url' => $hasil['url'],
                'duration_seconds' => $hasil['track']->duration_seconds,
                'cost_rm' => $hasil['track']->cost_rm,
            ],
        ]);
    }

    /**
     * Senarai rekod sintesis.
     */
    public function tracks(Request $request): JsonResponse
    {
        $query = VoiceTrack::query()->orderByDesc('created_at');

        if ($request->filled('voice_profile_id')) {
            $query->where('voice_profile_id', $request->input('voice_profile_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    /**
     * Butiran satu rekod.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => VoiceTrack::findOrFail($id),
        ]);
    }

    /**
     * Ringkasan penggunaan suara.
     */
    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_profil' => VoiceProfile::count(),
                'jumlah_track' => VoiceTrack::count(),
                'jumlah_karakter' => (int) VoiceProfile::sum('total_characters'),
                'jumlah_kos' => round((float) VoiceTrack::sum('cost_rm'), 4),
                'jumlah_menit' => round((float) VoiceTrack::sum('duration_seconds') / 60, 2),
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
