<?php

namespace App\Modules\ExecutiveAI\Services;

use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\ExecutiveAI\Models\ExecutiveBriefing;
use App\Modules\Prospecting\Models\Prospect;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * BriefingService — ringkasan proaktif untuk eksekutif.
 *
 * 3 jenis: daily, weekly, monthly.
 * Menyatukan: KPI jualan, pipeline, prospek panas, risiko SLA, blast.
 */
class BriefingService
{
    /**
     * Jana briefing untuk satu pengguna.
     */
    public function jana(string $type = 'daily', ?int $userId = null): ExecutiveBriefing
    {
        $tenantId = app(TenantContext::class)->id();
        $userId = $userId ?? auth()->id();

        $kandungan = $this->binaKandungan($tenantId, $type);

        return ExecutiveBriefing::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'content' => $kandungan,
            'generated_at' => now(),
        ]);
    }

    /**
     * Bina kandungan briefing.
     */
    private function binaKandungan(int $tenantId, string $type): array
    {
        $maxItems = (int) config('executive_ai.briefing.max_items', 10);

        return [
            'ringkasan' => $this->ringkasan($tenantId, $type),
            'kpi' => $this->kpi($tenantId, $type),
            'pipeline' => $this->pipeline($tenantId),
            'prospect_panas' => $this->prospectPanas($tenantId, $maxItems),
            'risiko' => $this->risiko($tenantId),
            'blast' => $this->blast($tenantId),
            'sistem' => $this->sistem(),
        ];
    }

    /**
     * Ringkasan eksekutif — 1–3 ayat.
     */
    private function ringkasan(int $tenantId, string $type): string
    {
        $kpi = $this->kpi($tenantId, $type);
        $panas = Prospect::where('tenant_id', $tenantId)
            ->where('score', '>=', (int) config('prospecting.scoring.hot_threshold', 70))
            ->count();

        $risikoCount = count($this->risiko($tenantId));

        $bahagian = [];
        $bahagian[] = sprintf(
            '%d prospek panas,%d item risiko perlu perhatian.',
            $panas,
            $risikoCount
        );

        if (($kpi['jumlah_deal'] ?? 0) > 0) {
            $bahagian[] = sprintf(
                'Pipeline: %d deal worth RM%s.',
                $kpi['jumlah_deal'],
                number_format((float) ($kpi['nilai_pipeline'] ?? 0), 0)
            );
        }

        if (($kpi['jumlah_invois'] ?? 0) > 0) {
            $bahagian[] = sprintf('%d invois belum dijelaskan.', $kpi['jumlah_invois']);
        }

        return implode(' ', $bahagian);
    }

    /**
     * KPI utama.
     */
    private function kpi(int $tenantId, string $type): array
    {
        $since = match ($type) {
            'monthly' => now()->startOfMonth(),
            'weekly' => now()->startOfWeek(),
            default => now()->startOfDay(),
        };

        $deals = DB::table('opportunities')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->get(['id', 'estimated_value', 'stage']);

        $invois = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->count();

        $bayaranBelum = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('amount_outstanding', '>', 0)
            ->whereNotIn('status', ['paid', 'void', 'cancelled'])
            ->count();

        return [
            'jumlah_deal' => $deals->count(),
            'nilai_pipeline' => round((float) $deals->sum('estimated_value'), 2),
            'jumlah_invois' => $invois,
            'bayaran_belum' => $bayaranBelum,
        ];
    }

    /**
     * Ringkasan pipeline CRM.
     */
    private function pipeline(int $tenantId): array
    {
        $rows = DB::table('opportunities')
            ->where('tenant_id', $tenantId)
            ->selectRaw('stage, COUNT(*) as jumlah, SUM(estimated_value) as nilai')
            ->groupBy('stage')
            ->get();

        return $rows->map(fn ($r) => [
            'stage' => $r->stage,
            'jumlah' => (int) $r->jumlah,
            'nilai' => round((float) $r->nilai, 2),
        ])->toArray();
    }

    /**
     * Prospek panas yang perlu tindakan.
     */
    private function prospectPanas(int $tenantId, int $limit): array
    {
        return Prospect::query()
            ->where('tenant_id', $tenantId)
            ->where('score', '>=', (int) config('prospecting.scoring.hot_threshold', 70))
            ->orderByDesc('score')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'company', 'score', 'status', 'last_touched_at'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama' => trim($p->first_name.' '.$p->last_name),
                'syarikat' => $p->company,
                'skor' => (int) $p->score,
                'status' => $p->status,
                'dikunci' => $p->last_touched_at?->diffInDays(now()),
            ])->toArray();
    }

    /**
     * Risiko yang memerlukan perhatian.
     */
    private function risiko(int $tenantId): array
    {
        $risiko = [];

        // SLA helpdesk hampir luput
        $tiket = DB::table('tickets')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed', 'resolved'])
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        if ($tiket > 0) {
            $risiko[] = [
                'jenis' => 'sla_breach',
                'label' => 'Tiket SLA hampir luput',
                'jumlah' => $tiket,
                'tahap' => 'tinggi',
            ];
        }

        // Blast bounce tinggi
        $bounce = DB::table('blast_messages')
            ->where('tenant_id', $tenantId)
            ->where('status', 'bounced')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($bounce > 0) {
            $risiko[] = [
                'jenis' => 'blast_bounce_high',
                'label' => 'Bounce blast meningkat (7 hari)',
                'jumlah' => $bounce,
                'tahap' => 'sederhana',
            ];
        }

        // Stok kritikal — jadual item inventori mungkin tidak wujud.
        try {
            $stok = DB::table('inventory_items')
                ->where('tenant_id', $tenantId)
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count();

            if ($stok > 0) {
                $risiko[] = [
                    'jenis' => 'stock_critical',
                    'label' => 'Stok kritikal',
                    'jumlah' => $stok,
                    'tahap' => 'sederhana',
                ];
            }
        } catch (\Throwable) {
            // Jadual inventori tidak wujud dalam pemasangan ini — abaikan
        }

        return $risiko;
    }

    /**
     * Status blast terkini.
     */
    private function blast(int $tenantId): array
    {
        return BlastCampaign::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['running', 'scheduled', 'paused'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'channel', 'status', 'scheduled_at'])
            ->toArray();
    }

    /**
     * Status sistem ringkas.
     */
    private function sistem(): array
    {
        return [
            'ai_gateway_live' => (bool) config('ai_gateway.live_calls_enabled', false),
            'autonomi_aras' => (int) config('executive_ai.default_autonomy_level', 0),
            'masa_briefian' => config('executive_ai.briefing.time', '07:30'),
        ];
    }
}
