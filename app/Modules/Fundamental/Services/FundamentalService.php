<?php

namespace App\Modules\Fundamental\Services;

use App\Modules\Fundamental\Models\AlertRule;
use App\Modules\Fundamental\Models\BackupSnapshot;
use App\Modules\Fundamental\Models\HealthCheck;
use App\Modules\Fundamental\Models\MetricSnapshot;
use App\Modules\Fundamental\Models\ObservabilityLog;
use App\Modules\Fundamental\Models\RestoreDrill;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FundamentalService — health, observability, metrics, alerts, backup (BAB 90).
 *
 * NOTA: Health check platform-level (tenant_id null) tidak perlu tenant.
 */
class FundamentalService
{
    // ===============================================================
    // HEALTH
    // ===============================================================

    /**
     * Jalankan semua health check dan simpan status.
     *
     * @return array{status: string, components: array, checked_at: string}
     */
    public function runHealthChecks(?int $tenantId = null): array
    {
        $components = [];

        foreach ($this->checkDefinitions() as $key => $definisi) {
            $mula = microtime(true);

            try {
                $hasil = $definisi['probe']();
                $status = $hasil['status'] ?? HealthCheck::STATUS_HEALTHY;
                $message = $hasil['message'] ?? null;
                $context = $hasil['context'] ?? null;
            } catch (\Throwable $e) {
                $status = HealthCheck::STATUS_UNHEALTHY;
                $message = mb_substr($e->getMessage(), 0, 500);
                $context = null;
            }

            $durasi = (int) round((microtime(true) - $mula) * 1000);

            HealthCheck::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenantId, 'check_key' => $key],
                [
                    'name' => $definisi['name'],
                    'component' => $definisi['component'],
                    'status' => $status,
                    'duration_ms' => $durasi,
                    'message' => $message,
                    'context' => $context,
                    'checked_at' => now(),
                ]
            );

            $components[] = [
                'key' => $key,
                'name' => $definisi['name'],
                'component' => $definisi['component'],
                'status' => $status,
                'duration_ms' => $durasi,
                'message' => $message,
            ];
        }

        $overall = $this->tentukanStatusKeseluruhan($components);

        return [
            'status' => $overall,
            'components' => $components,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Definisi check — setiap satu mengembalikan probe callable.
     */
    private function checkDefinitions(): array
    {
        return [
            'database' => [
                'name' => 'Pangkalan Data',
                'component' => 'database',
                'probe' => function () {
                    $mula = microtime(true);
                    DB::connection()->getPdo()->query('SELECT 1');

                    return [
                        'status' => HealthCheck::STATUS_HEALTHY,
                        'context' => ['driver' => DB::connection()->getDriverName()],
                        'message' => null,
                    ];
                },
            ],
            'cache' => [
                'name' => 'Cache',
                'component' => 'cache',
                'probe' => function () {
                    $kunci = 'health:'.Str::random(8);
                    Cache::put($kunci, 'ok', 10);
                    $nilai = Cache::get($kunci);
                    Cache::forget($kunci);

                    if ($nilai !== 'ok') {
                        return ['status' => HealthCheck::STATUS_DEGRADED, 'message' => 'Cache tidak menyimpan nilai'];
                    }

                    return ['status' => HealthCheck::STATUS_HEALTHY];
                },
            ],
            'queue' => [
                'name' => 'Antrean',
                'component' => 'queue',
                'probe' => function () {
                    $gagal = DB::table('failed_jobs')->count();

                    return [
                        'status' => $gagal > 0 ? HealthCheck::STATUS_DEGRADED : HealthCheck::STATUS_HEALTHY,
                        'context' => ['failed_jobs' => $gagal],
                        'message' => $gagal > 0 ? "{$gagal} job gagal" : null,
                    ];
                },
            ],
            'storage' => [
                'name' => 'Storan',
                'component' => 'storage',
                'probe' => function () {
                    $disks = config('filesystems.disks', []);

                    foreach (['local', 'public'] as $nama) {
                        if (isset($disks[$nama])) {
                            Storage::disk($nama)->files('health');
                        }
                    }

                    return ['status' => HealthCheck::STATUS_HEALTHY];
                },
            ],
        ];
    }

    /**
     * Status Keseluruhan — worst-of.
     */
    private function tentukanStatusKeseluruhan(array $components): string
    {
        $statuses = array_column($components, 'status');

        if (in_array(HealthCheck::STATUS_UNHEALTHY, $statuses, true)) {
            return HealthCheck::STATUS_UNHEALTHY;
        }

        if (in_array(HealthCheck::STATUS_DEGRADED, $statuses, true)) {
            return HealthCheck::STATUS_DEGRADED;
        }

        return HealthCheck::STATUS_HEALTHY;
    }

    /**
     * Health ringkas untuk status sahaja (bukan full probe).
     */
    public function healthRingkas(): array
    {
        $checks = HealthCheck::withoutGlobalScopes()
            ->orderByDesc('checked_at')
            ->get()
            ->groupBy('check_key')
            ->map(fn ($g) => $g->first());

        $components = $checks->map(fn ($c) => [
            'key' => $c->check_key,
            'name' => $c->name,
            'component' => $c->component,
            'status' => $c->status,
            'duration_ms' => $c->duration_ms,
            'message' => $c->message,
            'checked_at' => $c->checked_at?->toIso8601String(),
        ])->values()->all();

        return [
            'status' => $this->tentukanStatusKeseluruhan($components),
            'components' => $components,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    // ===============================================================
    // OBSERVABILITY
    // ===============================================================

    /**
     * Log satu peristiwa observability.
     */
    public function log(
        string $eventName,
        string $level = ObservabilityLog::LEVEL_INFO,
        string $channel = ObservabilityLog::CHANNEL_APP,
        ?string $message = null,
        array $context = [],
        ?int $durationMs = null
    ): ObservabilityLog {
        $tenantId = app(TenantContext::class)->id();

        return ObservabilityLog::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'level' => $level,
            'channel' => $channel,
            'event_name' => $eventName,
            'correlation_id' => $this->correlationId(),
            'request_id' => request()?->header('X-Request-Id'),
            'actor_user_id' => auth()->id(),
            'message' => $message,
            'context' => $context,
            'duration_ms' => $durationMs,
            'service' => config('app.name'),
            'host' => gethostname(),
        ]);
    }

    /**
     * Correlation ID — stable dalam satu request.
     */
    public function correlationId(): string
    {
        static $id = null;

        if ($id === null) {
            $id = (string) Str::uuid();
        }

        return $id;
    }

    /**
     * Cari log dengan penapis.
     */
    public function cariLog(array $penapis = [], int $had = 100): array
    {
        $query = ObservabilityLog::withoutGlobalScopes();

        if (! empty($penapis['level'])) {
            $query->where('level', $penapis['level']);
        }

        if (! empty($penapis['channel'])) {
            $query->where('channel', $penapis['channel']);
        }

        if (! empty($penapis['event_name'])) {
            $query->where('event_name', 'like', $penapis['event_name'].'%');
        }

        if (! empty($penapis['correlation_id'])) {
            $query->where('correlation_id', $penapis['correlation_id']);
        }

        $tenantId = app(TenantContext::class)->id();

        if ($tenantId) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query->orderByDesc('id')->limit(min($had, 500))->get()->map(fn ($l) => [
            'id' => $l->id,
            'level' => $l->level,
            'channel' => $l->channel,
            'event_name' => $l->event_name,
            'correlation_id' => $l->correlation_id,
            'message' => $l->message,
            'context' => $l->context,
            'duration_ms' => $l->duration_ms,
            'created_at' => $l->created_at?->toIso8601String(),
        ])->all();
    }

    // ===============================================================
    // METRICS
    // ===============================================================

    /**
     * Rekod nilai metrik.
     */
    public function rekodMetrik(string $metricKey, float $value, ?int $tenantId = null, array $meta = []): MetricSnapshot
    {
        return MetricSnapshot::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId ?? app(TenantContext::class)->id(),
            'metric_key' => $metricKey,
            'name' => $meta['name'] ?? $metricKey,
            'type' => $meta['type'] ?? MetricSnapshot::TYPE_GAUGE,
            'labels' => $meta['labels'] ?? null,
            'value' => $value,
            'unit' => $meta['unit'] ?? null,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Ambil siri metrik untuk grafik.
     */
    public function siriMetrik(string $metricKey, int $had = 30, ?int $tenantId = null): array
    {
        return MetricSnapshot::siri($tenantId ?? app(TenantContext::class)->id(), $metricKey, $had);
    }

    // ===============================================================
    // ALERTS
    // ===============================================================

    /**
     * Nilai semua peraturan alert terhadap metrik terkini.
     */
    public function semakAlerts(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? app(TenantContext::class)->id();

        $rules = AlertRule::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->get();

        $ditemui = [];

        foreach ($rules as $rule) {
            $snapshot = MetricSnapshot::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('metric_key', $rule->metric_key)
                ->orderByDesc('recorded_at')
                ->first();

            if (! $snapshot) {
                continue;
            }

            $nilai = (float) $snapshot->value;

            if ($rule->breach($nilai)) {
                $ditemui[] = [
                    'rule_id' => $rule->id,
                    'rule_key' => $rule->rule_key,
                    'name' => $rule->name,
                    'severity' => $rule->severity,
                    'metric_key' => $rule->metric_key,
                    'value' => $nilai,
                    'threshold' => (float) $rule->threshold,
                    'operator' => $rule->operator,
                    'message' => $rule->message_template
                        ? str_replace('{value}', (string) $nilai, $rule->message_template)
                        : "{$rule->name}: {$nilai} melebihi ambang {$rule->threshold}",
                ];

                $rule->update([
                    'last_triggered_at' => now(),
                    'trigger_count' => (int) $rule->trigger_count + 1,
                ]);
            }
        }

        return $ditemui;
    }

    public function ciptaAlertRule(?int $tenantId, array $data): AlertRule
    {
        return AlertRule::withoutGlobalScopes()->create(array_merge([
            'tenant_id' => $tenantId ?? app(TenantContext::class)->id(),
            'operator' => AlertRule::OP_GT,
            'window_minutes' => 5,
            'severity' => AlertRule::SEVERITY_WARNING,
            'channel' => 'in_app',
            'is_enabled' => true,
            'trigger_count' => 0,
        ], $data));
    }

    // ===============================================================
    // BACKUP
    // ===============================================================

    /**
     * Rekod backup snapshot (dipanggil selepas proses backup).
     */
    public function rekodBackup(array $data): BackupSnapshot
    {
        return BackupSnapshot::withoutGlobalScopes()->create(array_merge([
            'snapshot_id' => (string) Str::uuid(),
            'disk' => 'local',
            'type' => BackupSnapshot::TYPE_FULL,
            'size_bytes' => 0,
            'is_encrypted' => true,
            'status' => BackupSnapshot::STATUS_PENDING,
        ], $data));
    }

    /**
     * Rekod latihan pemulihan.
     */
    public function rekodLatihan(array $data): RestoreDrill
    {
        return RestoreDrill::withoutGlobalScopes()->create(array_merge([
            'tenant_id' => app(TenantContext::class)->id(),
            'drill_type' => RestoreDrill::TYPE_FULL,
            'status' => RestoreDrill::STATUS_PLANNED,
            'rpo_target_minutes' => (int) config('security.backup.rpo_minutes', 60),
            'rto_target_minutes' => (int) config('security.backup.rto_minutes', 240),
        ], $data));
    }

    /**
     * Status backup + keberangkaran RPO/RTO.
     */
    public function statusBackup(): array
    {
        $snapshots = BackupSnapshot::withoutGlobalScopes()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $terakhir = $snapshots->first();

        $rpo = config('security.backup.rpo_minutes', 60);
        $rto = config('security.backup.rto_minutes', 240);

        $umurMinit = $terakhir?->finished_at
            ? (int) $terakhir->finished_at->diffInMinutes(now())
            : null;

        $drills = RestoreDrill::withoutGlobalScopes()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'enabled' => (bool) config('security.backup.enabled', false),
            'jumlah_backup' => BackupSnapshot::withoutGlobalScopes()->count(),
            'backup_terakhir' => $terakhir ? [
                'snapshot_id' => $terakhir->snapshot_id,
                'filename' => $terakhir->filename,
                'status' => $terakhir->status,
                'size_bytes' => (int) $terakhir->size_bytes,
                'finished_at' => $terakhir->finished_at?->toIso8601String(),
                'umur_minit' => $umurMinit,
            ] : null,
            'rpo_target_minit' => $rpo,
            'rto_target_minit' => $rto,
            'rpo_status' => $umurMinit === null ? 'unknown' : ($umurMinit <= $rpo ? 'ok' : 'breach'),
            'latihan_pemulihan' => $drills->map(fn ($d) => [
                'id' => $d->id,
                'type' => $d->drill_type,
                'status' => $d->status,
                'passed' => $d->passed,
                'rpo_actual_minutes' => $d->rpo_actual_minutes,
                'rto_actual_minutes' => $d->rto_actual_minutes,
                'finished_at' => $d->finished_at?->toIso8601String(),
            ])->all(),
        ];
    }
}
