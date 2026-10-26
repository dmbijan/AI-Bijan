<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TenantUser;
use App\Models\Timesheet;
use App\Modules\Projects\ProjectService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pengurus projek nampak semua; ahli biasa hanya projek yang dia ahli. */
class ProjectController extends Controller
{
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function urus(): bool
    {
        return $this->ctx()->can('projects.manage');
    }

    private function projek(int $id): Project
    {
        $p = Project::findOrFail($id);
        abort_unless($this->urus() || $p->owner_id === auth()->id() || $p->members()->where('users.id', auth()->id())->exists(), 404);

        return $p;
    }

    private function ahliTenant(): \Closure
    {
        return fn ($attr, $v, $fail) => $v && ! TenantUser::where('user_id', $v)->where('status', 'active')->exists() ? $fail('Pengguna bukan ahli syarikat.') : null;
    }

    public function index()
    {
        $q = Project::withCount('tasks')->orderByDesc('id');
        if (! $this->urus()) {
            $q->where(fn ($w) => $w->where('owner_id', auth()->id())->orWhereHas('members', fn ($m) => $m->where('users.id', auth()->id())));
        }

        return response()->json($q->get());
    }

    public function save(Request $r, ?int $id = null)
    {
        abort_unless($this->urus(), 403);
        $tid = $this->ctx()->id();
        $d = $r->validate(['code' => ['required', 'string', 'max:30', Rule::unique('projects')->where('tenant_id', $tid)->ignore($id)], 'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:5000', 'client' => 'nullable|string|max:150', 'status' => 'nullable|in:perancangan,aktif,ditangguh,selesai,dibatalkan',
            'start_date' => 'nullable|date', 'end_date' => 'nullable|date|after_or_equal:start_date', 'budget' => 'nullable|numeric|min:0', 'hourly_rate' => 'nullable|numeric|min:0',
            'owner_id' => ['nullable', 'integer', $this->ahliTenant()], 'members' => 'nullable|array', 'members.*' => ['integer', $this->ahliTenant()]]);
        $p = $id ? Project::findOrFail($id) : new Project;
        $before = $p->exists ? $p->toArray() : null;
        $p->fill(collect($d)->except('members')->all() + ['owner_id' => $d['owner_id'] ?? $p->owner_id ?? auth()->id()])->save();
        if (isset($d['members'])) {
            $p->members()->sync(collect($d['members'])->mapWithKeys(fn ($u) => [$u => ['tenant_id' => $tid]])->all());
        }
        app(AuditLogger::class)->log($id ? 'projects.updated' : 'projects.created', $p, $before, collect($d)->except('description')->all());

        return response()->json($p, $id ? 200 : 201);
    }

    public function show(int $id, ProjectService $svc)
    {
        $p = $this->projek($id);

        return response()->json(['project' => $p->load('members:id,name'), 'summary' => $svc->summary($p)]);
    }

    /** Papan Kanban: tugasan ikut lajur status. */
    public function board(int $id)
    {
        $p = $this->projek($id);

        return response()->json($p->tasks()->with('dependencies:id')->orderBy('sort')->get()->groupBy('status'));
    }

    public function gantt(int $id, ProjectService $svc)
    {
        return response()->json($svc->gantt($this->projek($id)));
    }

    public function saveTask(Request $r, int $projectId, ?int $id = null)
    {
        $p = $this->projek($projectId);
        $d = $r->validate(['title' => 'required|string|max:200', 'description' => 'nullable|string|max:10000', 'priority' => 'nullable|in:rendah,sederhana,tinggi,kritikal',
            'assignee_id' => ['nullable', 'integer', $this->ahliTenant()], 'start_date' => 'nullable|date', 'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimate_hours' => 'nullable|numeric|min:0|max:10000', 'is_milestone' => 'boolean', 'sort' => 'nullable|integer|min:0', 'parent_id' => 'nullable|integer']);
        if (! empty($d['parent_id'])) {
            abort_unless(Task::where(['id' => $d['parent_id'], 'project_id' => $p->id])->exists(), 422, 'Tugasan induk bukan dalam projek ini.');
        }
        $t = $id ? Task::where('project_id', $p->id)->findOrFail($id) : new Task(['project_id' => $p->id]);
        $before = $t->exists ? $t->toArray() : null;
        $t->fill($d)->save();
        app(AuditLogger::class)->log($id ? 'projects.task_updated' : 'projects.task_created', $t, $before, collect($d)->except('description')->all());

        return response()->json($t, $id ? 200 : 201);
    }

    public function taskStatus(Request $r, int $id, ProjectService $svc)
    {
        $d = $r->validate(['status' => 'required|in:todo,doing,review,done']);
        $t = Task::findOrFail($id);
        $this->projek($t->project_id);

        return response()->json($svc->setStatus($t, $d['status']));
    }

    public function addDependency(Request $r, int $id, ProjectService $svc)
    {
        $d = $r->validate(['depends_on_id' => 'required|integer']);
        $t = Task::findOrFail($id);
        $this->projek($t->project_id);
        $svc->addDependency($t, Task::findOrFail($d['depends_on_id']));

        return response()->json(['ok' => true]);
    }

    public function comment(Request $r, int $id)
    {
        $d = $r->validate(['body' => 'required|string|max:5000', 'file_ulid' => 'nullable|string|size:26']);
        $t = Task::findOrFail($id);
        $this->projek($t->project_id);

        return response()->json(TaskComment::create(['task_id' => $t->id, 'user_id' => auth()->id()] + $d), 201);
    }

    public function comments(int $id)
    {
        $t = Task::findOrFail($id);
        $this->projek($t->project_id);

        return response()->json($t->comments()->orderBy('id')->get());
    }

    public function logTime(Request $r, int $projectId, ProjectService $svc)
    {
        $p = $this->projek($projectId);
        $d = $r->validate(['work_date' => 'required|date|before_or_equal:today', 'hours' => 'required|numeric|min:0.25|max:24', 'task_id' => 'nullable|integer', 'note' => 'nullable|string|max:255', 'billable' => 'boolean']);

        return response()->json($svc->logTime($p, auth()->id(), $d), 201);
    }

    public function timesheets(Request $r, int $projectId)
    {
        $p = $this->projek($projectId);
        $q = Timesheet::where('project_id', $p->id)->orderByDesc('work_date');
        if (! $this->urus()) {
            $q->where('user_id', auth()->id()); // staf hanya nampak jam sendiri
        }

        return response()->json($q->paginate(100));
    }

    public function decideTime(Request $r, int $id, ProjectService $svc)
    {
        abort_unless($this->urus(), 403);
        $d = $r->validate(['decision' => 'required|in:diluluskan,ditolak']);

        return response()->json($svc->decideTime(Timesheet::findOrFail($id), $d['decision'], auth()->id()));
    }
}
