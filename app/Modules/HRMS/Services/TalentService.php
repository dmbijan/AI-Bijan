<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\Applicant;
use App\Modules\HRMS\Models\BenefitClaim;
use App\Modules\HRMS\Models\BenefitPlan;
use App\Modules\HRMS\Models\ChecklistTemplate;
use App\Modules\HRMS\Models\CompensationChange;
use App\Modules\HRMS\Models\DesignationSkill;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\EmployeeSkill;
use App\Modules\HRMS\Models\Goal;
use App\Modules\HRMS\Models\LifecycleTask;
use App\Modules\HRMS\Models\Review;
use App\Modules\HRMS\Models\SalaryBand;
use App\Modules\HRMS\Models\Survey;
use App\Modules\HRMS\Models\SurveyResponse;
use App\Modules\HRMS\Models\Vacancy;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Kelompok 2 HR: pengambilan, prestasi, ganjaran, kemahiran, suara pekerja, faedah, masuk/keluar.
 * Keputusan yang melibatkan duit (kenaikan gaji, bonus, tuntutan) sentiasa perlu kelulusan manusia.
 */
class TalentService
{
    public const STAGES = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];

    /** Survey tanpa nama hanya tunjuk keputusan bila responden cukup ramai (elak teka siapa jawab). */
    public const MIN_ANON_RESPONSES = 3;

    public const DEFAULT_ONBOARDING = [
        ['title' => 'Kumpul salinan IC, sijil & butiran bank', 'owner' => 'HR', 'due_days' => 0],
        ['title' => 'Daftar KWSP, PERKESO & SIP', 'owner' => 'HR', 'due_days' => 7],
        ['title' => 'Tandatangan surat tawaran & kontrak', 'owner' => 'HR', 'due_days' => 0],
        ['title' => 'Cipta akaun AI Bijan & beri peranan', 'owner' => 'Pentadbir', 'due_days' => 1],
        ['title' => 'Serah peralatan (uniform, kunci, peranti)', 'owner' => 'Pengurus', 'due_days' => 1],
        ['title' => 'Taklimat SOP & keselamatan', 'owner' => 'Pengurus', 'due_days' => 3],
        ['title' => 'Semakan 30 hari bersama pengurus', 'owner' => 'Pengurus', 'due_days' => 30],
    ];

    public const DEFAULT_OFFBOARDING = [
        ['title' => 'Terima surat letak jawatan / notis', 'owner' => 'HR', 'due_days' => 0],
        ['title' => 'Pulangkan peralatan, kunci & uniform', 'owner' => 'Pengurus', 'due_days' => 0],
        ['title' => 'Tarik balik akses AI Bijan & sistem lain', 'owner' => 'Pentadbir', 'due_days' => 0],
        ['title' => 'Kira gaji akhir, baki cuti & potongan', 'owner' => 'Kewangan', 'due_days' => 3],
        ['title' => 'Temu bual keluar', 'owner' => 'HR', 'due_days' => 0],
        ['title' => 'Keluarkan surat pengesahan kerja', 'owner' => 'HR', 'due_days' => 7],
    ];

    private function log(string $action, $model, ?array $old, array $new): void
    {
        app(AuditLogger::class)->log($action, $model, $old, $new);
    }

    // ---------------------------------------------------------------- HR-004 ATS

    public function moveApplicant(Applicant $a, string $stage, array $extra = []): Applicant
    {
        if (! in_array($stage, self::STAGES, true)) {
            throw ValidationException::withMessages(['stage' => 'Peringkat tidak sah.']);
        }
        if (in_array($a->stage, ['hired', 'rejected'], true)) {
            throw ValidationException::withMessages(['stage' => 'Calon ini sudah '.($a->stage === 'hired' ? 'diambil bekerja' : 'ditolak').'.']);
        }
        if ($stage === 'hired') {
            throw ValidationException::withMessages(['stage' => 'Guna butang Ambil Bekerja supaya rekod pekerja dicipta.']);
        }
        $old = ['stage' => $a->stage];
        $a->fill(array_intersect_key($extra, array_flip(['score', 'notes', 'interview_at', 'offer_salary'])));
        $a->stage = $stage;
        $a->stage_changed_at = now();
        $a->save();
        $this->log('hrms.applicant_moved', $a, $old, ['stage' => $stage]);

        return $a;
    }

    /** Calon diterima → cipta pekerja + senarai semak masuk kerja. */
    public function hire(Applicant $a, array $d): Employee
    {
        if ($a->stage === 'hired') {
            throw ValidationException::withMessages(['stage' => 'Calon ini sudah diambil bekerja.']);
        }
        if ($a->stage === 'rejected') {
            throw ValidationException::withMessages(['stage' => 'Calon yang ditolak tidak boleh diambil bekerja.']);
        }
        $email = $d['email'] ?? $a->email;
        if (! $email) {
            throw ValidationException::withMessages(['email' => 'Emel calon diperlukan untuk rekod pekerja.']);
        }
        if (Employee::withoutGlobalScopes()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Emel ini sudah digunakan oleh pekerja lain.']);
        }

        return DB::transaction(function () use ($a, $d, $email) {
            $vacancy = Vacancy::find($a->vacancy_id);
            [$first, $last] = array_pad(explode(' ', trim($a->name), 2), 2, null);
            $joined = $d['date_joined'] ?? now()->toDateString();
            $emp = Employee::create([
                'employee_no' => Employee::generateEmployeeNo(),
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => $a->phone,
                'department_id' => $vacancy?->department_id,
                'designation_id' => $d['designation_id'] ?? null,
                'employment_type' => $vacancy?->employment_type ?? 'full_time',
                'date_joined' => $joined,
                'probation_end_date' => Carbon::parse($joined)->addMonths((int) ($d['probation_months'] ?? 3))->toDateString(),
                'status' => 'active',
                'metadata' => ['hired_from_applicant' => $a->id, 'offer_salary' => $a->offer_salary],
            ]);
            $a->update(['stage' => 'hired', 'employee_id' => $emp->id, 'stage_changed_at' => now()]);
            $this->startChecklist($emp, 'onboarding');
            if ($vacancy && Applicant::where('vacancy_id', $vacancy->id)->where('stage', 'hired')->count() >= $vacancy->openings) {
                $vacancy->update(['status' => 'closed']);
            }
            $this->log('hrms.applicant_hired', $a, null, ['employee_id' => $emp->id]);

            return $emp;
        });
    }

    public function pipeline(?int $vacancyId = null): array
    {
        $q = Applicant::query()->when($vacancyId, fn ($q) => $q->where('vacancy_id', $vacancyId));
        $counts = (clone $q)->select('stage', DB::raw('count(*) as n'))->groupBy('stage')->pluck('n', 'stage');

        return collect(self::STAGES)->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)])->all();
    }

    // ---------------------------------------------------------------- HR-009 prestasi

    /** Pencapaian berwajaran (%): setiap sasaran dihadkan 120% supaya satu sasaran tak menutup yang lain. */
    public function goalScore(int $employeeId, string $period): ?float
    {
        $goals = Goal::where('employee_id', $employeeId)->where('period', $period)->get();
        $wt = $goals->sum('weight');
        if ($goals->isEmpty() || $wt <= 0) {
            return null;
        }
        $sum = $goals->sum(fn ($g) => $g->target > 0 ? min($g->actual / $g->target, 1.2) * $g->weight : 0);

        return round($sum / $wt * 100, 1);
    }

    public function submitReview(Review $r, array $d): Review
    {
        if ($r->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Penilaian sudah dihantar.']);
        }
        $r->fill($d);
        $r->goal_score = $this->goalScore($r->employee_id, $r->period);
        if (! $r->rating) {
            throw ValidationException::withMessages(['rating' => 'Beri penarafan 1–5 sebelum hantar.']);
        }
        $r->status = 'submitted';
        $r->submitted_at = now();
        $r->save();
        $this->log('hrms.review_submitted', $r, null, ['rating' => $r->rating, 'goal_score' => $r->goal_score]);

        return $r;
    }

    // ---------------------------------------------------------------- HR-010 ganjaran

    private function payrollRow(Employee $e)
    {
        return $e->ic_number ? DB::table('employees')->where('tenant_id', $e->tenant_id)->where('ic_no', $e->ic_number)->first() : null;
    }

    public function currentSalary(Employee $e): ?float
    {
        $p = $this->payrollRow($e);

        return $p ? (float) $p->basic_salary : (isset($e->metadata['offer_salary']) ? (float) $e->metadata['offer_salary'] : null);
    }

    public function requestChange(Employee $e, array $d): CompensationChange
    {
        $current = $this->currentSalary($e);
        if (in_array($d['type'], ['increment', 'promotion'], true)) {
            if (empty($d['new_salary'])) {
                throw ValidationException::withMessages(['new_salary' => 'Masukkan gaji baharu.']);
            }
            $d['amount'] = $current !== null ? round($d['new_salary'] - $current, 2) : 0;
        } elseif (empty($d['amount']) || $d['amount'] <= 0) {
            throw ValidationException::withMessages(['amount' => 'Masukkan amaun lebih dari 0.']);
        }
        $c = CompensationChange::create($d + ['employee_id' => $e->id, 'current_salary' => $current, 'status' => 'pending', 'requested_by' => auth()->id()]);
        $this->log('hrms.compensation_requested', $c, null, ['type' => $c->type, 'amount' => $c->amount]);

        return $c;
    }

    /** Lulus: gaji baharu / elaun tetap ditulis ke rekod payroll (jika dipadankan dengan No. IC). */
    public function decideChange(CompensationChange $c, bool $approve): CompensationChange
    {
        if ($c->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Permohonan ini sudah diputuskan.']);
        }
        if ($c->requested_by && $c->requested_by === auth()->id()) {
            throw ValidationException::withMessages(['status' => 'Anda tidak boleh meluluskan permohonan anda sendiri.']);
        }

        return DB::transaction(function () use ($c, $approve) {
            $c->update(['status' => $approve ? 'approved' : 'rejected', 'decided_by' => auth()->id(), 'decided_at' => now()]);
            $payroll = false;
            if ($approve) {
                $e = Employee::find($c->employee_id);
                $p = $this->payrollRow($e);
                if ($p && in_array($c->type, ['increment', 'promotion'], true)) {
                    DB::table('employees')->where('id', $p->id)->update(['basic_salary' => $c->new_salary, 'updated_at' => now()]);
                    $payroll = true;
                } elseif ($p && $c->type === 'allowance') {
                    DB::table('employees')->where('id', $p->id)->update(['fixed_allowance' => round($p->fixed_allowance + $c->amount, 2), 'updated_at' => now()]);
                    $payroll = true;
                }
            }
            $this->log('hrms.compensation_decided', $c, ['status' => 'pending'], ['status' => $c->status, 'payroll_updated' => $payroll]);
            $c->setAttribute('payroll_updated', $payroll);

            return $c;
        });
    }

    public function bandPosition(?float $salary, ?SalaryBand $band): ?string
    {
        if ($salary === null || ! $band) {
            return null;
        }

        return $salary < $band->min_salary ? 'below' : ($salary > $band->max_salary ? 'above' : 'within');
    }

    // ---------------------------------------------------------------- HR-011 kemahiran

    /** Matriks: setiap pekerja aktif × kemahiran, dengan jurang ikut keperluan jawatan. */
    public function skillMatrix(): array
    {
        $emps = Employee::whereIn('status', ['active', 'on_leave'])->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'designation_id']);
        $have = EmployeeSkill::get()->groupBy('employee_id');
        $need = DesignationSkill::get()->groupBy('designation_id');
        $rows = [];
        $gaps = 0;
        foreach ($emps as $e) {
            $mine = ($have[$e->id] ?? collect())->keyBy('skill_id');
            $req = ($need[$e->designation_id] ?? collect())->keyBy('skill_id');
            $cells = [];
            foreach ($mine->keys()->merge($req->keys())->unique() as $sid) {
                $lvl = (int) ($mine[$sid]->level ?? 0);
                $r = isset($req[$sid]) ? (int) $req[$sid]->required_level : null;
                $exp = $mine[$sid]->certified_until ?? null;
                $cells[$sid] = ['level' => $lvl, 'required' => $r, 'gap' => $r !== null && $lvl < $r, 'expired' => $exp && $exp->isPast()];
                $gaps += ($r !== null && $lvl < $r) ? 1 : 0;
            }
            $rows[] = ['employee_id' => $e->id, 'name' => trim($e->first_name.' '.$e->last_name), 'skills' => $cells];
        }

        return ['rows' => $rows, 'gaps' => $gaps];
    }

    // ---------------------------------------------------------------- HR-012 suara pekerja

    public function respond(Survey $s, ?Employee $e, array $answers): SurveyResponse
    {
        if ($s->status !== 'open' || ($s->closes_on && $s->closes_on->endOfDay()->isPast())) {
            throw ValidationException::withMessages(['survey' => 'Tinjauan ini sudah ditutup.']);
        }
        $key = $e ? 'e'.$e->id : 'u'.auth()->id();
        $hash = hash('sha256', $s->id.'|'.$key.'|'.config('app.key'));
        if (SurveyResponse::where('survey_id', $s->id)->where('respondent_hash', $hash)->exists()) {
            throw ValidationException::withMessages(['survey' => 'Anda sudah menjawab tinjauan ini.']);
        }
        $clean = [];
        foreach ($s->questions as $q) {
            $v = $answers[$q['id']] ?? null;
            if ($v === null || $v === '') {
                continue;
            }
            $clean[$q['id']] = ($q['type'] ?? 'scale') === 'scale' ? max(1, min(5, (int) $v)) : mb_substr((string) $v, 0, 1000);
        }

        return SurveyResponse::create(['survey_id' => $s->id, 'employee_id' => $s->anonymous ? null : $e?->id, 'respondent_hash' => $hash, 'answers' => $clean]);
    }

    public function surveyResults(Survey $s): array
    {
        $resp = SurveyResponse::where('survey_id', $s->id)->get();
        $n = $resp->count();
        if ($s->anonymous && $n < self::MIN_ANON_RESPONSES) {
            return ['responses' => $n, 'hidden' => true, 'message' => 'Keputusan dipaparkan selepas sekurang-kurangnya '.self::MIN_ANON_RESPONSES.' jawapan, supaya responden tidak dapat dikenal pasti.', 'questions' => []];
        }
        $out = [];
        foreach ($s->questions as $q) {
            $vals = $resp->pluck('answers.'.$q['id'])->filter(fn ($v) => $v !== null && $v !== '');
            if (($q['type'] ?? 'scale') === 'scale') {
                $dist = [];
                for ($i = 1; $i <= 5; $i++) {
                    $dist[$i] = $vals->filter(fn ($v) => (int) $v === $i)->count();
                }
                $out[] = ['id' => $q['id'], 'text' => $q['text'], 'type' => 'scale', 'average' => $vals->count() ? round($vals->avg(), 2) : null, 'count' => $vals->count(), 'distribution' => $dist];
            } else {
                $out[] = ['id' => $q['id'], 'text' => $q['text'], 'type' => 'text', 'count' => $vals->count(), 'answers' => $vals->shuffle()->values()->take(50)];
            }
        }
        $scaleAvgs = collect($out)->where('type', 'scale')->pluck('average')->filter();

        return ['responses' => $n, 'hidden' => false, 'score' => $scaleAvgs->count() ? round($scaleAvgs->avg(), 2) : null, 'questions' => $out];
    }

    // ---------------------------------------------------------------- HR-013 faedah

    public function usedThisYear(int $employeeId, int $planId, int $year, ?int $exceptId = null): float
    {
        return (float) BenefitClaim::where('employee_id', $employeeId)->where('plan_id', $planId)->whereYear('claim_date', $year)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where(fn ($q) => $q->where('status', 'approved')->orWhere('status', 'pending'))
            ->sum(DB::raw('COALESCE(approved_amount, amount)'));
    }

    public function submitClaim(Employee $e, BenefitPlan $p, array $d): BenefitClaim
    {
        if (! $p->is_active) {
            throw ValidationException::withMessages(['plan_id' => 'Pelan faedah ini tidak aktif.']);
        }
        $date = Carbon::parse($d['claim_date']);
        if ($p->eligible_after_days && $e->date_joined && $e->date_joined->copy()->addDays($p->eligible_after_days)->gt($date)) {
            throw ValidationException::withMessages(['plan_id' => "Layak selepas {$p->eligible_after_days} hari bekerja."]);
        }
        if ($p->per_claim_limit && $d['amount'] > $p->per_claim_limit) {
            throw ValidationException::withMessages(['amount' => 'Melebihi had setiap tuntutan RM'.number_format($p->per_claim_limit, 2).'.']);
        }
        $baki = $p->annual_limit - $this->usedThisYear($e->id, $p->id, $date->year);
        if ($d['amount'] > $baki + 0.001) {
            throw ValidationException::withMessages(['amount' => 'Baki had tahunan tinggal RM'.number_format(max(0, $baki), 2).'.']);
        }
        $c = BenefitClaim::create($d + ['employee_id' => $e->id, 'plan_id' => $p->id, 'status' => 'pending']);
        $this->log('hrms.benefit_claimed', $c, null, ['amount' => $c->amount, 'plan' => $p->name]);

        return $c;
    }

    public function decideClaim(BenefitClaim $c, bool $approve, ?float $amount = null, ?string $notes = null): BenefitClaim
    {
        if ($c->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Tuntutan ini sudah diputuskan.']);
        }
        $emp = Employee::find($c->employee_id);
        if ($emp?->user_id && $emp->user_id === auth()->id()) {
            throw ValidationException::withMessages(['status' => 'Anda tidak boleh meluluskan tuntutan anda sendiri.']);
        }
        $amt = $approve ? round($amount ?? $c->amount, 2) : null;
        if ($approve && ($amt <= 0 || $amt > $c->amount)) {
            throw ValidationException::withMessages(['approved_amount' => 'Amaun lulus mesti antara 0 dan amaun dituntut.']);
        }
        $c->update(['status' => $approve ? 'approved' : 'rejected', 'approved_amount' => $amt, 'notes' => $notes ?? $c->notes, 'decided_by' => auth()->id(), 'decided_at' => now()]);
        $this->log('hrms.benefit_decided', $c, ['status' => 'pending'], ['status' => $c->status, 'approved_amount' => $amt]);

        return $c;
    }

    // ---------------------------------------------------------------- HR-014 masuk/keluar

    public function startChecklist(Employee $e, string $type, ?int $templateId = null): int
    {
        if (LifecycleTask::where('employee_id', $e->id)->where('type', $type)->exists()) {
            throw ValidationException::withMessages(['type' => 'Senarai semak ini sudah dimulakan untuk pekerja ini.']);
        }
        $tpl = $templateId ? ChecklistTemplate::where('type', $type)->findOrFail($templateId) : ChecklistTemplate::where('type', $type)->latest('id')->first();
        $items = $tpl?->items ?? ($type === 'onboarding' ? self::DEFAULT_ONBOARDING : self::DEFAULT_OFFBOARDING);
        $base = $type === 'onboarding' ? ($e->date_joined ?? now()) : ($e->last_worked_date ?? now());
        foreach (array_values($items) as $i => $it) {
            LifecycleTask::create(['employee_id' => $e->id, 'type' => $type, 'title' => $it['title'], 'owner' => $it['owner'] ?? null,
                'due_on' => Carbon::parse($base)->addDays((int) ($it['due_days'] ?? 0))->toDateString(), 'sort' => $i]);
        }

        return count($items);
    }

    /** Keluar: tetapkan tarikh akhir, mula senarai semak & cabut akses log masuk pada hari akhir (atau serta-merta jika sudah lepas). */
    public function startOffboarding(Employee $e, array $d): Employee
    {
        if (in_array($e->status, ['terminated', 'resigned'], true)) {
            throw ValidationException::withMessages(['status' => 'Pekerja ini sudah keluar.']);
        }

        return DB::transaction(function () use ($e, $d) {
            $e->update(['last_worked_date' => $d['last_worked_date'], 'termination_reason' => $d['reason'] ?? null]);
            $this->startChecklist($e, 'offboarding');
            if (Carbon::parse($d['last_worked_date'])->endOfDay()->isPast()) {
                $this->finishOffboarding($e, $d['exit_type'] ?? 'resigned');
            } else {
                $e->update(['metadata' => array_merge($e->metadata ?? [], ['exit_type' => $d['exit_type'] ?? 'resigned'])]);
            }
            $this->log('hrms.offboarding_started', $e, null, ['last_worked_date' => $d['last_worked_date']]);

            return $e->fresh();
        });
    }

    public function finishOffboarding(Employee $e, string $type = 'resigned'): void
    {
        $e->update(['status' => $type === 'terminated' ? 'terminated' : 'resigned']);
        if ($e->user_id) {
            // Lumpuhkan keahlian (bukan padam), dan jangan sesekali kunci pemilik syarikat.
            $ctx = app(TenantContext::class);
            $isOwner = DB::table('tenant_users')->join('role_tenant_user', 'role_tenant_user.tenant_user_id', '=', 'tenant_users.id')
                ->join('roles', 'roles.id', '=', 'role_tenant_user.role_id')
                ->where('tenant_users.tenant_id', $ctx->id())->where('tenant_users.user_id', $e->user_id)->where('roles.key', 'tenant_owner')->exists();
            if (! $isOwner) {
                DB::table('tenant_users')->where('tenant_id', $ctx->id())->where('user_id', $e->user_id)->update(['status' => 'disabled', 'updated_at' => now()]);
            }
        }
        $this->log('hrms.access_revoked', $e, null, ['user_id' => $e->user_id]);
    }
}
