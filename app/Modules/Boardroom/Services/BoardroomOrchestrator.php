<?php

namespace App\Modules\Boardroom\Services;

use App\Modules\Boardroom\Models\BoardroomSession;
use App\Modules\Boardroom\Models\BoardroomFinding;
use Illuminate\Support\Facades\DB;

/**
 * B-Boardroom Orchestrator (BAB 20)
 *
 * Mengurus aliran cadangan 7 langkah dari persoalan pemilik
 * hingga keputusan yang direkodkan.
 */
class BoardroomOrchestrator
{
    /**
     * Langkah 1: Pemilik mengemukakan persoalan
     */
    public function openSession(int $tenantId, int $userId, string $question, array $context = []): BoardroomSession
    {
        if (trim($question) === '') {
            throw new \InvalidArgumentException('Persoalan tidak boleh kosong.');
        }

        $session = BoardroomSession::create([
            'tenant_id' => $tenantId,
            'requested_by' => $userId,
            'question' => $question,
            'category' => $context['category'] ?? $this->detectCategory($question),
            'context' => $context,
            'current_step' => 1,
            'agents_involved' => [],
            'status' => BoardroomSession::STATUS_DRAFT,
        ]);

        return $session;
    }

    /**
     * Langkah 2: Chief of Staff pilih ejen relevan
     */
    public function selectAgents(BoardroomSession $session, ?array $explicitAgents = null): BoardroomSession
    {
        if ($session->current_step < 1) {
            throw new \RuntimeException('Sesi belum dibuka.');
        }

        $agents = $explicitAgents ?? $this->determineAgents($session->category);

        $session->agents_involved = $agents;
        $session->current_step = 2;
        $session->status = BoardroomSession::STATUS_IN_PROGRESS;
        $session->save();

        return $session;
    }

    /**
     * Langkah 3: Ejen terima konteks minimum
     *
     * Hanya konteks yang diperlukan dihantar kepada setiap ejen.
     */
    public function distributeContext(BoardroomSession $session): array
    {
        if (empty($session->agents_involved)) {
            throw new \RuntimeException('Tiada ejen dipilih.');
        }

        $minimalContext = [
            'question' => $session->question,
            'category' => $session->category,
            'tenant_scope' => $session->tenant_id,
            // Hanya konteks bukan-sensitif dihantar
            'context' => $this->sanitizeContext($session->context),
        ];

        $session->current_step = 3;
        $session->save();

        return [
            'session_id' => $session->id,
            'context' => $minimalContext,
            'assigned_agents' => $session->agents_involved,
        ];
    }

    /**
     * Langkah 4: Ejen kemukakan dapatan
     *
     * Setiap dapatan MESTI ada jenis (fakta/anggaran/andaian/ramalan/cadangan).
     */
    public function recordFinding(
        BoardroomSession $session,
        string $agentKey,
        array $data
    ): BoardroomFinding {
        if (!in_array($agentKey, $session->agents_involved ?? [], true)) {
            throw new \InvalidArgumentException("Ejen '{$agentKey}' tidak terlibat dalam sesi ini.");
        }

        $type = $data['finding_type'] ?? BoardroomFinding::TYPE_RECOMMENDATION;
        $validTypes = array_keys(config('boardroom.finding_types', []));

        if (!in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException("Jenis dapatan tidak sah: {$type}");
        }

        // Semak keperluan sumber
        $requiresSource = config("boardroom.finding_types.{$type}.requires_source", false);
        if ($requiresSource && empty($data['source_reference'])) {
            throw new \InvalidArgumentException(
                "Dapatan jenis '{$type}' memerlukan rujukan sumber."
            );
        }

        $finding = BoardroomFinding::create([
            // T-111: `BelongsToTenant` juga mengisi daripada TenantContext; nilai
            // eksplisit ini memastikan pekerjaan berbaris (queue) tetap betul.
            'tenant_id' => $session->tenant_id,
            'session_id' => $session->id,
            'agent_key' => $agentKey,
            'finding_type' => $type,
            'title' => $data['title'] ?? 'Dapatan tanpa tajuk',
            'content' => $data['content'] ?? '',
            'assumptions' => $data['assumptions'] ?? null,
            'evidence' => $data['evidence'] ?? null,
            'risks' => $data['risks'] ?? null,
            'source_reference' => $data['source_reference'] ?? null,
            'confidence_score' => $data['confidence_score'] ?? 0.80,
            'requires_review' => $data['requires_review'] ?? ($type === BoardroomFinding::TYPE_ASSUMPTION),
        ]);

        $session->current_step = 4;
        $session->save();

        return $finding;
    }

    /**
     * Langkah 5: Kesan percanggahan antara ejen
     */
    public function detectConflicts(BoardroomSession $session): array
    {
        $settings = config('boardroom.conflict_detection', []);

        if (!($settings['enabled'] ?? true)) {
            return ['conflict_detected' => false, 'reason' => 'Conflict detection disabled'];
        }

        $findings = $session->findings()->get();
        $conflicts = [];

        $threshold = $settings['confidence_divergence_threshold'] ?? 0.3;

        // Kumpul dapatan mengikut tajuk/kategori yang sama
        $grouped = $findings->groupBy(fn ($f) => strtolower(trim($f->title)));

        foreach ($grouped as $title => $group) {
            if ($group->count() < 2) {
                continue;
            }

            $scores = $group->pluck('confidence_score')->map(fn ($s) => (float) $s);
            $divergence = $scores->max() - $scores->min();

            if ($divergence > $threshold) {
                $conflicts[] = [
                    'title' => $title,
                    'agents' => $group->pluck('agent_key')->toArray(),
                    'confidence_range' => [
                        'min' => $scores->min(),
                        'max' => $scores->max(),
                        'divergence' => round($divergence, 2),
                    ],
                    'findings' => $group->map(fn ($f) => [
                        'agent' => $f->agent_key,
                        'type' => $f->finding_type,
                        'confidence' => (float) $f->confidence_score,
                        'content' => $f->content,
                    ])->toArray(),
                ];
            }
        }

        // Dapatan yang berkonflik perlu semakan
        if (!empty($conflicts) && ($settings['require_review_on_conflict'] ?? true)) {
            $session->findings()
                ->whereIn('title', array_column($conflicts, 'title'))
                ->update(['requires_review' => true]);

            // Reset relation cache supaya semakan seterusnya baca data terkini
            $session->unsetRelation('findings');
        }

        $session->conflict_detected = !empty($conflicts);
        $session->conflict_details = $conflicts ?: null;
        $session->current_step = 5;

        if (!empty($conflicts)) {
            $session->status = BoardroomSession::STATUS_AWAITING_REVIEW;
            $session->review_round = ($session->review_round ?? 0) + 1;
        }

        $session->save();

        return [
            'conflict_detected' => !empty($conflicts),
            'conflicts' => $conflicts,
            'review_round' => $session->review_round,
        ];
    }

    /**
     * Langkah 6: AI CEO sediakan ringkasan keputusan
     */
    public function prepareSummary(BoardroomSession $session): BoardroomSession
    {
        $findings = $session->findings()->get();

        // Susun mengikut jenis — fakta dahulu, andaian kemudian
        $byType = [
            'fact' => $findings->where('finding_type', 'fact')->values()->toArray(),
            'estimate' => $findings->where('finding_type', 'estimate')->values()->toArray(),
            'forecast' => $findings->where('finding_type', 'forecast')->values()->toArray(),
            'assumption' => $findings->where('finding_type', 'assumption')->values()->toArray(),
            'recommendation' => $findings->where('finding_type', 'recommendation')->values()->toArray(),
        ];

        $summary = [
            'question' => $session->question,
            'agents_involved' => $session->agents_involved,
            'findings_by_type' => $byType,
            'counts' => [
                'facts' => count($byType['fact']),
                'estimates' => count($byType['estimate']),
                'forecasts' => count($byType['forecast']),
                'assumptions' => count($byType['assumption']),
                'recommendations' => count($byType['recommendation']),
            ],
            'conflicts' => $session->conflict_details,
            'disclaimer' => 'Ringkasan ini membezakan fakta, anggaran, ramalan dan andaian. '
                . 'Persetujuan ejen BUKAN bukti kebenaran. Sila semak sumber sebelum membuat keputusan.',
            'generated_at' => now()->toIso8601String(),
        ];

        $session->summary = $summary;
        $session->current_step = 6;
        $session->status = BoardroomSession::STATUS_AWAITING_OWNER_DECISION;
        $session->save();

        return $session;
    }

    /**
     * Langkah 7: Pemilik rekod keputusan
     */
    public function recordDecision(
        BoardroomSession $session,
        int $userId,
        string $decision,
        ?string $reason = null,
        ?array $options = null
    ): BoardroomSession {
        if (!$session->canRecordDecision()) {
            throw new \RuntimeException(
                "Keputusan tidak boleh direkod dalam status '{$session->status}'."
            );
        }

        $validDecisions = array_keys(config('boardroom.decision_options', []));
        if (!in_array($decision, $validDecisions, true)) {
            throw new \InvalidArgumentException("Keputusan tidak sah: {$decision}");
        }

        $session->decision = $decision;
        $session->decision_by = $userId;
        $session->decision_reason = $reason;
        $session->options = $options;
        $session->decision_at = now();
        $session->current_step = 7;

        // Defer dan request_more_info kekalkan sesi terbuka
        $session->status = in_array($decision, ['defer', 'request_more_info'], true)
            ? BoardroomSession::STATUS_IN_PROGRESS
            : BoardroomSession::STATUS_DECIDED;

        $session->save();

        return $session;
    }

    /**
     * Tutup sesi
     */
    public function close(BoardroomSession $session): BoardroomSession
    {
        $session->status = BoardroomSession::STATUS_CLOSED;
        $session->closed_at = now();
        $session->save();

        return $session;
    }

    /**
     * Batal sesi
     */
    public function cancel(BoardroomSession $session, ?string $reason = null): BoardroomSession
    {
        $session->status = BoardroomSession::STATUS_CANCELLED;
        $session->closed_at = now();
        if ($reason) {
            $session->decision_reason = "Dibatalkan: {$reason}";
        }
        $session->save();

        return $session;
    }

    // ===== Helper Methods =====

    /**
     * Kesan kategori persoalan dari teks
     */
    protected function detectCategory(string $question): string
    {
        $q = strtolower($question);

        $keywords = [
            'financial' => ['kewangan', 'cash', 'tunai', 'bajet', 'untung', 'rugi', 'margin', 'kos', 'invois', 'bayar'],
            'marketing' => ['pemasaran', 'kempen', 'iklan', 'campaign', 'marketing', 'audience', 'content'],
            'sales' => ['jualan', 'sales', 'lead', 'pipeline', 'pelanggan', 'quotation'],
            'hr' => ['pekerja', 'staff', 'cuti', 'gaji', 'hr', 'onboarding', 'shift'],
            'operations' => ['operasi', 'stok', 'inventori', 'logistik', 'penghantaran', 'produksi'],
            'risk' => ['risiko', 'risk', 'bahaya', 'ancaman', 'compliance'],
            'customer' => ['pelanggan', 'customer', 'aduan', 'support', 'tiket'],
            'research' => ['kajian', 'research', 'pesaing', 'trend', 'pasaran'],
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (str_contains($q, $word)) {
                    return $category;
                }
            }
        }

        return 'default';
    }

    /**
     * Tentukan ejen berdasarkan kategori
     */
    public function determineAgents(string $category): array
    {
        $map = config('boardroom.agent_selection', []);
        return $map[$category] ?? $map['default'] ?? ['ceo'];
    }

    /**
     * Buang data sensitif dari konteks sebelum diedarkan
     */
    protected function sanitizeContext(array $context): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'credential', 'ssn', 'credit_card'];

        $clean = [];
        foreach ($context as $key => $value) {
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->sanitizeContext($value) : $value;
        }

        return $clean;
    }

    /**
     * Statistik sesi boardroom
     */
    public function getStats(int $tenantId): array
    {
        $base = BoardroomSession::forTenant($tenantId);

        return [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->open()->count(),
            'awaiting_owner' => (clone $base)->awaitingOwner()->count(),
            'decided' => (clone $base)->byStatus(BoardroomSession::STATUS_DECIDED)->count(),
            'with_conflicts' => (clone $base)->where('conflict_detected', true)->count(),
            'by_category' => (clone $base)
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_decision' => (clone $base)
                ->whereNotNull('decision')
                ->select('decision', DB::raw('count(*) as count'))
                ->groupBy('decision')
                ->pluck('count', 'decision')
                ->toArray(),
        ];
    }
}
