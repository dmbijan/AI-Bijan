<?php

namespace App\Modules\SocialBridge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SocialBridge\Models\SocialAccount;
use App\Modules\SocialBridge\Models\SocialPost;
use App\Modules\SocialBridge\Services\SocialBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SocialBridgeController extends Controller
{
    public function __construct(private SocialBridgeService $bridge) {}

    /**
     * Senarai akaun sosial.
     */
    public function accounts(Request $request): JsonResponse
    {
        $query = SocialAccount::query()->orderBy('platform');

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
            'meta' => ['platform' => SocialAccount::senaraiPlatform()],
        ]);
    }

    /**
     * Sambung akaun sosial.
     */
    public function connectAccount(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'platform' => ['required', 'string', 'in:x,instagram,linkedin,facebook,tiktok,youtube,bluesky,threads,pinterest,google_business'],
            'account_name' => ['required', 'string', 'max:120'],
            'handle' => ['nullable', 'string', 'max:80'],
            'external_id' => ['nullable', 'string', 'max:120'],
            'access_token' => ['nullable', 'string', 'max:2000'],
            'refresh_token' => ['nullable', 'string', 'max:2000'],
            'token_expires_at' => ['nullable', 'date'],
            'is_brand' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $akaun = SocialAccount::create(array_merge($v->validated(), [
            'status' => SocialAccount::STATUS_ACTIVE,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Akaun sosial disambungkan.',
            'data' => $akaun,
        ], 201);
    }

    /**
     * Putuskan sambungan akaun.
     */
    public function disconnectAccount(int $id): JsonResponse
    {
        $akaun = SocialAccount::findOrFail($id);
        $akaun->update(['status' => SocialAccount::STATUS_REVOKED]);

        return response()->json([
            'success' => true,
            'message' => 'Akaun diputuskan.',
        ]);
    }

    /**
     * Senarai catatan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SocialPost::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->with('sasaran')->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    /**
     * Cipta catatan.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:250'],
            'body' => ['required', 'string', 'max:63206'],
            'type' => ['nullable', 'string', 'in:text,image,video,carousel,story'],
            'media' => ['nullable', 'array', 'max:'.config('social_bridge.limits.max_media_per_post', 10)],
            'scheduled_at' => ['nullable', 'date'],
            'per_platform_overrides' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'account_ids' => ['nullable', 'array'],
            'account_ids.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $akaunIds = $data['account_ids'] ?? [];
        unset($data['account_ids']);

        $catatan = $this->bridge->ciptaCatatan($data);

        $sasaran = $akaunIds === []
            ? ['sedia' => 0, 'dilangkau' => 0]
            : $this->bridge->sediakanSasaran($catatan, $akaunIds);

        return response()->json([
            'success' => true,
            'message' => 'Catatan dicipta.',
            'data' => [
                'post' => $catatan->fresh()->load('sasaran'),
                'sasaran' => $sasaran,
            ],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SocialPost::with('sasaran')->findOrFail($id),
        ]);
    }

    /**
     * Jadualkan catatan.
     */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'scheduled_at' => ['required', 'date'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $catatan = SocialPost::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Catatan dijadualkan.',
            'data' => $this->bridge->jadualkan($catatan, Carbon::parse($v->validated()['scheduled_at'])),
        ]);
    }

    /**
     * Siarkan sekarang.
     */
    public function publish(int $id): JsonResponse
    {
        $catatan = SocialPost::findOrFail($id);
        $hasil = $this->bridge->siarkan($catatan);

        return response()->json([
            'success' => $hasil['gagal'] === 0,
            'message' => $hasil['gagal'] === 0
                ? 'Catatan disiarkan ke semua platform.'
                : $hasil['berjaya'].' berjaya, '.$hasil['gagal'].' gagal.',
            'data' => [
                'berjaya' => $hasil['berjaya'],
                'gagal' => $hasil['gagal'],
                'butiran' => $hasil['butiran'],
                'post' => $catatan->fresh()->load('sasaran'),
            ],
        ]);
    }

    /**
     * Ringkasan.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bridge->ringkasan(),
        ]);
    }
}
