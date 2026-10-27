<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalEntryLine;
use App\Modules\FlowAutomation\Models\WorkflowDefinition;
use App\Modules\FlowAutomation\Models\WorkflowRun;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — Finance (COA, jurnal, baris jurnal) dan FlowAutomation
 * (definisi aliran kerja + larian).
 *
 * Ditambah 28 Okt 2026 (T-111). Kedua-dua modul sebelum ini dikira "diliputi"
 * hanya kerana fail ujian lain mengimport modelnya (korpus gabungan).
 *
 * • Finance: buku lejar ialah data kewangan paling sensitif. `JournalEntryLine`
 *   ialah jadual ANAK (tiada `tenant_id`) — skop melalui induk.
 * • FlowAutomation: `workflow_definitions`/`workflow_runs` bertindih dengan
 *   modul lain; kebocoran mendedahkan automasi pesaing.
 */
class FinanceFlowIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Finance,FlowAutomation';

    protected array $model = [
        ChartOfAccount::class,
        JournalEntry::class,
        WorkflowDefinition::class,
        WorkflowRun::class,
    ];

    protected ?string $laluanApi = '/api/v1/finance/journal-entries';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ChartOfAccount::class) {
            return [
                'code' => '4-' . $n,
                'name' => 'Hasil Rahsia Tenant Satu ' . $n,
                'type' => 'revenue',
                'normal_balance' => 'credit',
            ];
        }

        if ($this->rekodSemasa === JournalEntry::class) {
            return [
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => now()->toDateString(),
                'status' => 'draft',
                'description' => 'Jurnal Rahsia Tenant Satu ' . $n,
                'fiscal_year' => (int) date('Y'),
                'period' => (int) date('n'),
            ];
        }

        if ($this->rekodSemasa === WorkflowDefinition::class) {
            return [
                'name' => 'Aliran Rahsia Tenant Satu ' . $n,
                'trigger_type' => 'manual',
                'steps' => [],
                'is_active' => true,
            ];
        }

        $aliran = WorkflowDefinition::create([
            'name' => 'Aliran induk untuk larian ' . $n,
            'trigger_type' => 'manual',
            'steps' => [],
        ]);

        if ($this->rekodSemasa === WorkflowRun::class) {
            return [
                'workflow_id' => $aliran->id,
                'idempotency_key' => 'rahsia-' . $n . '-' . uniqid(),
                'status' => 'completed',
                'trigger_source' => 'manual',
            ];
        }

        return ['name' => 'Aliran Rahsia Tenant Satu ' . $n, 'trigger_type' => 'manual'];
    }
}
