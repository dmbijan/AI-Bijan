<?php

namespace App\Modules\Blast\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Blast\Services\BlastService;
use App\Modules\Blast\Services\DeliverabilityService;
use App\Modules\Blast\Services\OptOutService;
use App\Modules\Blast\Services\RateLimiterService;
use App\Modules\Blast\Services\TemplateService;
use App\Modules\Prospecting\Models\Prospect;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlastController extends Controller
{
    public function __construct(
        private BlastService $blast,
        private OptOutService $optOut,
        private RateLimiterService $limiter,
        private DeliverabilityService $deliverability
    ) {
    }

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai kempen.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlastCampaign::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel')->toString());
        }

        $campaigns = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $campaigns->items(),
            'meta' => [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'total' => $campaigns->total(),
            ],
        ]);
    }

    /**
     * Butiran kempen + statistik mesej.
     */
    public function show(int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        $stats = BlastMessage::query()
            ->where('tenant_id', $this->tenantId())
            ->where('campaign_id', $campaign->id)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'stats' => $stats,
        ]);
    }

    /**
     * Cipta kempen blast baharu.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'channel' => ['required', 'in:email,sms,whatsapp,video,multi'],
            'subject' => ['nullable', 'string', 'max:250'],
            'body' => ['required', 'string', 'max:50000'],
            'media_asset_id' => ['nullable', 'integer'],
            'sequence_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaign = BlastCampaign::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'media_asset_id' => $validated['media_asset_id'] ?? null,
            'sequence_id' => $validated['sequence_id'] ?? null,
            'status' => BlastCampaign::STATUS_DRAFT,
            'approval_state' => 'not_required',
            'autonomy_level' => (int) config('blast.default_autonomy_level', 0),
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $campaign], 201);
    }

    /**
     * Kemas kini kempen (hanya bila masih draf).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        if ($campaign->status !== BlastCampaign::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kempen draf boleh dikemas kini',
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:250'],
            'body' => ['sometimes', 'string', 'max:50000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaign->update($validated);

        return response()->json(['success' => true, 'data' => $campaign->fresh()]);
    }

    /**
     * Bina pesanan untuk kempen daripada senarai prospect.
     */
    public function buildAudience(Request $request, int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        $validated = $request->validate([
            'prospect_ids' => ['nullable', 'array'],
            'prospect_ids.*' => ['integer'],
            'min_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', 'string'],
            'list_id' => ['nullable', 'integer'],
            'template' => ['nullable', 'string', 'max:50000'],
        ]);

        $query = Prospect::query()->where('tenant_id', $this->tenantId());

        if (! empty($validated['prospect_ids'])) {
            $query->whereIn('id', $validated['prospect_ids']);
        }

        if (isset($validated['min_score'])) {
            $query->where('score', '>=', $validated['min_score']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['list_id'])) {
            $query->where('list_id', $validated['list_id']);
        }

        $prospects = $query->limit(10000)->get();

        $result = $this->blast->binaPesanan($campaign, $prospects, $validated['template'] ?? null);

        return response()->json([
            'success' => true,
            'message' => sprintf(
                '%d mesej dijana, %d dilangkau (consent/penindihan/had kadar)',
                $result['queued'],
                $result['skipped']
            ),
            'data' => [
                'queued' => $result['queued'],
                'skipped' => $result['skipped'],
                'details' => array_slice($result['details'], 0, 50),
            ],
        ]);
    }

    /**
     * Lulus kempen (perlu untuk penghantaran).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        if ($campaign->status !== BlastCampaign::STATUS_DRAFT
            && $campaign->status !== BlastCampaign::STATUS_REVIEW) {
            return response()->json([
                'success' => false,
                'message' => 'Kempen tidak boleh diluluskan pada status '.$campaign->status,
            ], 422);
        }

        // Semak sekurang-kurangnya ada mesej
        $messageCount = BlastMessage::where('tenant_id', $this->tenantId())
            ->where('campaign_id', $campaign->id)
            ->where('status', BlastMessage::STATUS_QUEUED)
            ->count();

        if ($messageCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tiada mesej untuk dihantar. Bina audiens dahulu.',
            ], 422);
        }

        $campaign->update([
            'status' => BlastCampaign::STATUS_APPROVED,
            'approval_state' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $campaign->fresh()]);
    }

    /**
     * Jadualkan penghantaran.
     */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        if (! $campaign->bolehDihantar()) {
            return response()->json([
                'success' => false,
                'message' => 'Kempen mesti diluluskan dahulu',
            ], 422);
        }

        $validated = $request->validate([
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'start_now' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['start_now'])) {
            $campaign->update([
                'status' => BlastCampaign::STATUS_RUNNING,
                'started_at' => now(),
            ]);
            $this->dispatchQueues($campaign);
        } else {
            $campaign->update([
                'status' => BlastCampaign::STATUS_SCHEDULED,
                'scheduled_at' => $validated['scheduled_at'] ?? now(),
            ]);
        }

        return response()->json(['success' => true, 'data' => $campaign->fresh()]);
    }

    /**
     * Jeda / sambung / batal kempen.
     */
    public function control(Request $request, int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:pause,resume,cancel'],
        ]);

        match ($validated['action']) {
            'pause' => $campaign->update(['status' => BlastCampaign::STATUS_PAUSED]),
            'resume' => $campaign->update(['status' => BlastCampaign::STATUS_RUNNING]),
            'cancel' => $campaign->update(['status' => BlastCampaign::STATUS_CANCELLED]),
        };

        return response()->json(['success' => true, 'data' => $campaign->fresh()]);
    }

    /**
     * Statistik kempen (penghantaran, bounce, open, click).
     */
    public function stats(int $id): JsonResponse
    {
        $campaign = BlastCampaign::find($id);

        if (! $campaign) {
            return response()->json(['success' => false, 'message' => 'Kempen tidak dijumpai'], 404);
        }

        $base = BlastMessage::query()
            ->where('tenant_id', $this->tenantId())
            ->where('campaign_id', $campaign->id);

        $total = (clone $base)->count();
        $sent = (clone $base)->whereIn('status', ['sent', 'delivered'])->count();
        $delivered = (clone $base)->where('status', 'delivered')->count();
        $bounced = (clone $base)->where('status', 'bounced')->count();
        $opened = (clone $base)->whereNotNull('opened_at')->count();
        $clicked = (clone $base)->whereNotNull('clicked_at')->count();
        $replied = (clone $base)->whereNotNull('replied_at')->count();
        $skipped = (clone $base)->whereIn('status', ['skipped', 'suppressed'])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'jumlah' => $total,
                'dihantar' => $sent,
                'delivered' => $delivered,
                'bounce' => $bounced,
                'dibuka' => $opened,
                'diklik' => $clicked,
                'dibalas' => $replied,
                'dilangkau' => $skipped,
                'kadar_hantar' => $total > 0 ? round($sent / $total * 100, 2) : 0,
                'kadar_bounce' => $sent > 0 ? round($bounced / $sent * 100, 2) : 0,
                'kadar_buka' => $sent > 0 ? round($opened / $sent * 100, 2) : 0,
                'kadar_klik' => $sent > 0 ? round($clicked / $sent * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Senarai mesej kempen.
     */
    public function messages(Request $request, int $id): JsonResponse
    {
        $query = BlastMessage::query()
            ->where('tenant_id', $this->tenantId())
            ->where('campaign_id', $id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $messages = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => ['total' => $messages->total()],
        ]);
    }

    /**
     * Preview mesej dengan merge tag (tanpa menghantar).
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:50000'],
            'subject' => ['nullable', 'string', 'max:250'],
            'prospect_id' => ['nullable', 'integer'],
        ]);

        $data = [
            'nama' => 'Ahmad',
            'nama_penuh' => 'Ahmad bin Ali',
            'syarikat' => 'Syarikat Contoh Sdn Bhd',
            'jawatapan' => 'Pengarahbitmap',
            'kota' => 'Kuala Lumpur',
            'negara' => 'MY',
            'tawaran' => 'audit operasi percuma',
            'tarikh' => now()->format('d M Y'),
            'unsubscribe_link' => $this->blast->unsubscribeUrl($this->tenantId()),
        ];

        if (! empty($validated['prospect_id'])) {
            $prospect = Prospect::find($validated['prospect_id']);
            if ($prospect) {
                $data = array_merge($data, TemplateService::dataDaripadaProspect($prospect));
            }
        }

        $template = new TemplateService();
        $template = $template->dengan($data);

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => $template->render($validated['subject'] ?? ''),
                'body' => $template->render($validated['body']),
                'tags' => TemplateService::availableTags(),
            ],
        ]);
    }

    /**
     * Senarai penindihan.
     */
    public function suppressions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->optOut->senarai($this->tenantId()),
        ]);
    }

    /**
     * Tambah penindihan manual.
     */
    public function addSuppression(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'channel' => ['required', 'in:email,sms,whatsapp,video'],
            'reason' => ['nullable', 'string', 'max:100'],
        ]);

        $this->optOut->suppress(
            $this->tenantId(),
            $validated['email'] ?? null,
            $validated['phone'] ?? null,
            $validated['channel'],
            $validated['reason'] ?? 'manual'
        );

        return response()->json(['success' => true]);
    }

    /**
     * Semak DNS deliverability domain.
     */
    public function dnsCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:190'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->deliverability->semakDns($validated['domain']),
        ]);
    }

    /**
     * Maklumat had kadar semasa.
     */
    public function quota(): JsonResponse
    {
        $tenantId = $this->tenantId();

        return response()->json([
            'success' => true,
            'data' => [
                'sent_today' => $this->limiter->countHariIni($tenantId),
                'daily_per_tenant' => (int) config('blast.quotas.daily_per_tenant'),
                'remaining' => max(0, (int) config('blast.quotas.daily_per_tenant') - $this->limiter->countHariIni($tenantId)),
                'per_second' => config('blast.quotas.per_second'),
            ],
        ]);
    }

    /**
     * Hantar mesej berbaris ke queue.
     */
    private function dispatchQueues(BlastCampaign $campaign): int
    {
        $messages = BlastMessage::query()
            ->where('tenant_id', $campaign->tenant_id)
            ->where('campaign_id', $campaign->id)
            ->where('status', BlastMessage::STATUS_QUEUED)
            ->limit((int) config('blast.quotas.batch_size'))
            ->get();

        foreach ($messages as $message) {
            \App\Jobs\Blast\SendBlastMessageJob::dispatch($message->id);
        }

        return $messages->count();
    }
}
