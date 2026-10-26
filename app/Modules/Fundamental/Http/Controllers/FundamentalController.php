<?php

namespace App\Modules\Fundamental\Http\Controllers;

use App\Modules\Fundamental\Models\AlertRule;
use App\Modules\Fundamental\Models\MetricSnapshot;
use App\Modules\Fundamental\Services\FundamentalService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API B-Fundamental — health, observability, metrics, alerts, backup (BAB 90).
 *
 * ⚠️ Laluan statik DIDAHULUKAN sebelum `/{id}`.
 */
class FundamentalController extends Controller
{
    public function __construct(private FundamentalService $servis)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // STATIK
    // ---------------------------------------------------------------

    /**
     * Health live — proses hidup. Tidak probe apa-apa (untuk load balancer).
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Health ready — sedia menerima trafik. Probe komponen kritikal.
     */
    public function ready(): JsonResponse
    {
        $hasil = $this->servis->runHealthChecks();

        $kod = $hasil['status'] === 'healthy' ? 200 : 503;

        return response()->json([
            'success' => $hasil['status'] === 'healthy',
            'data' => $hasil,
        ], $kod);
    }

    /**
     * Health deep — probe penuh + status backup + alerts.
     */
    public function deep(): JsonResponse
    {
        $health = $this->servis->runHealthChecks();
        $backup = $this->servis->statusBackup();
        $alerts = $this->servis->semakAlerts();

        return response()->json([
            'success' => true,
            'data' => [
                'health' => $health,
                'backup' => $backup,
                'alerts' => $alerts,
                'alert_count' => count($alerts),
            ],
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        $had = min((int) $request->query('limit', 50), 200);

        $query = MetricSnapshot::withoutGlobalScopes()
            ->orderByDesc('recorded_at')
            ->limit($had);

        if ($request->query('metric_key')) {
            $query->where('metric_key', $request->query('metric_key'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()->map(fn ($m) => [
                'metric_key' => $m->metric_key,
                'name' => $m->name,
                'type' => $m->type,
                'value' => (float) $m->value,
                'unit' => $m->unit,
                'labels' => $m->labels,
                'recorded_at' => $m->recorded_at?->toIso8601String(),
            ])->all(),
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->cariLog([
                'level' => $request->query('level'),
                'channel' => $request->query('channel'),
                'event_name' => $request->query('event_name'),
                'correlation_id' => $request->query('correlation_id'),
            ], (int) $request->query('limit', 100)),
        ]);
    }

    public function backupStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->statusBackup(),
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        if ($request->boolean('evaluate')) {
            $ditemui = $this->servis->semakAlerts();

            return response()->json([
                'success' => true,
                'count' => count($ditemui),
                'data' => $ditemui,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => AlertRule::withoutGlobalScopes()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get(),
        ]);
    }

    // ---------------------------------------------------------------
    // MUTASI
    // ---------------------------------------------------------------

    public function storeAlertRule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rule_key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:150'],
            'metric_key' => ['required', 'string', 'max:100'],
            'operator' => ['nullable', 'in:gt,gte,lt,lte,eq'],
            'threshold' => ['required', 'numeric'],
            'window_minutes' => ['nullable', 'integer', 'min:1'],
            'severity' => ['nullable', 'in:info,warning,critical'],
            'channel' => ['nullable', 'string', 'max:40'],
            'message_template' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $rule = $this->servis->ciptaAlertRule($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    public function storeMetric(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metric_key' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric'],
            'name' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'in:gauge,counter,histogram'],
            'unit' => ['nullable', 'string', 'max:20'],
            'labels' => ['nullable', 'string', 'max:200'],
        ]);

        $snapshot = $this->servis->rekodMetrik(
            $data['metric_key'],
            (float) $data['value'],
            $this->tenantId(),
            $data
        );

        return response()->json(['success' => true, 'data' => $snapshot], 201);
    }

    public function metricSeries(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metric_key' => ['required', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->servis->siriMetrik(
                $data['metric_key'],
                (int) ($data['limit'] ?? 30),
                $this->tenantId()
            ),
        ]);
    }

    public function storeRestoreDrill(Request $request): JsonResponse
    {
        $data = $request->validate([
            'backup_snapshot_id' => ['nullable', 'integer'],
            'drill_type' => ['nullable', 'in:full_restore,tenant_restore,point_in_time'],
            'status' => ['nullable', 'in:planned,running,passed,failed'],
            'rpo_actual_minutes' => ['nullable', 'integer', 'min:0'],
            'rto_actual_minutes' => ['nullable', 'integer', 'min:0'],
            'passed' => ['nullable', 'boolean'],
            'findings' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
        ]);

        $drill = $this->servis->rekodLatihan($data);

        return response()->json(['success' => true, 'data' => $drill], 201);
    }
}
