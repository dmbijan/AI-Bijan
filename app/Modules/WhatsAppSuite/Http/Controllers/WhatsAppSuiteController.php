<?php

namespace App\Modules\WhatsAppSuite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WhatsAppSuite\Models\WaAutoReply;
use App\Modules\WhatsAppSuite\Models\WaCampaign;
use App\Modules\WhatsAppSuite\Models\WaContact;
use App\Modules\WhatsAppSuite\Models\WaDevice;
use App\Modules\WhatsAppSuite\Models\WaMessage;
use App\Modules\WhatsAppSuite\Models\WaOrderNotification;
use App\Modules\WhatsAppSuite\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppSuiteController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    // ===============================================================
    // PERANTI
    // ===============================================================

    public function devices(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => WaDevice::query()->orderBy('name')->get(),
        ]);
    }

    public function storeDevice(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:30'],
            'daily_limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'warmup_enabled' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $nombor = $this->wa->normalkanNombor($data['phone_number']);

        if ($nombor === null) {
            return response()->json([
                'success' => false,
                'message' => 'Nombor telefon tidak sah (format Malaysia).',
            ], 422);
        }

        $peranti = WaDevice::create(array_merge($data, [
            'phone_number' => $nombor,
            'session_id' => 'wa-'.str()->random(12),
            'status' => WaDevice::STATUS_PAIRING,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Peranti dicipta — imbas QR untuk sambung.',
            'data' => $peranti,
        ], 201);
    }

    public function startWarmup(int $id): JsonResponse
    {
        $peranti = WaDevice::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Warm-up dimulakan.',
            'data' => $this->wa->mulaWarmup($peranti),
        ]);
    }

    // ===============================================================
    // KONTAK
    // ===============================================================

    public function contacts(Request $request): JsonResponse
    {
        $query = WaContact::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('label')) {
            $query->whereJsonContains('labels', $request->input('label'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    public function storeContact(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'max:30'],
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'labels' => ['nullable', 'array'],
            'wa_device_id' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $nombor = $this->wa->normalkanNombor($data['phone_number']);

        if ($nombor === null) {
            return response()->json([
                'success' => false,
                'message' => 'Nombor telefon tidak sah.',
            ], 422);
        }

        $pendua = WaContact::query()->where('phone_number', $nombor)->exists();

        $kontak = WaContact::updateOrCreate(
            ['phone_number' => $nombor],
            array_merge($data, [
                'phone_number' => $nombor,
                'status' => $pendua ? WaContact::STATUS_VALID : WaContact::STATUS_UNKNOWN,
                'is_duplicate' => $pendua,
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Kontak disimpan.',
            'data' => $kontak,
        ], 201);
    }

    /**
     * Pengesah nombor — buang pendua & tidak sah.
     */
    public function validateNumbers(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'numbers' => ['required', 'array', 'min:1', 'max:50000'],
            'numbers.*' => ['string', 'max:30'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        return response()->json([
            'success' => true,
            'data' => $this->wa->sahkanNombor($v->validated()['numbers']),
        ]);
    }

    // ===============================================================
    // AUTOREPLY
    // ===============================================================

    public function autoReplies(int $deviceId): JsonResponse
    {
        WaDevice::findOrFail($deviceId);

        return response()->json([
            'success' => true,
            'data' => WaAutoReply::query()
                ->where('wa_device_id', $deviceId)
                ->orderByDesc('priority')
                ->get(),
            'meta' => ['jenis' => WaAutoReply::senaraiJenis()],
        ]);
    }

    public function storeAutoReply(Request $request, int $deviceId): JsonResponse
    {
        WaDevice::findOrFail($deviceId);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:exact,contains,regex,ai,welcome,fallback'],
            'trigger' => ['nullable', 'string', 'max:500'],
            'reply' => ['required', 'string', 'max:4000'],
            'use_ai' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $peraturan = WaAutoReply::create(array_merge($v->validated(), [
            'wa_device_id' => $deviceId,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Balasan automatik dicipta.',
            'data' => $peraturan,
        ], 201);
    }

    // ===============================================================
    // KEMPEN
    // ===============================================================

    public function campaigns(Request $request): JsonResponse
    {
        $query = WaCampaign::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'wa_device_id' => ['required', 'integer', 'exists:wa_devices,id'],
            'name' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:4096'],
            'media_url' => ['nullable', 'url', 'max:500'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['string', 'max:30'],
            'delay_min_seconds' => ['nullable', 'integer', 'min:5', 'max:600'],
            'delay_max_seconds' => ['nullable', 'integer', 'min:5', 'max:900'],
            'pause_every' => ['nullable', 'integer', 'min:1', 'max:500'],
            'pause_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        $kempen = WaCampaign::create(array_merge($data, [
            'status' => empty($data['scheduled_at'])
                ? WaCampaign::STATUS_DRAFT
                : WaCampaign::STATUS_SCHEDULED,
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kempen dicipta.',
            'data' => $kempen,
        ], 201);
    }

    public function runCampaign(int $id): JsonResponse
    {
        $kempen = WaCampaign::findOrFail($id);
        $hasil = $this->wa->jalankanKempen($kempen);

        return response()->json([
            'success' => true,
            'message' => 'Kempen dibariskan.',
            'data' => [
                'queued' => $hasil['queued'],
                'skipped' => $hasil['skipped'],
                'anti_ban' => $this->wa->tetapanAntiBan($kempen),
                'campaign' => $kempen->fresh(),
            ],
        ]);
    }

    // ===============================================================
    // MESEJ & PEMBERITAHUAN
    // ===============================================================

    public function messages(Request $request): JsonResponse
    {
        $query = WaMessage::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('device_id')) {
            $query->where('wa_device_id', $request->input('device_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'wa_device_id' => ['required', 'integer', 'exists:wa_devices,id'],
            'to' => ['required', 'string', 'max:30'],
            'body' => ['required', 'string', 'max:4096'],
            'media_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $peranti = WaDevice::findOrFail($data['wa_device_id']);

        $hasil = $this->wa->hantar($peranti, $data['to'], $data['body'], [
            'media_url' => $data['media_url'] ?? null,
        ]);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => ['message_id' => $hasil['message_id']],
        ], $hasil['success'] ? 201 : 422);
    }

    public function orderNotifications(int $deviceId): JsonResponse
    {
        WaDevice::findOrFail($deviceId);

        return response()->json([
            'success' => true,
            'data' => WaOrderNotification::query()
                ->where('wa_device_id', $deviceId)
                ->get(),
            'meta' => ['pencetus' => WaOrderNotification::senaraiPencetus()],
        ]);
    }

    public function storeOrderNotification(Request $request, int $deviceId): JsonResponse
    {
        WaDevice::findOrFail($deviceId);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'trigger' => ['required', 'string', 'in:thank_you,completed,abandoned_cart,repeat_order,tracking,shipped,cancelled'],
            'source_platform' => ['nullable', 'string', 'max:40'],
            'message_template' => ['required', 'string', 'max:4000'],
            'delay_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $templat = WaOrderNotification::create(array_merge($v->validated(), [
            'wa_device_id' => $deviceId,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Templat pemberitahuan dicipta.',
            'data' => $templat,
        ], 201);
    }

    // ===============================================================
    // RINGKASAN
    // ===============================================================

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wa->ringkasan(),
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
