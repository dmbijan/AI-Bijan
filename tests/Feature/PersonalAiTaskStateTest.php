<?php

namespace Tests\Feature;

use App\Modules\PersonalAssistant\Models\PersonalTask;
use App\Modules\PersonalAssistant\Services\TaskExecutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian state machine tugas personal AI (BAB 17.5).
 *
 * Peralihan tidak sah MESTI ditolak.
 */
class PersonalAiTaskStateTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected TaskExecutor $executor;
    protected int $tenantId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();

        [$user, $tenant] = $this->daftar('state@ujian.test', 'Syarikat State');
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
        $this->dalamTenant($tenant);
        $this->executor = app(TaskExecutor::class);
    }

    private function buatTugas(array $overrides = []): PersonalTask
    {
        return $this->executor->createTask($this->tenantId, $this->userId, array_merge([
            'objective' => 'Uji state machine',
            'assistant_type' => 'owner',
            'risk_level' => 'low',
        ], $overrides));
    }

    public function test_aliran_penuh_berjaya(): void
    {
        $task = $this->buatTugas();
        $this->assertSame(PersonalTask::STATUS_DRAFT, $task->status);

        $task = $this->executor->plan($task);
        $this->assertSame(PersonalTask::STATUS_PLANNED, $task->status);

        $task = $this->executor->queue($task);
        $this->assertSame(PersonalTask::STATUS_QUEUED, $task->status);

        $task = $this->executor->start($task);
        $this->assertSame(PersonalTask::STATUS_RUNNING, $task->status);

        $task = $this->executor->complete($task, ['reference' => 'HASIL-001']);
        $this->assertSame(PersonalTask::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_tidak_boleh_skip_draft_ke_running(): void
    {
        $task = $this->buatTugas();

        $this->expectException(\RuntimeException::class);
        $task->status = PersonalTask::STATUS_RUNNING;
        // Guna transition melalui executor — mesti tolak
        $reflection = new \ReflectionMethod(TaskExecutor::class, 'transition');
        $reflection->setAccessible(true);
        $reflection->invoke($this->executor, $task, PersonalTask::STATUS_RUNNING);
    }

    public function test_tidak_boleh_complete_tanpa_running(): void
    {
        $task = $this->buatTugas();
        $this->executor->plan($task);

        $this->expectException(\RuntimeException::class);
        $this->executor->complete($task, []);
    }

    public function test_tugas_berisiko_tinggi_perlukan_kelulusan(): void
    {
        $task = $this->buatTugas(['risk_level' => 'high']);

        $this->assertTrue($task->requiresApproval());

        // Queue tanpa kelulusan mesti gagal
        $this->executor->plan($task);
        $this->expectException(\RuntimeException::class);
        $this->executor->queue($task);
    }

    public function test_tugas_berisiko_tinggi_boleh_queue_selepas_kelulusan(): void
    {
        $task = $this->buatTugas(['risk_level' => 'critical']);

        $this->executor->plan($task);
        $this->executor->requestApproval($task, 1);
        $this->assertSame(PersonalTask::STATUS_AWAITING_APPROVAL, $task->status);

        $task = $this->executor->queue($task);
        $this->assertSame(PersonalTask::STATUS_QUEUED, $task->status);
    }

    public function test_tugas_risiko_rendah_tidak_perlukan_kelulusan(): void
    {
        $task = $this->buatTugas(['risk_level' => 'low']);

        $this->assertFalse($task->requiresApproval());
    }

    public function test_batal_dari_mana_mana_status_aktif(): void
    {
        $task = $this->buatTugas();
        $this->executor->plan($task);

        $task = $this->executor->cancel($task, 'Ujian batal');
        $this->assertSame(PersonalTask::STATUS_CANCELLED, $task->status);
        $this->assertStringContainsString('Ujian batal', $task->error_details);
    }

    public function test_failed_merekod_ralat(): void
    {
        $task = $this->buatTugas();
        $this->executor->plan($task);
        $this->executor->queue($task);
        $this->executor->start($task);

        $task = $this->executor->complete($task, ['error' => 'Gateway timeout'], PersonalTask::STATUS_FAILED);

        $this->assertSame(PersonalTask::STATUS_FAILED, $task->status);
        $this->assertSame('Gateway timeout', $task->error_details);
    }

    public function test_status_terminal_tidak_boleh_berubah(): void
    {
        $task = $this->buatTugas();
        $this->executor->plan($task);
        $this->executor->queue($task);
        $this->executor->start($task);
        $this->executor->complete($task, []);

        $this->assertTrue($task->fresh()->isTerminal());
        $this->assertFalse($task->fresh()->canTransitionTo(PersonalTask::STATUS_RUNNING));
    }

    public function test_statistik_tugas(): void
    {
        $this->buatTugas();
        $this->buatTugas(['assistant_type' => 'finance']);

        $stats = $this->executor->getStats($this->tenantId, $this->userId);

        $this->assertSame(2, $stats['total']);
        $this->assertSame(0, $stats['active']);
    }
}
