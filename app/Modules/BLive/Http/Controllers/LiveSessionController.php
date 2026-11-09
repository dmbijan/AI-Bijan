<?php

namespace App\Modules\BLive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BLive\Models\LiveMessage;
use App\Modules\BLive\Models\LiveSession;
use App\Modules\BLive\Models\StreamDestination;
use App\Modules\BLive\Services\HostBrainService;
use App\Modules\BLive\Services\LiveControlService;
use App\Modules\BLive\Services\LiveSessionService;
use App\Modules\BLive\Services\StreamingGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiveSessionController extends Controller
{
    public function __construct(
        private LiveSessionService $sesiService,
        private LiveControlService $kawalan,
        private StreamingGatewayService $gateway,
        private HostBrainService $brain,
    ) {}

    /**
     * Senarai sesi live.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->sesiService->senarai([
            'status' => $request->input('status'),
            'avatar_id' => $request->input('avatar_id'),
            'limit' => $request->input('limit', 100),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Butiran sesi live.
     */
    public function show(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sesi,
            'meta' => [
                'streaming' => $this->gateway->status($sesi),
                'tempoh_minit' => $sesi->tempohMenit(),
            ],
        ]);
    }

    /**
     * Cipta sesi live.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:180'],
            'avatar_id' => ['nullable', 'integer', 'exists:blive_avatars,id'],
            'host_personality_id' => ['nullable', 'integer', 'exists:blive_host_personalities,id'],
            'session_mode' => ['nullable', 'string', 'in:ai_assisted,ai_autonomous,human_led'],
            'autonomy_level' => ['nullable', 'string', 'in:supervised,full'],
            'resolution' => ['nullable', 'string', 'max:20'],
            'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
            'fps' => ['nullable', 'integer', 'min:15', 'max:60'],
            'bitrate_kbps' => ['nullable', 'integer', 'min:500', 'max:15000'],
            'language' => ['nullable', 'string', 'max:5'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
            'scheduled_at' => ['nullable', 'date'],
            'settings' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->sesiService->cipta($v->validated());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi live berjaya dicipta.',
            'data' => $hasil['session'],
        ], 201);
    }

    /**
     * Mulakan sesi live.
     */
    public function start(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $hasil = $this->sesiService->mula($sesi);

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
                'meta' => ['code' => $hasil['code'] ?? null],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi live bermula.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Hentikan sesi live.
     */
    public function stop(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $jumlahDisambung = $this->gateway->hentiSemua($sesi);

        $hasil = $this->sesiService->henti($sesi);

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi live dihentikan.',
            'data' => $hasil['session'],
            'meta' => ['sasaran_dihentikan' => $jumlahDisambung],
        ]);
    }

    /**
     * Ambil alih - manusia mengambil alih mikrofon.
     */
    public function takeover(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $hasil = $this->kawalan->ambilAlih($sesi, auth()->id());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Anda kini mengawal hos.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Lepaskan takeover - kembali kepada AI.
     */
    public function release(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $hasil = $this->kawalan->lepaskan($sesi, auth()->id());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kawalan dikembalikan kepada AI.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Jeda AI.
     */
    public function pauseAi(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $hasil = $this->kawalan->jedaAi($sesi, auth()->id());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Respons AI dijeda.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Sambung semula AI.
     */
    public function resumeAi(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $hasil = $this->kawalan->sambungAi($sesi, auth()->id());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Respons AI disambung semula.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Beri arahan suara kepada hos.
     */
    public function speak(int $id, Request $request): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $v = Validator::make($request->all(), [
            'instruction' => ['required', 'string', 'min:1', 'max:2000'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->kawalan->cakap(
            $sesi,
            $v->validated()['instruction'],
            auth()->id(),
            array_filter($v->validated())
        );

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Arahan telah dihantar kepada hos.',
            'data' => $hasil['message'],
        ]);
    }

    /**
     * Henti kecemasan.
     */
    public function emergencyStop(int $id, Request $request): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $this->gateway->hentiSemua($sesi);

        $hasil = $this->kawalan->hentiKecemasan($sesi, auth()->id(), $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Henti kecemasan diaktifkan. Semua output AI dihentikan.',
            'data' => $hasil['session'],
        ]);
    }

    /**
     * Sambung sasaran penstriman.
     */
    public function connectChannel(int $id, Request $request): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $v = Validator::make($request->all(), [
            'platform' => ['required', 'string', 'max:30'],
            'channel_name' => ['nullable', 'string', 'max:120'],
            'channel_reference' => ['nullable', 'string', 'max:255'],
            'credential_reference' => ['required', 'string', 'max:255'],
            'stream_key_reference' => ['nullable', 'string', 'max:255'],
            'ingest_url' => ['nullable', 'string', 'max:500'],
            'bitrate_kbps' => ['nullable', 'integer', 'min:500', 'max:15000'],
            'resolution' => ['nullable', 'string', 'max:20'],
            'fps' => ['nullable', 'integer', 'min:15', 'max:60'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->gateway->sambung($sesi, $v->validated());

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sasaran penstriman berjaya disambung.',
            'data' => $hasil['destination'],
        ], 201);
    }

    /**
     * Senarai sasaran penstriman.
     */
    public function channels(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->gateway->status($sesi),
            'meta' => ['platform_aktif' => array_keys($this->gateway->platformAktif())],
        ]);
    }

    /**
     * Mulakan penstriman ke satu sasaran.
     */
    public function startChannel(int $id, int $destinationId): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $destination = StreamDestination::query()
            ->where('live_session_id', $sesi->id)
            ->findOrFail($destinationId);

        $hasil = $this->gateway->mula($destination);

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Penstriman bermula.',
            'data' => $hasil['destination'],
        ]);
    }

    /**
     * Terima komen penonton dan jana jawapan hos.
     */
    public function comment(int $id, Request $request): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $v = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:1000'],
            'platform' => ['nullable', 'string', 'max:30'],
            'external_id' => ['nullable', 'string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $komen = $this->kawalan->terimaKomen(
            $sesi,
            $v->validated()['content'],
            $v->validated()['platform'] ?? null,
            $v->validated()['external_id'] ?? null,
        );

        $jawab = $this->brain->jawab([
            'content' => $komen->content,
            'platform' => $komen->platform,
        ], $sesi);

        if ($jawab['success'] && $jawab['should_speak']) {
            $this->kawalan->tandaiDijawab($komen);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'komen' => $komen,
                'jawapan' => $jawab['content'],
                'intent' => $jawab['intent'],
                'keyakinan' => $jawab['confidence'],
                'perlu_eskalasi' => $jawab['escalate_to_human'],
                'sebab' => $jawab['reason'],
            ],
        ]);
    }

    /**
     * Senarai mesej dalam sesi.
     */
    public function messages(int $id, Request $request): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        $query = LiveMessage::query()
            ->where('live_session_id', $sesi->id)
            ->orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    /**
     * Analisis prestasi sesi.
     */
    public function metrics(int $id): JsonResponse
    {
        $sesi = LiveSession::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->sesiService->ringkasan($sesi),
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
