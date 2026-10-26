<?php

namespace Tests\Feature;

use App\Modules\FlowAutomation\Models\WorkflowDefinition;
use App\Modules\FlowAutomation\Models\WorkflowRun;
use App\Modules\FlowAutomation\Services\FlowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian enjin automasi (BAB 28).
 *
 * Kritikal: idempotency, kill switch, retry, dead-letter.
 */
class FlowAutomationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected FlowEngine $engine;
    protected int $tenantId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();

        [$user, $tenant] = $this->daftar('flow@ujian.test', 'Syarikat Flow');
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
        $this->dalamTenant($tenant);
        $this->engine = app(FlowEngine::class);
    }

    private function buatWorkflow(array $overrides = []): WorkflowDefinition
    {
        return WorkflowDefinition::create(array_merge([
            'tenant_id' => $this->tenantId,
            'name' => 'Ujian Workflow',
            'trigger_type' => 'manual',
            'trigger_config' => [],
            'conditions' => [],
            'steps' => [
                ['type' => 'send_notification', 'config' => [
                    'channel' => 'in_app',
                    'recipient' => '{{user_id}}',
                    'message' => 'Ujian',
                ]],
            ],
            'is_active' => true,
            'version' => 1,
            'created_by' => $this->userId,
        ], $overrides));
    }

    public function test_workflow_berjaya_dijalankan(): void
    {
        $wf = $this->buatWorkflow();

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);

        $this->assertSame(WorkflowRun::STATUS_COMPLETED, $run->status);
        $this->assertNotNull($run->completed_at);
        $this->assertSame(1, $run->workflow->fresh()->run_count);
    }

    public function test_kill_switch_menghalang_run(): void
    {
        $wf = $this->buatWorkflow();
        $wf->kill_switch = true;
        $wf->save();

        $this->expectException(\RuntimeException::class);
        $this->engine->triggerManual($wf, ['user_id' => $this->userId]);
    }

    public function test_workflow_tidak_aktif_ditolak(): void
    {
        $wf = $this->buatWorkflow(['is_active' => false]);

        $this->expectException(\RuntimeException::class);
        $this->engine->triggerManual($wf, ['user_id' => $this->userId]);
    }

    public function test_idempotency_menghalang_duplicate_run(): void
    {
        $wf = $this->buatWorkflow();
        $key = 'kunci-tetap-123';

        $run1 = $this->engine->triggerManual($wf, ['user_id' => $this->userId], $key);
        $run2 = $this->engine->triggerManual($wf, ['user_id' => $this->userId], $key);

        // Run kedua mesti pulangkan run yang SAMA
        $this->assertSame($run1->id, $run2->id);
        $this->assertSame(1, WorkflowRun::where('workflow_id', $wf->id)->count());
    }

    public function test_kondisi_tidak_sepadan_menghalang_run(): void
    {
        $wf = $this->buatWorkflow([
            'trigger_type' => 'domain_event',
            'trigger_config' => ['event_name' => 'order.paid'],
            'conditions' => [
                ['field' => 'amount', 'operator' => 'greater_than', 'value' => 1000],
            ],
        ]);

        $runs = $this->engine->triggerEvent($this->tenantId, 'order.paid', ['amount' => 500]);

        $this->assertCount(0, $runs);
        $this->assertSame(0, WorkflowRun::where('workflow_id', $wf->id)->count());
    }

    public function test_kondisi_sepadan_membenarkan_run(): void
    {
        $wf = $this->buatWorkflow([
            'trigger_type' => 'domain_event',
            'trigger_config' => ['event_name' => 'order.paid'],
            'conditions' => [
                ['field' => 'amount', 'operator' => 'greater_than', 'value' => 1000],
            ],
        ]);

        $runs = $this->engine->triggerEvent($this->tenantId, 'order.paid', ['amount' => 5000, 'user_id' => $this->userId]);

        $this->assertCount(1, $runs);
        $this->assertSame(WorkflowRun::STATUS_COMPLETED, $runs[0]->status);
    }

    public function test_operator_kondisi_berfungsi(): void
    {
        $wf = $this->buatWorkflow(['conditions' => []]);

        $this->assertTrue($wf->conditionsMatch([]));

        $wf->conditions = [['field' => 'a', 'operator' => 'equals', 'value' => 5]];
        $this->assertTrue($wf->conditionsMatch(['a' => 5]));
        $this->assertFalse($wf->conditionsMatch(['a' => 6]));

        $wf->conditions = [['field' => 'a', 'operator' => 'in', 'value' => [1, 2, 3]]];
        $this->assertTrue($wf->conditionsMatch(['a' => 2]));
        $this->assertFalse($wf->conditionsMatch(['a' => 9]));

        $wf->conditions = [['field' => 'a', 'operator' => 'is_empty']];
        $this->assertTrue($wf->conditionsMatch(['a' => null]));
        $this->assertFalse($wf->conditionsMatch(['a' => 'x']));
    }

    public function test_langkah_perlukan_kelulusan_hentikan_run(): void
    {
        $wf = $this->buatWorkflow([
            'steps' => [
                ['type' => 'financial_action', 'config' => ['amount' => 5000]],
            ],
        ]);

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);

        $this->assertSame(WorkflowRun::STATUS_WAITING_APPROVAL, $run->status);
    }

    public function test_langkah_tidak_sah_gagal(): void
    {
        $wf = $this->buatWorkflow([
            'steps' => [['type' => 'jenis_tidak_wujud', 'config' => []]],
        ]);

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);

        $this->assertSame(WorkflowRun::STATUS_FAILED, $run->status);
        $this->assertStringContainsString('tidak sah', $run->error_message);
        $this->assertSame(1, $wf->fresh()->failure_count);
    }

    public function test_retry_dijadualkan_selepas_gagal(): void
    {
        $wf = $this->buatWorkflow([
            'steps' => [['type' => 'jenis_tidak_wujud', 'config' => []]],
        ]);

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);

        $this->assertSame(1, $run->retry_count);
        $this->assertNotNull($run->next_retry_at);
        $this->assertTrue($run->canRetry());
    }

    public function test_dead_letter_selepas_had_retry(): void
    {
        $wf = $this->buatWorkflow([
            'steps' => [['type' => 'jenis_tidak_wujud', 'config' => []]],
        ]);

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);

        // Paksa retry_count melebihi had
        $run->retry_count = 3;
        $run->status = WorkflowRun::STATUS_FAILED;
        $run->markFailed('Gagal sekali lagi');
        $run->refresh();

        $this->assertSame(WorkflowRun::STATUS_DEAD_LETTER, $run->status);
        $this->assertNull($run->next_retry_at);
        $this->assertFalse($run->canRetry());
    }

    public function test_dry_run_tiada_kesan_sampingan(): void
    {
        $wf = $this->buatWorkflow();

        $result = $this->engine->dryRun($wf, ['user_id' => $this->userId]);

        $this->assertSame(1, $result['total_steps']);
        $this->assertArrayHasKey('steps', $result);
        // Tiada run dicipta
        $this->assertSame(0, WorkflowRun::where('workflow_id', $wf->id)->count());
    }

    public function test_batal_run_aktif(): void
    {
        $wf = $this->buatWorkflow([
            'steps' => [['type' => 'financial_action', 'config' => []]],
        ]);

        $run = $this->engine->triggerManual($wf, ['user_id' => $this->userId]);
        $this->assertSame(WorkflowRun::STATUS_WAITING_APPROVAL, $run->status);

        $run = $this->engine->cancel($run, 'Ujian batal');
        $this->assertSame(WorkflowRun::STATUS_CANCELLED, $run->status);
    }

    public function test_kill_switch_tenant_hentikan_semua(): void
    {
        $wf1 = $this->buatWorkflow();
        $wf2 = $this->buatWorkflow(['name' => 'Kedua']);

        WorkflowDefinition::forTenant($this->tenantId)->update(['kill_switch' => true]);

        $this->assertTrue($wf1->fresh()->kill_switch);
        $this->assertTrue($wf2->fresh()->kill_switch);
        $this->assertFalse($wf1->fresh()->isRunnable());
    }
}
