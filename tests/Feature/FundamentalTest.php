<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Modules\Entitlement\Models\EntitlementGrant;
use App\Modules\Entitlement\Models\PlanLimit;
use App\Modules\Entitlement\Services\ModuleGate;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Modules\Fundamental\Models\AlertRule;
use App\Modules\Fundamental\Models\HealthCheck;
use App\Modules\Fundamental\Models\MetricSnapshot;
use App\Modules\Fundamental\Models\ObservabilityLog;
use App\Modules\Fundamental\Services\FundamentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-Fundamental + B-Entitlement + Security Middleware (BAB 90).
 *
 * Liputan: health check, observability log, metrics, alerts, backup,
 * entitlement gate, rate limit, security headers, idempotency middleware.
 */
class FundamentalTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private FundamentalService $fundamental;
    private EntitlementService $entitlement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@fundamental.my', 'Fundamental Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->fundamental = app(FundamentalService::class);
        $this->entitlement = app(EntitlementService::class);
    }

    // ===================================================================
    // HEALTH
    // ===================================================================

    public function test_run_health_checks(): void
    {
        $hasil = $this->fundamental->runHealthChecks();

        $this->assertArrayHasKey('status', $hasil);
        $this->assertArrayHasKey('components', $hasil);
        $this->assertNotEmpty($hasil['components']);

        // Sekurang-kurangnya database, cache, queue, storage
        $keys = array_column($hasil['components'], 'key');
        $this->assertContains('database', $keys);
        $this->assertContains('cache', $keys);
    }

    public function test_health_check_disimpan(): void
    {
        $this->fundamental->runHealthChecks();

        $this->assertGreaterThan(0, HealthCheck::withoutGlobalScopes()->count());
    }

    public function test_health_ringkas(): void
    {
        $this->fundamental->runHealthChecks();

        $ringkas = $this->fundamental->healthRingkas();

        $this->assertArrayHasKey('status', $ringkas);
        $this->assertNotEmpty($ringkas['components']);
    }

    // ===================================================================
    // OBSERVABILITY
    // ===================================================================

    public function test_log_observability(): void
    {
        $log = $this->fundamental->log(
            'test.event',
            ObservabilityLog::LEVEL_WARNING,
            ObservabilityLog::CHANNEL_APP,
            'Ujian',
            ['nilai' => 42]
        );

        $this->assertSame('test.event', $log->event_name);
        $this->assertSame(ObservabilityLog::LEVEL_WARNING, $log->level);
        $this->assertNotEmpty($log->correlation_id);
        $this->assertSame(['nilai' => 42], $log->context);
    }

    public function test_cari_log_penapis(): void
    {
        $this->fundamental->log('alpha.one', ObservabilityLog::LEVEL_ERROR, ObservabilityLog::CHANNEL_API);
        $this->fundamental->log('beta.two', ObservabilityLog::LEVEL_INFO, ObservabilityLog::CHANNEL_APP);

        $hasil = $this->fundamental->cariLog(['level' => ObservabilityLog::LEVEL_ERROR]);

        $this->assertCount(1, $hasil);
        $this->assertSame('alpha.one', $hasil[0]['event_name']);
    }

    public function test_correlation_id_stabil(): void
    {
        $a = $this->fundamental->correlationId();
        $b = $this->fundamental->correlationId();

        $this->assertSame($a, $b);
    }

    // ===================================================================
    // METRICS
    // ===================================================================

    public function test_rekod_metrik_dan_siri(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->fundamental->rekodMetrik('jualan.harian', (float) ($i * 100), $this->t->id, [
                'name' => 'Jualan Harian',
                'unit' => 'MYR',
            ]);
            // Elak timestamp sama supaya urutan stabil
            usleep(1000);
        }

        $siri = $this->fundamental->siriMetrik('jualan.harian', 10, $this->t->id);

        $this->assertCount(3, $siri);
        $this->assertSame(100.0, $siri[0]['value']);
        $this->assertSame(300.0, $siri[2]['value']);
    }

    // ===================================================================
    // ALERTS
    // ===================================================================

    public function test_cipata_alert_rule(): void
    {
        $alert = $this->fundamental->ciptaAlertRule($this->t->id, [
            'rule_key' => 'jualan_tinggi',
            'name' => 'Jualan tinggi',
            'metric_key' => 'jualan.harian',
            'operator' => AlertRule::OP_GT,
            'threshold' => 1000,
            'severity' => AlertRule::SEVERITY_WARNING,
        ]);

        $this->assertSame('jualan.harian', $alert->metric_key);
        $this->assertTrue($alert->is_enabled);
    }

    public function test_semak_alerts_trigger(): void
    {
        $this->fundamental->ciptaAlertRule($this->t->id, [
            'rule_key' => 'jualan_tinggi',
            'name' => 'Jualan tinggi',
            'metric_key' => 'jualan.harian',
            'operator' => AlertRule::OP_GT,
            'threshold' => 1000,
        ]);

        $this->fundamental->rekodMetrik('jualan.harian', 5000, $this->t->id);

        $ditemui = $this->fundamental->semakAlerts($this->t->id);

        $this->assertCount(1, $ditemui);
        $this->assertSame('jualan_tinggi', $ditemui[0]['rule_key']);
        $this->assertSame(5000.0, $ditemui[0]['value']);
    }

    public function test_semak_alerts_tidak_trigger(): void
    {
        $this->fundamental->ciptaAlertRule($this->t->id, [
            'rule_key' => 'jualan_tinggi',
            'name' => 'Jualan tinggi',
            'metric_key' => 'jualan.harian',
            'operator' => AlertRule::OP_GT,
            'threshold' => 1000,
        ]);

        $this->fundamental->rekodMetrik('jualan.harian', 50, $this->t->id);

        $this->assertCount(0, $this->fundamental->semakAlerts($this->t->id));
    }

    public function test_alert_breach_operator(): void
    {
        $rule = new AlertRule(['operator' => 'gt', 'threshold' => 100]);

        $this->assertTrue($rule->breach(101));
        $this->assertFalse($rule->breach(100));
        $this->assertFalse($rule->breach(99));
    }

    // ===================================================================
    // BACKUP
    // ===================================================================

    public function test_status_backup(): void
    {
        $status = $this->fundamental->statusBackup();

        $this->assertArrayHasKey('rpo_target_minit', $status);
        $this->assertArrayHasKey('rto_target_minit', $status);
        $this->assertArrayHasKey('latihan_pemulihan', $status);
    }

    public function test_rekod_backup_dan_latihan(): void
    {
        $snapshot = $this->fundamental->rekodBackup([
            'filename' => 'backup-2026-10-24.sql.gz',
            'status' => 'completed',
            'size_bytes' => 1024,
            'finished_at' => now(),
        ]);

        $this->assertTrue($snapshot->berjaya());

        $drill = $this->fundamental->rekodLatihan([
            'backup_snapshot_id' => $snapshot->id,
            'status' => 'passed',
            'passed' => true,
            'rpo_actual_minutes' => 30,
            'rto_actual_minutes' => 120,
            'finished_at' => now(),
        ]);

        $this->assertSame('passed', $drill->status);
        $this->assertTrue($drill->passed);
    }

    // ===================================================================
    // ENTITLEMENT
    // ===================================================================

    public function test_jana_plan_lalai_idempoten(): void
    {
        // Plan lalai sudah dijana semasa TenantProvisioner — uji idempotensi
        $pertama = $this->entitlement->janaPlanLalai();
        $kedua = $this->entitlement->janaPlanLalai();

        $this->assertSame(0, $pertama);
        $this->assertSame(0, $kedua);

        // Pastikan 3 plan lalai wujud (starter, growth, enterprise)
        $this->assertSame(1, Plan::query()->where('code', 'starter')->count());
        $this->assertSame(1, Plan::query()->where('code', 'growth')->count());
        $this->assertSame(1, Plan::query()->where('code', 'enterprise')->count());
    }

    public function test_plan_lalai_mempunyai_had(): void
    {
        $this->entitlement->janaPlanLalai();

        $starter = Plan::query()->where('code', 'starter')->first();

        $this->assertNotNull($starter);
        $this->assertSame('88.00', number_format((float) $starter->price_monthly, 2, '.', ''));
        $this->assertGreaterThan(0, PlanLimit::query()->where('plan_id', $starter->id)->count());
        $this->assertNotEmpty((array) ($starter->modules ?? []));
    }

    public function test_tukar_plan_sync_entitlement(): void
    {
        $this->entitlement->janaPlanLalai();

        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'crm'));
        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'seo'));

        // Modul premium tidak termasuk dalam starter
        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));
    }

    public function test_tukar_plan_ke_growth_tambah_modul(): void
    {
        $this->entitlement->janaPlanLalai();

        $starter = Plan::query()->where('code', 'starter')->first();
        $growth = Plan::query()->where('code', 'growth')->first();

        $this->entitlement->tukarPlan($this->t->id, $starter->id);
        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));

        $this->entitlement->tukarPlan($this->t->id, $growth->id);
        $this->entitlement->clearCache();

        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'payroll'));
    }

    public function test_tenant_tanpa_langganan_ditolak(): void
    {
        // Daftar tenant baharu — ia dapat plan percubaan (semua modul)
        [, $t3] = $this->daftar('baru@entitlement.my', 'Tenant Baru Sdn Bhd');
        $this->dalamTenant($t3);
        $this->entitlement->clearCache();

        // Plan percubaan = akses semua modul semasa tempoh percubaan
        $this->assertTrue($this->entitlement->bolehModul($t3->id, 'crm'));

        // Buang langganan → deny-by-default
        \App\Models\Subscription::withoutGlobalScopes()
            ->where('tenant_id', $t3->id)
            ->delete();

        $this->entitlement->clearCache();

        $this->assertFalse($this->entitlement->bolehModul($t3->id, 'crm'));
    }

    public function test_langganan_tidak_aktif_tolak_semua_modul(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();

        $langganan = $this->entitlement->tukarPlan($this->t->id, $plan->id);
        $langganan->update(['status' => 'suspended']);

        $this->entitlement->clearCache();

        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'crm'));
    }

    public function test_grant_dan_revoke_modul(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));

        $this->entitlement->grantModul($this->t->id, 'payroll', 'addon');
        $this->entitlement->clearCache();
        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'payroll'));

        $this->entitlement->revokeModul($this->t->id, 'payroll');
        $this->entitlement->clearCache();
        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));
    }

    public function test_entitlement_kedaluwarsa(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->entitlement->grantModul(
            $this->t->id,
            'trial_modul',
            'trial',
            now()->subDay()
        );
        $this->entitlement->clearCache();

        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'trial_modul'));
    }

    // ===================================================================
    // HAD PENGGUNAAN
    // ===================================================================

    public function test_had_dari_plan(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->assertSame(5, $this->entitlement->had($this->t->id, 'seats'));
        $this->assertSame(50000, $this->entitlement->had($this->t->id, 'ai_tokens'));
    }

    public function test_boleh_guna_had(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->assertTrue($this->entitlement->bolehGuna($this->t->id, 'seats', 5));
        $this->assertFalse($this->entitlement->bolehGuna($this->t->id, 'seats', 6));
    }

    public function test_reserve_dan_settle_usage(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $berjaya = $this->entitlement->reserveUsage($this->t->id, 'ai_tokens', 1000);
        $this->assertTrue($berjaya);

        $kaunter = $this->entitlement->kaunter($this->t->id, 'ai_tokens');
        $this->assertSame(1000, $kaunter->reserved_value);
        $this->assertSame(0, $kaunter->used_value);

        $this->entitlement->settleUsage($this->t->id, 'ai_tokens', 1000);

        $kaunter = $this->entitlement->kaunter($this->t->id, 'ai_tokens');
        $this->assertSame(0, $kaunter->reserved_value);
        $this->assertSame(1000, $kaunter->used_value);
    }

    public function test_reserve_melebihi_had_gagal(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        // Had starter = 50000 token; cuba reserved 60000 → mesti gagal
        $this->assertSame(50000, $this->entitlement->had($this->t->id, 'ai_tokens'));
        $this->assertFalse($this->entitlement->reserveUsage($this->t->id, 'ai_tokens', 60000));
        $this->assertTrue($this->entitlement->reserveUsage($this->t->id, 'ai_tokens', 50000));
    }

    public function test_release_usage(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->entitlement->reserveUsage($this->t->id, 'ai_tokens', 1000);
        $this->entitlement->releaseUsage($this->t->id, 'ai_tokens', 1000);

        $kaunter = $this->entitlement->kaunter($this->t->id, 'ai_tokens');
        $this->assertSame(0, $kaunter->reserved_value);
    }

    public function test_semak_semua_had(): void
    {
        $this->entitlement->janaPlanLalai();
        $plan = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $plan->id);

        $this->entitlement->tambahUsage($this->t->id, 'seats', 3);

        $had = $this->entitlement->semakSemuaHad($this->t->id);
        $seats = collect($had)->firstWhere('limit_key', 'seats');

        $this->assertNotNull($seats);
        $this->assertSame(3, $seats['used']);
        $this->assertSame(2, $seats['remaining']);
        $this->assertSame(60.0, $seats['percent']);
    }

    // ===================================================================
    // SECURITY MIDDLEWARE
    // ===================================================================

    public function test_security_headers_ditetapkan(): void
    {
        $this->sebagai($this->u, $this->t);

        $response = $this->getJson('/api/v1/entitlement/summary');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_rate_limit_header_disediakan(): void
    {
        $this->sebagai($this->u, $this->t);

        $response = $this->getJson('/api/v1/entitlement/summary');

        $response->assertOk()->assertHeader('X-RateLimit-Limit');
    }

    public function test_idempotency_middleware_replay(): void
    {
        $this->sebagai($this->u, $this->t);

        $kunci = 'test-key-001';

        $pertama = $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'seo',
            'source' => 'addon',
        ], ['Idempotency-Key' => $kunci]);

        $pertama->assertCreated();

        $kedua = $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'seo',
            'source' => 'addon',
        ], ['Idempotency-Key' => $kunci]);

        $kedua->assertStatus(201)->assertHeader('Idempotent-Replay', 'true');

        // Hanya 1 rekod dicipta
        $this->assertSame(1, EntitlementGrant::where('tenant_id', $this->t->id)
            ->where('module', 'seo')->count());
    }

    public function test_idempotency_body_berbeza_422(): void
    {
        $this->sebagai($this->u, $this->t);

        $kunci = 'test-key-002';

        $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'seo',
        ], ['Idempotency-Key' => $kunci])->assertCreated();

        $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'crm',
        ], ['Idempotency-Key' => $kunci])->assertStatus(422);
    }

    public function test_request_tanpa_idempotency_key_berlaku(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'seo',
        ])->assertCreated();

        $this->postJson('/api/v1/entitlement/grant-module', [
            'module' => 'seo',
        ])->assertCreated();
    }

    // ===================================================================
    // API
    // ===================================================================

    public function test_api_health_live(): void
    {
        $this->getJson('/api/v1/fundamental/health/live')
            ->assertOk()
            ->assertJsonPath('status', 'alive');
    }

    public function test_api_health_ready(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/fundamental/health/ready')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['status', 'components']]);
    }

    public function test_api_entitlement_summary(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/entitlement/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['langganan', 'jumlah_modul_aktif', 'had']]);
    }

    public function test_api_plans(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/entitlement/plans')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_metrics(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/fundamental/metrics')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_alerts(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/fundamental/alerts')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_backup_status(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/fundamental/backup')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ===================================================================
    // MODULE GATE MIDDLEWARE
    // ===================================================================

    public function test_module_middleware_tolak_modul_tiada_entitlement(): void
    {
        $this->sebagai($this->u, $this->t);

        // Tiada langganan → modul ditolak
        $this->getJson('/api/v1/entitlement/summary')
            ->assertOk();

        // Pulihkan konteks tenant selepas request HTTP
        $this->dalamTenant($this->t);

        // Plan belum dipilih → modul bukan dalam plan
        $this->entitlement->janaPlanLalai();
        $starter = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $starter->id);
        $this->entitlement->clearCache();

        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'crm'));
        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));
    }

    // ===================================================================
    // MODULE GATE — PENGAKTIAN AUTOMATIK
    // ===================================================================

    public function test_modul_dalam_plan_dibenarkan(): void
    {
        $this->dalamTenant($this->t);

        // Tukar ke plan starter (terhad) — 'seo' termasuk
        $this->entitlement->janaPlanLalai();
        $starter = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $starter->id);
        $this->entitlement->clearCache();

        $this->assertContains('seo', (array) $starter->modules);
        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'seo'));

        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/seo/profile')->assertOk();
    }

    public function test_modul_bukan_dalam_plan_ditolak_403(): void
    {
        $this->dalamTenant($this->t);

        // Plan starter TIDAK termasuk 'payroll'
        $this->entitlement->janaPlanLalai();
        $starter = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $starter->id);
        $this->entitlement->clearCache();

        $this->assertFalse($this->entitlement->bolehModul($this->t->id, 'payroll'));

        $this->sebagai($this->u, $this->t);

        $respon = $this->getJson('/api/v1/payroll/employees');

        $respon->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('meta.module', 'payroll');
    }

    public function test_modul_boleh_dibenarkan_via_grant(): void
    {
        $this->dalamTenant($this->t);

        $this->entitlement->janaPlanLalai();
        $starter = Plan::query()->where('code', 'starter')->first();
        $this->entitlement->tukarPlan($this->t->id, $starter->id);

        $this->entitlement->grantModul($this->t->id, 'payroll', 'addon');
        $this->entitlement->clearCache();

        $this->assertTrue($this->entitlement->bolehModul($this->t->id, 'payroll'));

        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/payroll/employees')->assertOk();
    }

    public function test_module_gate_mapping_alias(): void
    {
        // 'content' → 'content_studio', 'workflow' → 'workflow_studio'
        $this->assertSame('content_studio', ModuleGate::moduleKey('content'));
        $this->assertSame('workflow_studio', ModuleGate::moduleKey('workflow'));
        $this->assertSame('orders', ModuleGate::moduleKey('orders'));

        // Namespace teras bukan modul
        $this->assertFalse(ModuleGate::ialahModul('members'));
        $this->assertFalse(ModuleGate::ialahModul('audit'));
        $this->assertTrue(ModuleGate::ialahModul('orders'));
    }
}
