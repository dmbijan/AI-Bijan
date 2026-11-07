<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Projects\Models\Project;
use App\Modules\WorkManagement\Models\ProjectBaseline;
use App\Modules\WorkManagement\Models\ProjectBudgetLine;
use App\Modules\WorkManagement\Models\ProjectPortfolio;
use App\Modules\WorkManagement\Models\ProjectTimeline;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul WorkManagement (Gantt, bajet, portfolio).
 *
 * Ditambah 8 Nov 2026. Jadual projek dan bajet mendedahkan pelan
 * pelaksanaan serta kos perbelanjaan syarikat.
 */
class WorkManagementTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'WorkManagement';

    protected array $model = [
        ProjectTimeline::class,
        ProjectBaseline::class,
        ProjectBudgetLine::class,
        ProjectPortfolio::class,
    ];

    protected ?string $laluanApi = '/api/v1/work-management/portfolios';

    protected ?string $namaRahsia = 'Portfolio Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $projek = Project::create([
            'name' => 'Projek Anak '.$n,
            'code' => 'PRJ-ANAK-'.$n,
            // CHECK constraint: status guna nilai BM.
            'status' => 'aktif',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'budget' => 10000,
        ]);

        switch ($this->rekodSemasa) {
            case ProjectTimeline::class:
                return [
                    'project_id' => $projek->id,
                    'name' => 'Aktiviti Rahsia Tenant Satu '.$n,
                    'type' => ProjectTimeline::TYPE_GANTT,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(5)->toDateString(),
                    'duration_days' => 5,
                ];

            case ProjectBaseline::class:
                return [
                    'project_id' => $projek->id,
                    'name' => 'Garis Dasar Rahsia Tenant Satu '.$n,
                    'captured_at' => now(),
                    'planned_duration_days' => 30,
                    'planned_cost' => 10000,
                ];

            case ProjectBudgetLine::class:
                return [
                    'project_id' => $projek->id,
                    'name' => 'Bajet Rahsia Tenant Satu '.$n,
                    'category' => ProjectBudgetLine::CATEGORY_LABOUR,
                    'planned_cost' => 5000,
                    'actual_cost' => 6000,
                ];

            default:
                return [
                    'name' => 'Portfolio Rahsia Tenant Satu '.$n,
                    'slug' => 'portfolio-rahsia-'.$n,
                    'status' => 'active',
                    'is_active' => true,
                ];
        }
    }
}
