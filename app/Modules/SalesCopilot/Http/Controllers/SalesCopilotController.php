<?php

namespace App\Modules\SalesCopilot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Prospecting\Models\Prospect;
use App\Modules\SalesCopilot\Services\DealBriefService;
use App\Modules\SalesCopilot\Services\FollowUpNudgerService;
use App\Modules\SalesCopilot\Services\ReplyAnalyzerService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesCopilotController extends Controller
{
    public function __construct(
        private DealBriefService $briefService,
        private ReplyAnalyzerService $replyAnalyzer,
        private FollowUpNudgerService $nudger
    ) {
    }

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Brief prospect — 1 halaman untuk eksekutif.
     */
    public function brief(int $prospectId): JsonResponse
    {
        $brief = $this->briefService->brief($this->tenantId(), $prospectId);

        if (! $brief) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        return response()->json(['success' => true, 'data' => $brief]);
    }

    /**
     * Analisis balasan tanpa memproses (dry-run).
     */
    public function analyzeReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:10000'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->replyAnalyzer->analyze($validated['reply']),
        ]);
    }

    /**
     * Proses balasan prospect — kemas kini status & consent.
     */
    public function processReply(Request $request, int $prospectId): JsonResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:10000'],
            'blast_message_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $this->tenantId();

        $prospect = Prospect::query()
            ->where('tenant_id', $tenantId)
            ->find($prospectId);

        if (! $prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        $message = null;
        if (! empty($validated['blast_message_id'])) {
            $message = BlastMessage::query()
                ->where('tenant_id', $tenantId)
                ->find($validated['blast_message_id']);
        }

        $result = $this->replyAnalyzer->prosesBalasan($prospect, $validated['reply'], $message);

        return response()->json([
            'success' => true,
            'data' => $result,
            'prospect' => $prospect->fresh(),
        ]);
    }

    /**
     * Prospect yang perlukan perhatian.
     */
    public function requiresAttention(Request $request): JsonResponse
    {
        $limit = min((int) $request->integer('limit', 10), 50);

        return response()->json([
            'success' => true,
            'data' => $this->briefService->requiresAttention($this->tenantId(), $limit),
        ]);
    }

    /**
     * Cadangan follow-up.
     */
    public function followUpQueue(Request $request): JsonResponse
    {
        $limit = min((int) $request->integer('limit', 20), 100);

        return response()->json([
            'success' => true,
            'data' => $this->nudger->cadangFollowUp($this->tenantId(), $limit),
        ]);
    }
}
