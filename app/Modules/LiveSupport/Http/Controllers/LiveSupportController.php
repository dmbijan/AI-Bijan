<?php

namespace App\Modules\LiveSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LiveSupport\Models\LiveSupportCall;
use App\Modules\LiveSupport\Models\LiveSupportChannel;
use App\Modules\LiveSupport\Services\LiveSupportService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSupportController extends Controller
{
    public function __construct(private LiveSupportService $service) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    // Saluran
    public function channels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => LiveSupportChannel::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get()
                ->each(fn ($c) => $c->makeHidden(['config'])),
        ]);
    }

    public function simpanChannel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'channel_type' => ['nullable', 'in:voice,video,chat,whatsapp_call'],
            'provider' => ['nullable', 'string', 'max:80'],
            'inbound_number' => ['nullable', 'string', 'max:40'],
            'esim_iccid' => ['nullable', 'string', 'max:40'],
            'esim_provider' => ['nullable', 'string', 'max:80'],
            'config' => ['nullable', 'array'],
            'ai_enabled' => ['nullable', 'boolean'],
            'human_fallback' => ['nullable', 'boolean'],
            'ai_confidence_threshold' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'max_concurrent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'business_hours' => ['nullable', 'array'],
        ]);

        $channel = LiveSupportChannel::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        $channel->makeHidden(['config']);

        return response()->json(['success' => true, 'data' => $channel], 201);
    }

    // Panggilan
    public function calls(Request $request): JsonResponse
    {
        $query = LiveSupportCall::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('started_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function terimaPanggilan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'integer'],
            'external_call_id' => ['nullable', 'string', 'max:120'],
            'caller_number' => ['nullable', 'string', 'max:40'],
            'caller_name' => ['nullable', 'string', 'max:180'],
            'direction' => ['nullable', 'in:inbound,outbound'],
        ]);

        $call = $this->service->terimaPanggilan(
            $this->tenantId(),
            $validated['channel_id'],
            $validated
        );

        return response()->json(['success' => true, 'data' => $call], 201);
    }

    public function tambahTranskrip(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'speaker' => ['required', 'in:caller,ai,human'],
            'text' => ['required', 'string', 'max:5000'],
            'confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'offset_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $transcript = $this->service->tambahTranskrip($this->tenantId(), $id, $validated);

        return response()->json(['success' => true, 'data' => $transcript], 201);
    }

    public function eskalasi(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return response()->json([
            'success' => true,
            'data' => $this->service->eskalasi($this->tenantId(), $id, $validated['reason'] ?? null),
        ]);
    }

    public function tamat(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->tamatPanggilan($this->tenantId(), $id),
        ]);
    }

    public function butiran(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => LiveSupportCall::query()
                ->with('transcripts')
                ->where('tenant_id', $this->tenantId())
                ->findOrFail($id),
        ]);
    }

    public function ringkasan(Request $request): JsonResponse
    {
        $hari = min(max((int) $request->integer('days', 30), 1), 365);

        return response()->json([
            'success' => true,
            'data' => $this->service->ringkasan($this->tenantId(), $hari),
        ]);
    }
}
