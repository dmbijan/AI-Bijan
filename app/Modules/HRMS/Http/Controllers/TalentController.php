<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Models\Applicant;
use App\Modules\HRMS\Models\BenefitClaim;
use App\Modules\HRMS\Models\BenefitPlan;
use App\Modules\HRMS\Models\ChecklistTemplate;
use App\Modules\HRMS\Models\CompensationChange;
use App\Modules\HRMS\Models\DesignationSkill;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\EmployeeSkill;
use App\Modules\HRMS\Models\ExitInterview;
use App\Modules\HRMS\Models\Goal;
use App\Modules\HRMS\Models\LifecycleTask;
use App\Modules\HRMS\Models\Recognition;
use App\Modules\HRMS\Models\Review;
use App\Modules\HRMS\Models\SalaryBand;
use App\Modules\HRMS\Models\Skill;
use App\Modules\HRMS\Models\Survey;
use App\Modules\HRMS\Models\SurveyResponse;
use App\Modules\HRMS\Models\Vacancy;
use App\Modules\HRMS\Services\HrBriefService;
use App\Modules\HRMS\Services\TalentService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Kelompok 2 HR: HR-004, 009, 010, 011, 012, 013, 014 dan AI-006. */
class TalentController extends Controller
{
    public function __construct(private TalentService $svc) {}

    private function ok($data, int $code = 200): JsonResponse
    {
        return response()->json(['data' => $data], $code);
    }

    private function names(): array
    {
        return Employee::get(['id', 'first_name', 'last_name'])->mapWithKeys(fn ($e) => [$e->id => trim($e->first_name.' '.$e->last_name)])->all();
    }

    private function ex(string $table)
    {
        return Rule::exists($table, 'id')->where('tenant_id', app(TenantContext::class)->id());
    }

    private function empRule(): array
    {
        // Pekerja mesti dalam syarikat ini DAN dalam skop cawangan/jabatan ahli.
        $ids = app(\App\Support\Tenancy\DataScope::class)->employees();

        return ['required', 'integer', $ids === null ? $this->ex('hrms_employees') : $this->ex('hrms_employees')->whereIn('id', $ids ?: [0])];
    }

    // ------------------------------------------------------------ ATS

    public function vacancies(): JsonResponse
    {
        $v = Vacancy::orderByRaw("status = 'open' desc")->latest('id')->get();
        $counts = Applicant::selectRaw('vacancy_id, stage, count(*) n')->groupBy('vacancy_id', 'stage')->get()->groupBy('vacancy_id');

        return $this->ok($v->map(fn ($x) => $x->toArray() + ['pipeline' => ($counts[$x->id] ?? collect())->pluck('n', 'stage')]));
    }

    public function saveVacancy(Request $r, ?int $id = null): JsonResponse
    {
        $d = $r->validate([
            'title' => 'required|string|max:150', 'department_id' => ['nullable', 'integer', $this->ex('hrms_departments')],
            'employment_type' => 'nullable|in:full_time,part_time,contract,freelance,intern', 'openings' => 'nullable|integer|min:1|max:999',
            'salary_min' => 'nullable|numeric|min:0', 'salary_max' => 'nullable|numeric|gte:salary_min', 'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:draft,open,closed', 'closes_on' => 'nullable|date',
        ]);
        $v = $id ? tap(Vacancy::findOrFail($id))->update($d) : Vacancy::create($d);

        return $this->ok($v, $id ? 200 : 201);
    }

    public function applicants(Request $r): JsonResponse
    {
        $q = Applicant::query()->when($r->vacancy_id, fn ($q, $v) => $q->where('vacancy_id', $v))->latest('id');

        return $this->ok(['applicants' => $q->limit(500)->get(), 'pipeline' => $this->svc->pipeline($r->vacancy_id ? (int) $r->vacancy_id : null)]);
    }

    public function storeApplicant(Request $r): JsonResponse
    {
        $d = $r->validate([
            'vacancy_id' => ['required', 'integer', $this->ex('hrms_vacancies')], 'name' => 'required|string|max:150', 'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30', 'source' => 'nullable|string|max:40', 'notes' => 'nullable|string|max:3000',
        ]);
        Vacancy::findOrFail($d['vacancy_id']);
        $a = Applicant::create($d + ['stage' => 'applied', 'stage_changed_at' => now()]);

        return $this->ok($a, 201);
    }

    public function moveApplicant(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'stage' => 'required|in:applied,screening,interview,offer,rejected', 'score' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:3000', 'interview_at' => 'nullable|date', 'offer_salary' => 'nullable|numeric|min:0',
        ]);

        return $this->ok($this->svc->moveApplicant(Applicant::findOrFail($id), $d['stage'], $d));
    }

    public function hire(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'email' => 'nullable|email|max:150', 'date_joined' => 'nullable|date', 'designation_id' => ['nullable', 'integer', $this->ex('hrms_designations')],
            'probation_months' => 'nullable|integer|min:0|max:12',
        ]);

        return $this->ok($this->svc->hire(Applicant::findOrFail($id), $d), 201);
    }

    // ------------------------------------------------------------ prestasi

    public function goals(Request $r): JsonResponse
    {
        $r->validate(['period' => 'nullable|string|max:12', 'employee_id' => 'nullable|integer']);
        $g = Goal::query()->when($r->period, fn ($q, $p) => $q->where('period', $p))->when($r->employee_id, fn ($q, $e) => $q->where('employee_id', $e))->orderBy('employee_id')->get();
        $n = $this->names();

        return $this->ok($g->map(fn ($x) => $x->toArray() + ['employee_name' => $n[$x->employee_id] ?? '—', 'progress' => $x->target > 0 ? round(min($x->actual / $x->target, 1.2) * 100, 1) : 0]));
    }

    public function saveGoal(Request $r, ?int $id = null): JsonResponse
    {
        $d = $r->validate([
            'employee_id' => $id ? 'sometimes|integer' : $this->empRule(), 'period' => ($id ? 'sometimes' : 'required').'|string|max:12', 'title' => ($id ? 'sometimes' : 'required').'|string|max:200',
            'target' => 'nullable|numeric|min:0', 'actual' => 'nullable|numeric|min:0', 'weight' => 'nullable|integer|min:1|max:100', 'due_on' => 'nullable|date',
        ]);
        $g = $id ? tap(Goal::findOrFail($id))->update(collect($d)->except('employee_id')->all()) : Goal::create($d);

        return $this->ok($g, $id ? 200 : 201);
    }

    public function reviews(Request $r): JsonResponse
    {
        $n = $this->names();
        $rv = Review::query()->when($r->period, fn ($q, $p) => $q->where('period', $p))->latest('id')->limit(500)->get();

        return $this->ok($rv->map(fn ($x) => $x->toArray() + ['employee_name' => $n[$x->employee_id] ?? '—', 'live_goal_score' => $this->svc->goalScore($x->employee_id, $x->period)]));
    }

    public function saveReview(Request $r): JsonResponse
    {
        $d = $r->validate([
            'employee_id' => $this->empRule(), 'period' => 'required|string|max:12', 'rating' => 'nullable|integer|min:1|max:5',
            'strengths' => 'nullable|string|max:3000', 'improvements' => 'nullable|string|max:3000', 'submit' => 'nullable|boolean',
        ]);
        $rv = Review::firstOrNew(['employee_id' => $d['employee_id'], 'period' => $d['period']]);
        if ($rv->exists && $rv->status !== 'draft') {
            return response()->json(['message' => 'Penilaian tempoh ini sudah dihantar.'], 422);
        }
        $fields = collect($d)->only(['rating', 'strengths', 'improvements'])->all();
        if (! empty($d['submit'])) {
            $rv->reviewer_id = $r->user()->id;
            $rv->status = $rv->status ?? 'draft';
            $rv->save();

            return $this->ok($this->svc->submitReview($rv, $fields));
        }
        $rv->fill($fields + ['reviewer_id' => $r->user()->id, 'status' => 'draft']);
        $rv->goal_score = $this->svc->goalScore($rv->employee_id, $rv->period);
        $rv->save();

        return $this->ok($rv, 201);
    }

    // ------------------------------------------------------------ ganjaran

    public function compensation(): JsonResponse
    {
        $n = $this->names();
        $bands = SalaryBand::orderBy('min_salary')->get();
        $emps = Employee::whereIn('status', ['active', 'on_leave'])->get();
        $staff = $emps->map(function ($e) use ($bands, $n) {
            $sal = $this->svc->currentSalary($e);
            $band = $e->metadata['salary_grade'] ?? null ? $bands->firstWhere('grade', $e->metadata['salary_grade']) : null;

            return ['employee_id' => $e->id, 'name' => $n[$e->id] ?? '—', 'salary' => $sal, 'grade' => $band?->grade, 'position' => $this->svc->bandPosition($sal, $band)];
        });

        return $this->ok([
            'bands' => $bands,
            'staff' => $staff,
            'changes' => CompensationChange::latest('id')->limit(200)->get()->map(fn ($c) => $c->toArray() + ['employee_name' => $n[$c->employee_id] ?? '—']),
        ]);
    }

    public function saveBand(Request $r): JsonResponse
    {
        $d = $r->validate(['grade' => 'required|string|max:20', 'name' => 'required|string|max:100', 'min_salary' => 'required|numeric|min:0', 'max_salary' => 'required|numeric|gte:min_salary']);
        $b = SalaryBand::updateOrCreate(['grade' => $d['grade']], $d);

        return $this->ok($b, 201);
    }

    public function setGrade(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['grade' => 'nullable|string|max:20']);
        $e = Employee::findOrFail($id);
        if ($d['grade'] && ! SalaryBand::where('grade', $d['grade'])->exists()) {
            return response()->json(['message' => 'Gred tidak wujud.'], 422);
        }
        $e->update(['metadata' => array_merge($e->metadata ?? [], ['salary_grade' => $d['grade']])]);

        return $this->ok(['employee_id' => $e->id, 'grade' => $d['grade']]);
    }

    public function requestChange(Request $r): JsonResponse
    {
        $d = $r->validate([
            'employee_id' => $this->empRule(), 'type' => 'required|in:increment,bonus,allowance,promotion', 'new_salary' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0', 'effective_on' => 'required|date', 'reason' => 'nullable|string|max:255',
        ]);

        return $this->ok($this->svc->requestChange(Employee::findOrFail($d['employee_id']), collect($d)->except('employee_id')->all()), 201);
    }

    public function decideChange(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['approve' => 'required|boolean']);

        return $this->ok($this->svc->decideChange(CompensationChange::findOrFail($id), (bool) $d['approve']));
    }

    // ------------------------------------------------------------ kemahiran

    public function skills(): JsonResponse
    {
        return $this->ok(['skills' => Skill::orderBy('category')->orderBy('name')->get(), 'requirements' => DesignationSkill::get()] + $this->svc->skillMatrix());
    }

    public function saveSkill(Request $r): JsonResponse
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'category' => 'nullable|string|max:40']);

        return $this->ok(Skill::firstOrCreate(['name' => $d['name']], $d), 201);
    }

    public function rateSkill(Request $r): JsonResponse
    {
        $d = $r->validate(['employee_id' => $this->empRule(), 'skill_id' => ['required', 'integer', $this->ex('hrms_skills')], 'level' => 'required|integer|min:0|max:4', 'certified_until' => 'nullable|date']);
        Skill::findOrFail($d['skill_id']);

        return $this->ok(EmployeeSkill::updateOrCreate(['employee_id' => $d['employee_id'], 'skill_id' => $d['skill_id']], $d));
    }

    public function requireSkill(Request $r): JsonResponse
    {
        $d = $r->validate(['designation_id' => ['required', 'integer', $this->ex('hrms_designations')], 'skill_id' => ['required', 'integer', $this->ex('hrms_skills')], 'required_level' => 'required|integer|min:1|max:4']);
        Skill::findOrFail($d['skill_id']);

        return $this->ok(DesignationSkill::updateOrCreate(['designation_id' => $d['designation_id'], 'skill_id' => $d['skill_id']], $d));
    }

    // ------------------------------------------------------------ suara pekerja

    public function surveys(): JsonResponse
    {
        return $this->ok(Survey::latest('id')->get()->map(fn ($s) => $s->toArray() + ['responses' => SurveyResponse::where('survey_id', $s->id)->count()]));
    }

    public function storeSurvey(Request $r): JsonResponse
    {
        $d = $r->validate([
            'title' => 'required|string|max:150', 'anonymous' => 'nullable|boolean', 'closes_on' => 'nullable|date',
            'questions' => 'required|array|min:1|max:30', 'questions.*.text' => 'required|string|max:300', 'questions.*.type' => 'nullable|in:scale,text',
        ]);
        $d['questions'] = collect($d['questions'])->map(fn ($q) => ['id' => 'q'.Str::lower(Str::random(6)), 'text' => $q['text'], 'type' => $q['type'] ?? 'scale'])->all();

        return $this->ok(Survey::create($d + ['anonymous' => true, 'status' => 'open']), 201);
    }

    public function closeSurvey(int $id): JsonResponse
    {
        $s = Survey::findOrFail($id);
        $s->update(['status' => 'closed']);

        return $this->ok($s);
    }

    /** Sesiapa dalam syarikat (hrms.view) boleh menjawab sekali; dipadankan ke rekod pekerja melalui akaun log masuk. */
    public function respond(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['answers' => 'required|array']);
        $emp = Employee::where('user_id', $r->user()->id)->first();
        $this->svc->respond(Survey::findOrFail($id), $emp, $d['answers']);

        return $this->ok(['message' => 'Terima kasih, jawapan direkodkan.'], 201);
    }

    public function surveyResults(int $id): JsonResponse
    {
        return $this->ok($this->svc->surveyResults(Survey::findOrFail($id)));
    }

    public function recognitions(): JsonResponse
    {
        $n = $this->names();

        return $this->ok(Recognition::latest('id')->limit(100)->get()->map(fn ($x) => $x->toArray() + ['employee_name' => $n[$x->employee_id] ?? '—']));
    }

    public function recognise(Request $r): JsonResponse
    {
        $d = $r->validate(['employee_id' => $this->empRule(), 'message' => 'required|string|max:500', 'value_tag' => 'nullable|string|max:40']);

        return $this->ok(Recognition::create($d + ['from_user_id' => $r->user()->id]), 201);
    }

    // ------------------------------------------------------------ faedah

    public function benefits(): JsonResponse
    {
        $n = $this->names();
        $plans = BenefitPlan::orderBy('name')->get();
        $year = now()->year;
        $claims = BenefitClaim::latest('id')->limit(300)->get()->map(fn ($c) => $c->toArray() + [
            'employee_name' => $n[$c->employee_id] ?? '—',
            'plan_name' => $plans->firstWhere('id', $c->plan_id)?->name,
            'balance' => ($p = $plans->firstWhere('id', $c->plan_id)) ? round($p->annual_limit - $this->svc->usedThisYear($c->employee_id, $p->id, $year), 2) : null,
        ]);

        return $this->ok(['plans' => $plans, 'claims' => $claims]);
    }

    public function savePlan(Request $r, ?int $id = null): JsonResponse
    {
        $d = $r->validate([
            'name' => 'required|string|max:100', 'type' => 'nullable|in:medical,dental,optical,insurance,other', 'annual_limit' => 'required|numeric|min:0',
            'per_claim_limit' => 'nullable|numeric|min:0', 'eligible_after_days' => 'nullable|integer|min:0|max:3650',
            'panel_clinics' => 'nullable|array', 'panel_clinics.*' => 'string|max:120', 'is_active' => 'nullable|boolean',
        ]);
        $p = $id ? tap(BenefitPlan::findOrFail($id))->update($d) : BenefitPlan::create($d);

        return $this->ok($p, $id ? 200 : 201);
    }

    public function submitClaim(Request $r): JsonResponse
    {
        $d = $r->validate([
            'employee_id' => $this->empRule(), 'plan_id' => ['required', 'integer', $this->ex('hrms_benefit_plans')], 'claim_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01', 'provider' => 'nullable|string|max:150', 'receipt_ref' => 'nullable|string|max:60', 'notes' => 'nullable|string|max:255',
        ]);

        return $this->ok($this->svc->submitClaim(Employee::findOrFail($d['employee_id']), BenefitPlan::findOrFail($d['plan_id']), collect($d)->except(['employee_id', 'plan_id'])->all()), 201);
    }

    public function decideClaim(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['approve' => 'required|boolean', 'approved_amount' => 'nullable|numeric|min:0', 'notes' => 'nullable|string|max:255']);

        return $this->ok($this->svc->decideClaim(BenefitClaim::findOrFail($id), (bool) $d['approve'], isset($d['approved_amount']) ? (float) $d['approved_amount'] : null, $d['notes'] ?? null));
    }

    // ------------------------------------------------------------ masuk / keluar

    public function lifecycle(Request $r): JsonResponse
    {
        $r->validate(['type' => 'nullable|in:onboarding,offboarding']);
        $n = $this->names();
        $tasks = LifecycleTask::query()->when($r->type, fn ($q, $t) => $q->where('type', $t))->orderBy('employee_id')->orderBy('sort')->get();
        $groups = $tasks->groupBy(fn ($t) => $t->type.'-'.$t->employee_id)->map(fn ($g) => [
            'employee_id' => $g->first()->employee_id, 'employee_name' => $n[$g->first()->employee_id] ?? '—', 'type' => $g->first()->type,
            'done' => $g->whereNotNull('done_at')->count(), 'total' => $g->count(),
            'overdue' => $g->whereNull('done_at')->filter(fn ($t) => $t->due_on && $t->due_on->lt(today()))->count(), 'tasks' => $g->values(),
        ])->values()->sortBy('done')->values();

        return $this->ok(['groups' => $groups, 'templates' => ChecklistTemplate::latest('id')->get(), 'exits' => ExitInterview::latest('id')->limit(100)->get()
            ->map(fn ($x) => $x->toArray() + ['employee_name' => $n[$x->employee_id] ?? '—'])]);
    }

    public function saveTemplate(Request $r): JsonResponse
    {
        $d = $r->validate([
            'type' => 'required|in:onboarding,offboarding', 'name' => 'required|string|max:100', 'items' => 'required|array|min:1|max:50',
            'items.*.title' => 'required|string|max:200', 'items.*.owner' => 'nullable|string|max:40', 'items.*.due_days' => 'nullable|integer|min:-60|max:365',
        ]);

        return $this->ok(ChecklistTemplate::create($d), 201);
    }

    public function startOnboarding(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['template_id' => 'nullable|integer']);

        return $this->ok(['created' => $this->svc->startChecklist(Employee::findOrFail($id), 'onboarding', $d['template_id'] ?? null)], 201);
    }

    public function startOffboarding(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['last_worked_date' => 'required|date', 'reason' => 'nullable|string|max:255', 'exit_type' => 'nullable|in:resigned,terminated']);
        $e = Employee::findOrFail($id);
        if ($e->user_id && $e->user_id === $r->user()->id) {
            return response()->json(['message' => 'Anda tidak boleh memproses keluar diri sendiri.'], 422);
        }

        return $this->ok($this->svc->startOffboarding($e, $d));
    }

    public function toggleTask(Request $r, int $id): JsonResponse
    {
        $t = LifecycleTask::findOrFail($id);
        $t->update($t->done_at ? ['done_at' => null, 'done_by' => null] : ['done_at' => now(), 'done_by' => $r->user()->id]);

        return $this->ok($t);
    }

    public function exitInterview(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['reason' => 'required|in:gaji,kerjaya,pengurus,beban,peribadi,lain', 'would_rehire' => 'nullable|boolean', 'feedback' => 'nullable|string|max:3000']);
        Employee::findOrFail($id);

        return $this->ok(ExitInterview::updateOrCreate(['employee_id' => $id], $d), 201);
    }

    // ------------------------------------------------------------ AI CHRO

    public function brief(HrBriefService $b): JsonResponse
    {
        return $this->ok($b->brief());
    }
}
