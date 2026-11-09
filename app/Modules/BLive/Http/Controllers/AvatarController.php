<?php

namespace App\Modules\BLive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\ConsentRecord;
use App\Modules\BLive\Services\AvatarService;
use App\Modules\BLive\Services\ConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvatarController extends Controller
{
    public function __construct(
        private AvatarService $avatar,
        private ConsentService $consent,
    ) {}

    /**
     * Senarai avatar.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->avatar->senarai([
            'status' => $request->input('status'),
            'avatar_type' => $request->input('avatar_type'),
            'consent_status' => $request->input('consent_status'),
            'limit' => $request->input('limit', 100),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'jenis' => [Avatar::TYPE_AI_GENERATED, Avatar::TYPE_PHOTO, Avatar::TYPE_DIGITAL_TWIN],
                'enjin' => Avatar::enjin(),
                'ciri_penampilan' => Avatar::ciriPenampilan(),
            ],
        ]);
    }

    /**
     * Butiran avatar.
     */
    public function show(int $id): JsonResponse
    {
        $avatar = Avatar::findOrFail($id);

        $semak = $this->avatar->semakKelayakan($avatar);

        return response()->json([
            'success' => true,
            'data' => $avatar,
            'meta' => [
                'boleh_live' => $semak['ok'],
                'sebab' => $semak['reason'],
                'kode' => $semak['code'],
            ],
        ]);
    }

    /**
     * Cipta avatar baharu.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar_type' => ['nullable', 'string', 'in:ai_generated,photo_to_avatar,custom_digital_twin'],
            'engine' => ['nullable', 'string', 'max:40'],
            'voice_profile_id' => ['nullable', 'integer'],
            'host_personality_id' => ['nullable', 'integer', 'exists:blive_host_personalities,id'],
            'render_quality' => ['nullable', 'string', 'in:low,medium,high,ultra'],
            'appearance' => ['nullable', 'array'],
            'body_traits' => ['nullable', 'array'],
            'is_ai_disclosed' => ['nullable', 'boolean'],
            'subject_name' => ['nullable', 'string', 'max:120'],
            'subject_reference' => ['nullable', 'string', 'max:255'],
            'consent_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->avatar->cipta($v->validated());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avatar berjaya dicipta.',
            'data' => $hasil['avatar'],
        ], 201);
    }

    /**
     * Jana avatar melalui enjin render.
     */
    public function generate(int $id, Request $request): JsonResponse
    {
        $avatar = Avatar::findOrFail($id);

        $v = Validator::make($request->all(), [
            'appearance' => ['nullable', 'array'],
            'body_traits' => ['nullable', 'array'],
            'engine' => ['nullable', 'string', 'max:40'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        if ($v->validated()['engine'] ?? null) {
            $avatar->update(['engine' => $v->validated()['engine']]);
        }

        $hasil = $this->avatar->jana($avatar->fresh(), array_filter($v->validated()));

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
                'meta' => ['job_id' => $hasil['job']?->id],
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avatar berjaya dijana.',
            'data' => $hasil['avatar'],
            'meta' => ['job_id' => $hasil['job']?->id, 'engine' => $hasil['job']?->engine],
        ]);
    }

    /**
     * Kemas kini avatar.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $avatar = Avatar::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'render_quality' => ['nullable', 'string', 'in:low,medium,high,ultra'],
            'appearance' => ['nullable', 'array'],
            'body_traits' => ['nullable', 'array'],
            'voice_profile_id' => ['nullable', 'integer'],
            'host_personality_id' => ['nullable', 'integer', 'exists:blive_host_personalities,id'],
            'engine' => ['nullable', 'string', 'max:40'],
            'is_ai_disclosed' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $avatar = $this->avatar->kemasKini($avatar, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Avatar berjaya dikemas kini.',
            'data' => $avatar,
        ]);
    }

    /**
     * Arkib avatar.
     */
    public function destroy(int $id): JsonResponse
    {
        $avatar = Avatar::findOrFail($id);
        $avatar = $this->avatar->arkib($avatar);

        return response()->json([
            'success' => true,
            'message' => 'Avatar berjaya diarkib.',
            'data' => $avatar,
        ]);
    }

    /**
     * Senarai rekod kebenaran.
     */
    public function consents(Request $request): JsonResponse
    {
        $query = ConsentRecord::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('avatar_id')) {
            $query->where('avatar_id', (int) $request->input('avatar_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    /**
     * Luluskan kebenaran.
     */
    public function grantConsent(int $id): JsonResponse
    {
        $rekod = ConsentRecord::findOrFail($id);
        $rekod = $this->consent->luluskan($rekod);

        return response()->json([
            'success' => true,
            'message' => 'Kebenaran berjaya diluluskan.',
            'data' => $rekod,
        ]);
    }

    /**
     * Tarik balik kebenaran.
     */
    public function revokeConsent(int $id, Request $request): JsonResponse
    {
        $rekod = ConsentRecord::findOrFail($id);
        $rekod = $this->consent->tarikBalik($rekod, $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'Kebenaran berjaya ditarik balik.',
            'data' => $rekod,
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
