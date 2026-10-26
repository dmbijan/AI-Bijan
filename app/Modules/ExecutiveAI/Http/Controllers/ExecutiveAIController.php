<?php

namespace App\Modules\ExecutiveAI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExecutiveAI\Models\ExecutiveBriefing;
use App\Modules\ExecutiveAI\Models\ExecutivePreference;
use App\Modules\ExecutiveAI\Services\AutonomyService;
use App\Modules\ExecutiveAI\Services\BriefingService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExecutiveAIController extends Controller
{
    public function __construct(
        private AutonomyService $autonomy,
        private BriefingService $briefing
    ) {
    }

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Command Centre — keadaan perniagaan dalam satu pandangan.
     */
    public function commandCentre(): JsonResponse
    {
        $briefing = $this->briefing->jana('daily');

        return response()->json([
            'success' => true,
            'data' => $briefing->content,
            'autonomi' => [
                'aras' => $this->autonomy->label($this->currentLevel()),
                'level' => $this->currentLevel(),
                'peratus' => $this->autonomy->description($this->currentLevel()),
            ],
        ]);
    }

    /**
     * Tanya soalan kepada pembantu eksekutif.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $level = $this->currentLevel();

        // Jika autonomy 0, hanya boleh baca — tiada tindakan ditulis.
        $result = [
            'soalan' => $validated['question'],
            'jawapan' => null,
            'tindakan_dicadangkan' => [],
            'perlu_kelulusan' => false,
        ];

        $cadangan = $this->cadangTindakan($validated['question']);

        if ($cadangan) {
            // Bentuk senarai supaya frontend boleh iterate.
            $result['tindakan_dicadangkan'] = [$cadangan];
            $result['perlu_kelulusan'] = $this->autonomy->requiresApproval($level, $cadangan['action']);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'autonomy_level' => $level,
        ]);
    }

    /**
     * Senarai briefing.
     */
    public function briefings(Request $request): JsonResponse
    {
        $query = ExecutiveBriefing::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('generated_at');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $briefings = $query->limit(min((int) $request->integer('limit', 10), 50))->get();

        return response()->json(['success' => true, 'data' => $briefings]);
    }

    /**
     * Jana briefing baharu.
     */
    public function generateBriefing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:daily,weekly,monthly'],
        ]);

        $briefing = $this->briefing->jana($validated['type'] ?? 'daily');

        return response()->json(['success' => true, 'data' => $briefing], 201);
    }

    /**
     * Tahap autonomy semasa.
     */
    public function autonomy(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'level' => $this->currentLevel(),
                'label' => $this->autonomy->label($this->currentLevel()),
                'description' => $this->autonomy->description($this->currentLevel()),
                'tahap' => $this->autonomy->semuaTahap(),
                'perlu_kelulusan' => $this->autonomy->actionsRequiringApproval($this->currentLevel()),
            ],
        ]);
    }

    /**
     * Kemas kini tahap autonomy.
     */
    public function updateAutonomy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'autonomy_level' => ['required', 'integer', 'min:0', 'max:4'],
            'briefing_time' => ['nullable', 'string', 'max:5'],
            'briefing_enabled' => ['nullable', 'boolean'],
            'voice_enabled' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string', 'max:8'],
        ]);

        $preference = ExecutivePreference::updateOrCreate(
            [
                'tenant_id' => $this->tenantId(),
                'user_id' => auth()->id(),
            ],
            [
                'autonomy_level' => $validated['autonomy_level'],
                'briefing_time' => $validated['briefing_time'] ?? '07:30',
                'briefing_enabled' => $validated['briefing_enabled'] ?? true,
                'voice_enabled' => $validated['voice_enabled'] ?? false,
                'language' => $validated['language'] ?? 'ms',
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $preference,
            'label' => $this->autonomy->label((int) $preference->autonomy_level),
        ]);
    }

    /**
     * Tanda briefing sebagai dibaca.
     */
    public function markRead(int $id): JsonResponse
    {
        $briefing = ExecutiveBriefing::query()
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $briefing) {
            return response()->json(['success' => false, 'message' => 'Briefing tidak dijumpai'], 404);
        }

        $briefing->update(['read_at' => now()]);

        return response()->json(['success' => true, 'data' => $briefing->fresh()]);
    }

    /**
     * Tahap autonomy semasa pengguna.
     */
    private function currentLevel(): int
    {
        $preference = ExecutivePreference::query()
            ->where('tenant_id', $this->tenantId())
            ->where('user_id', auth()->id())
            ->first();

        if ($preference) {
            return $preference->autonomyLevel();
        }

        return (int) config('executive_ai.default_autonomy_level', 0);
    }

    /**
     * Cadang tindakan berdasarkan soalan (perkataan kunci ringkas).
     */
    private function cadangTindakan(string $soalan): ?array
    {
        $lower = mb_strtolower($soalan);

        $peta = [
            'hantar e-mel' => ['action' => 'send_email', 'label' => 'Hantar e-mel'],
            'hantar emel' => ['action' => 'send_email', 'label' => 'Hantar e-mel'],
            'email' => ['action' => 'draft_email', 'label' => 'Draf e-mel'],
            'emel' => ['action' => 'draft_email', 'label' => 'Draf e-mel'],
            'whatsapp' => ['action' => 'draft_whatsapp', 'label' => 'Draf WhatsApp'],
            'sms' => ['action' => 'draft_sms', 'label' => 'Draf SMS'],
            'video' => ['action' => 'draft_video', 'label' => 'Draf video'],
            'proposal' => ['action' => 'draft_proposal', 'label' => 'Draf proposal'],
            'harga' => ['action' => 'draft_price_change', 'label' => 'Draf perubahan harga'],
            'tugas' => ['action' => 'create_task', 'label' => 'Cipta tugas'],
            'task' => ['action' => 'create_task', 'label' => 'Cipta tugas'],
            'follow up' => ['action' => 'automated_followup', 'label' => 'Follow-up automasi'],
            'follow-up' => ['action' => 'automated_followup', 'label' => 'Follow-up automasi'],
        ];

        foreach ($peta as $kata => $tindakan) {
            if (str_contains($lower, $kata)) {
                return $tindakan;
            }
        }

        return null;
    }
}
