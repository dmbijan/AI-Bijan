<?php

namespace App\Modules\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Models\Timesheet;
use App\Services\AuditLogger;
use App\Support\Dec;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    /** Tukar status tugasan. Tidak boleh mula/siap jika tugasan yang menjadi kebergantungan belum siap. */
    public function setStatus(Task $t, string $status): Task
    {
        if (in_array($status, ['doing', 'review', 'done'], true)) {
            $belum = $t->dependencies()->where('status', '!=', 'done')->pluck('title');
            if ($belum->isNotEmpty()) {
                throw ValidationException::withMessages(['status' => 'Perlu siapkan dahulu: '.$belum->implode(', ')]);
            }
        }
        $before = ['status' => $t->status];
        $t->update(['status' => $status, 'completed_at' => $status === 'done' ? ($t->completed_at ?? now()) : null]);
        app(AuditLogger::class)->log('projects.task_status', $t, $before, ['status' => $status]);

        return $t;
    }

    /** Tambah kebergantungan (sama projek, tiada kitaran). */
    public function addDependency(Task $t, Task $dep): void
    {
        if ($t->project_id !== $dep->project_id || $t->id === $dep->id) {
            throw ValidationException::withMessages(['depends_on_id' => 'Kebergantungan mesti tugasan lain dalam projek yang sama.']);
        }
        if ($this->bergantung($dep->id, $t->id)) {
            throw ValidationException::withMessages(['depends_on_id' => 'Kebergantungan ini membentuk kitaran.']);
        }
        $t->dependencies()->syncWithoutDetaching([$dep->id => ['tenant_id' => $t->tenant_id]]);
    }

    /** Adakah $a (secara langsung/tidak) bergantung pada $b? */
    private function bergantung(int $a, int $b, array $dilawat = []): bool
    {
        if ($a === $b) {
            return true;
        }
        $dilawat[] = $a;
        foreach (DB::table('task_dependencies')->where('task_id', $a)->pluck('depends_on_id') as $n) {
            if (! in_array($n, $dilawat, true) && $this->bergantung($n, $b, $dilawat)) {
                return true;
            }
        }

        return false;
    }

    /** Rekod jam kerja: 0.25–24 jam, jumlah sehari seorang ≤ 24 jam. */
    public function logTime(Project $p, int $userId, array $d): Timesheet
    {
        $h = Dec::n($d['hours']);
        $hari = Dec::n(Timesheet::where(['user_id' => $userId, 'work_date' => $d['work_date']])->whereIn('status', ['dihantar', 'diluluskan', 'draf'])->sum('hours'));
        if (Dec::cmp(Dec::add($hari, $h), 24) > 0) {
            throw ValidationException::withMessages(['hours' => 'Jumlah jam sehari melebihi 24.']);
        }
        if (! empty($d['task_id']) && ! Task::where(['id' => $d['task_id'], 'project_id' => $p->id])->exists()) {
            throw ValidationException::withMessages(['task_id' => 'Tugasan bukan dalam projek ini.']);
        }
        $ts = Timesheet::create(['project_id' => $p->id, 'task_id' => $d['task_id'] ?? null, 'user_id' => $userId, 'work_date' => $d['work_date'], 'hours' => $h,
            'note' => $d['note'] ?? null, 'billable' => $d['billable'] ?? true, 'status' => 'dihantar']);
        app(AuditLogger::class)->log('projects.time_logged', $ts, null, ['hours' => $h, 'date' => $d['work_date']]);

        return $ts;
    }

    public function decideTime(Timesheet $ts, string $keputusan, int $approverId): Timesheet
    {
        if ($ts->user_id === $approverId) {
            throw ValidationException::withMessages(['status' => 'Tidak boleh meluluskan jam kerja sendiri.']);
        }
        if ($ts->status !== 'dihantar') {
            throw ValidationException::withMessages(['status' => 'Hanya jam kerja yang dihantar boleh diputuskan.']);
        }
        $ts->update(['status' => $keputusan, 'approved_by' => $approverId]);
        app(AuditLogger::class)->log('projects.time_'.$keputusan, $ts);

        return $ts;
    }

    public function summary(Project $p): array
    {
        $tasks = $p->tasks()->get();
        $jam = Timesheet::where('project_id', $p->id)->where('status', 'diluluskan');
        $boleh = (clone $jam)->where('billable', true)->sum('hours');
        $anggaran = $tasks->sum('estimate_hours');
        $sebenar = Dec::n($jam->sum('hours'));

        return [
            'tasks' => ['total' => $tasks->count(), 'by_status' => $tasks->countBy('status'), 'overdue' => $tasks->where('status', '!=', 'done')->filter(fn ($t) => $t->due_date && $t->due_date->isPast())->count(),
                'progress_pct' => $tasks->count() ? round($tasks->where('status', 'done')->count() / $tasks->count() * 100, 1) : 0],
            'hours' => ['estimate' => Dec::round($anggaran), 'approved' => Dec::round($sebenar), 'billable' => Dec::round($boleh), 'pending' => Dec::round(Timesheet::where('project_id', $p->id)->where('status', 'dihantar')->sum('hours'))],
            'billable_amount' => $p->hourly_rate ? Dec::round(Dec::mul($boleh, $p->hourly_rate)) : null,
            'budget' => $p->budget, 'budget_used_pct' => ($p->budget && $p->hourly_rate) ? round((float) Dec::div(Dec::mul($sebenar, $p->hourly_rate), $p->budget) * 100, 1) : null,
        ];
    }

    /** Data carta Gantt: tugasan, tarikh, kebergantungan, kemajuan. */
    public function gantt(Project $p): array
    {
        return $p->tasks()->with('dependencies:id')->orderBy('start_date')->orderBy('sort')->get()->map(fn (Task $t) => [
            'id' => $t->id, 'title' => $t->title, 'start' => $t->start_date?->toDateString(), 'end' => ($t->due_date ?? $t->start_date)?->toDateString(),
            'milestone' => $t->is_milestone, 'status' => $t->status, 'assignee_id' => $t->assignee_id, 'depends_on' => $t->dependencies->pluck('id'),
            'progress' => ['todo' => 0, 'doing' => 50, 'review' => 80, 'done' => 100][$t->status],
        ])->all();
    }
}
