<?php

namespace Tests\Feature;

use App\Modules\Boardroom\Models\BoardroomSession;
use App\Modules\FlowAutomation\Models\WorkflowDefinition;
use App\Modules\FlowAutomation\Models\WorkflowRun;
use App\Modules\PersonalAssistant\Models\PersonalTask;
use App\Modules\AIGateway\Models\AiCreditWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian pengasingan tenant untuk modul AI (BAB 3.4, BAB 186.4).
 *
 * Pemilik Tenant A TIDAK BOLEH membaca atau mengubah data Tenant B
 * melalui mana-mana endpoint AI.
 */
class ModulAiTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    public function test_boardroom_sesi_tenant_lain_tidak_boleh_dilihat(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a@ujian.test', 'Syarikat A');
        [$ownerB, $tenantB] = $this->daftar('b@ujian.test', 'Syarikat B');

        // Tenant B cipta sesi boardroom
        $this->sebagai($ownerB, $tenantB);
        $sessionId = $this->postJson('/api/v1/boardroom/sessions', [
            'question' => 'Rahsia perniagaan Tenant B?',
            'category' => 'strategy',
        ])->assertCreated()->json('data.id');

        // Tenant A cuba akses
        $this->sebagai($ownerA, $tenantA);
        $this->getJson("/api/v1/boardroom/sessions/{$sessionId}")
            ->assertNotFound();

        $this->getJson('/api/v1/boardroom/sessions')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');
    }

    public function test_boardroom_keputusan_tenant_lain_ditolak(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a2@ujian.test', 'Syarikat A2');
        [$ownerB, $tenantB] = $this->daftar('b2@ujian.test', 'Syarikat B2');

        $this->sebagai($ownerB, $tenantB);
        $sessionId = $this->postJson('/api/v1/boardroom/sessions', [
            'question' => 'Adakah kita patut beli lori baru?',
            'category' => 'financial',
        ])->assertCreated()->json('data.id');

        $this->sebagai($ownerA, $tenantA);
        $this->postJson("/api/v1/boardroom/sessions/{$sessionId}/decide", [
            'decision' => 'approve',
        ])->assertNotFound();

        // Pastikan status tidak berubah (skop tenant dilangkau untuk semakan langsung)
        $this->assertNull(BoardroomSession::withoutGlobalScope('tenant')->find($sessionId)->decision);
    }

    public function test_task_personal_ai_hanya_nampak_milik_sendiri(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a3@ujian.test', 'Syarikat A3');
        [$ownerB, $tenantB] = $this->daftar('b3@ujian.test', 'Syarikat B3');

        $this->sebagai($ownerB, $tenantB);
        $this->postJson('/api/v1/personal-ai/tasks', [
            'objective' => 'Rahsia Tenant B',
        ])->assertCreated();

        $this->sebagai($ownerA, $tenantA);
        $this->getJson('/api/v1/personal-ai/tasks')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');
    }

    public function test_memori_personal_ai_terasing(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a4@ujian.test', 'Syarikat A4');
        [$ownerB, $tenantB] = $this->daftar('b4@ujian.test', 'Syarikat B4');

        $this->sebagai($ownerB, $tenantB);
        $this->postJson('/api/v1/personal-ai/memory', [
            'memory_type' => 'preference',
            'category' => 'laporan',
            'content_text' => 'Rahsia Tenant B',
        ])->assertCreated();

        $this->sebagai($ownerA, $tenantA);
        $this->getJson('/api/v1/personal-ai/memory')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_workflow_terasing_antara_tenant(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a5@ujian.test', 'Syarikat A5');
        [$ownerB, $tenantB] = $this->daftar('b5@ujian.test', 'Syarikat B5');

        $this->sebagai($ownerB, $tenantB);
        $workflowId = $this->postJson('/api/v1/flows/workflows', [
            'name' => 'Workflow Rahsia B',
            'trigger_type' => 'manual',
            'steps' => [['type' => 'send_notification', 'config' => ['recipient' => 'x', 'message' => 'y']]],
        ])->assertCreated()->json('data.id');

        $this->sebagai($ownerA, $tenantA);
        $this->getJson("/api/v1/flows/workflows/{$workflowId}")->assertNotFound();
        $this->postJson("/api/v1/flows/workflows/{$workflowId}/trigger")->assertNotFound();
        $this->postJson("/api/v1/flows/workflows/{$workflowId}/activate")->assertNotFound();

        $this->getJson('/api/v1/flows/workflows')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');
    }

    public function test_wallet_kredit_terasing(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a6@ujian.test', 'Syarikat A6');
        [$ownerB, $tenantB] = $this->daftar('b6@ujian.test', 'Syarikat B6');

        // Tenant B top-up
        $this->sebagai($ownerB, $tenantB);
        $this->postJson('/api/v1/ai-gateway/wallet/top-up', ['amount' => 500])
            ->assertOk();

        // Tenant A tidak nampak
        $this->sebagai($ownerA, $tenantA);
        $this->getJson('/api/v1/ai-gateway/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 0);
    }

    public function test_usage_event_terasing(): void
    {
        $this->setUpAkses();
        [$ownerA, $tenantA] = $this->daftar('a7@ujian.test', 'Syarikat A7');
        [$ownerB, $tenantB] = $this->daftar('b7@ujian.test', 'Syarikat B7');

        $this->sebagai($ownerB, $tenantB);
        $this->postJson('/api/v1/ai-gateway/chat', [
            'messages' => [['role' => 'user', 'content' => 'Hai']],
        ])->assertOk();

        $this->sebagai($ownerA, $tenantA);
        $this->getJson('/api/v1/ai-gateway/admin/usage/events')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');
    }
}
