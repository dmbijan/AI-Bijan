<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\Applicant;
use App\Modules\HRMS\Models\BenefitClaim;
use App\Modules\HRMS\Models\CompensationChange;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\EmployeeSkill;
use App\Modules\HRMS\Models\Review;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * AI-006 AI CHRO (L1: memerhati & mencadang sahaja).
 * Semua isyarat dikira daripada data sebenar dengan peraturan yang boleh diterangkan.
 * Tiada tindakan disiplin atau keputusan tentang pekerja dibuat secara automatik.
 */
class HrBriefService
{
    /** Akta Kerja 1955: had kerja lebih masa 104 jam sebulan. Amaran pada 80%. */
    public const OT_MONTHLY_LIMIT = 104;

    public function brief(?Carbon $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $emps = Employee::whereIn('status', ['active', 'on_leave'])->get();
        $ids = $emps->pluck('id');
        $name = fn ($id) => ($e = $emps->firstWhere('id', $id)) ? trim($e->first_name.' '.$e->last_name) : '#'.$id;
        $items = [];

        // Pertindihan cuti dalam jabatan yang sama (14 hari akan datang).
        $leave = DB::table('hrms_leave_requests')->whereIn('employee_id', $ids)->whereIn('status', ['approved', 'pending'])
            ->where('end_date', '>=', $today->toDateString())->where('start_date', '<=', $today->copy()->addDays(14)->toDateString())->get();
        $byDept = $emps->groupBy('department_id');
        for ($d = 0; $d <= 14; $d++) {
            $day = $today->copy()->addDays($d)->toDateString();
            $off = $leave->filter(fn ($l) => $l->start_date <= $day && $l->end_date >= $day)->pluck('employee_id')->unique();
            foreach ($byDept as $dept => $group) {
                if (! $dept || $group->count() < 2) {
                    continue;
                }
                $away = $off->intersect($group->pluck('id'));
                if ($away->count() >= 2 && $away->count() / $group->count() >= 0.3) {
                    $key = 'clash-'.$dept;
                    if (! isset($items[$key])) {
                        $items[$key] = ['type' => 'leave_clash', 'severity' => 'warning', 'title' => 'Ramai bercuti serentak',
                            'detail' => $away->count().' daripada '.$group->count().' orang jabatan yang sama bercuti pada '.Carbon::parse($day)->format('d/m').': '.$away->map($name)->join(', ').'.',
                            'action' => 'Semak sebelum lulus cuti baharu', 'tab' => 'leave'];
                    }
                }
            }
        }
        $items = array_values($items);

        // Kerja lebih masa bulan ini.
        $start = $today->copy()->startOfMonth()->toDateString();
        $ot = DB::table('hrms_overtime_requests')->whereIn('employee_id', $ids)->where('status', 'approved')->where('date', '>=', $start)
            ->groupBy('employee_id')->select('employee_id', DB::raw('SUM(hours_requested) as h'))->pluck('h', 'employee_id');
        foreach ($ot as $eid => $h) {
            if ($h >= self::OT_MONTHLY_LIMIT * 0.8) {
                $items[] = ['type' => 'overtime', 'severity' => $h >= self::OT_MONTHLY_LIMIT ? 'danger' : 'warning', 'title' => 'Kerja lebih masa tinggi',
                    'detail' => $name($eid).': '.round($h, 1).' jam bulan ini (had Akta Kerja '.self::OT_MONTHLY_LIMIT.' jam).', 'action' => 'Agih semula syif atau tambah orang', 'tab' => 'attendance'];
            }
        }

        // Lewat / tidak hadir berulang bulan ini (isyarat untuk perbualan, bukan tindakan disiplin).
        $att = DB::table('hrms_attendance')->whereIn('employee_id', $ids)->where('date', '>=', $start)
            ->groupBy('employee_id')->select('employee_id', DB::raw("SUM(CASE WHEN status='late' THEN 1 ELSE 0 END) as late"), DB::raw("SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) as absent"))->get()->keyBy('employee_id');
        foreach ($att as $eid => $a) {
            if ($a->late >= 3 || $a->absent >= 2) {
                $items[] = ['type' => 'attendance', 'severity' => 'info', 'title' => 'Corak kehadiran berubah',
                    'detail' => $name($eid).': lewat '.$a->late.' kali, tidak hadir '.$a->absent.' kali bulan ini.', 'action' => 'Berbual dahulu. AI tidak mengambil tindakan disiplin.', 'tab' => 'attendance'];
            }
        }

        // Kontrak & percubaan.
        foreach ($emps as $e) {
            if ($e->contract_end_date && $e->contract_end_date->between($today, $today->copy()->addDays(30))) {
                $items[] = ['type' => 'contract', 'severity' => 'warning', 'title' => 'Kontrak hampir tamat',
                    'detail' => $name($e->id).': tamat '.$e->contract_end_date->format('d/m/Y').'.', 'action' => 'Putuskan sambung atau tidak', 'tab' => 'employees'];
            }
            if ($e->probation_end_date && $e->probation_end_date->between($today, $today->copy()->addDays(14))) {
                $items[] = ['type' => 'probation', 'severity' => 'info', 'title' => 'Tempoh percubaan hampir tamat',
                    'detail' => $name($e->id).': '.$e->probation_end_date->format('d/m/Y').'.', 'action' => 'Buat penilaian pengesahan jawatan', 'tab' => 'performance'];
            }
        }

        // Sijil kemahiran tamat dalam 30 hari.
        EmployeeSkill::whereIn('employee_id', $ids)->whereNotNull('certified_until')->whereDate('certified_until', '<=', $today->copy()->addDays(30))
            ->get()->each(function ($s) use (&$items, $name, $today) {
                $items[] = ['type' => 'certification', 'severity' => $s->certified_until->lt($today) ? 'danger' : 'warning', 'title' => 'Sijil perlu diperbaharui',
                    'detail' => $name($s->employee_id).': tamat '.$s->certified_until->format('d/m/Y').'.', 'action' => 'Jadualkan latihan semula', 'tab' => 'skills'];
            });

        // Calon tersangkut lebih 7 hari.
        $stuck = Applicant::whereIn('stage', ['applied', 'screening', 'interview', 'offer'])
            ->where(fn ($q) => $q->where('stage_changed_at', '<', $today->copy()->subDays(7))->orWhere(fn ($q) => $q->whereNull('stage_changed_at')->where('created_at', '<', $today->copy()->subDays(7))))->count();
        if ($stuck) {
            $items[] = ['type' => 'hiring', 'severity' => 'info', 'title' => 'Calon menunggu lama', 'detail' => $stuck.' calon tiada pergerakan lebih 7 hari.', 'action' => 'Gerakkan atau tolak dengan baik', 'tab' => 'recruitment'];
        }

        // Risiko berhenti: isyarat ringkas yang boleh diterangkan.
        $risks = $this->attritionSignals($emps, $att, $today);
        foreach (array_slice($risks, 0, 5) as $r) {
            $items[] = ['type' => 'attrition', 'severity' => $r['score'] >= 3 ? 'warning' : 'info', 'title' => 'Isyarat mungkin berhenti',
                'detail' => $name($r['employee_id']).': '.implode('; ', $r['reasons']).'.', 'action' => 'Berbual satu-satu. Ini isyarat, bukan ramalan pasti.', 'tab' => 'performance'];
        }

        $order = ['danger' => 0, 'warning' => 1, 'info' => 2];
        usort($items, fn ($a, $b) => $order[$a['severity']] <=> $order[$b['severity']]);

        $payroll = DB::table('employees')->where('is_active', true)
            ->where('tenant_id', app(TenantContext::class)->id())
            ->selectRaw('COUNT(*) n, COALESCE(SUM(basic_salary + fixed_allowance), 0) total')->first();

        return [
            'generated_at' => now()->toIso8601String(),
            'headcount' => $emps->count(),
            'monthly_salary_cost' => round((float) $payroll->total, 2),
            'payroll_matched' => (int) $payroll->n,
            'pending' => [
                'leave' => $leave->where('status', 'pending')->count(),
                'claims' => BenefitClaim::where('status', 'pending')->count(),
                'compensation' => CompensationChange::where('status', 'pending')->count(),
            ],
            'items' => $items,
            'attrition' => $risks,
            'policy' => 'AI CHRO L1: memerhati & mencadang. Tiada keputusan disiplin, gaji atau pemberhentian dibuat secara automatik.',
        ];
    }

    private function attritionSignals($emps, $att, Carbon $today): array
    {
        $lastRaise = CompensationChange::where('status', 'approved')->whereIn('type', ['increment', 'promotion'])
            ->groupBy('employee_id')->select('employee_id', DB::raw('MAX(effective_on) d'))->pluck('d', 'employee_id');
        $lastRating = Review::where('status', '!=', 'draft')->orderBy('period')->get()->groupBy('employee_id')->map(fn ($g) => $g->last()->rating);
        $out = [];
        foreach ($emps as $e) {
            $s = 0;
            $why = [];
            $tenure = $e->date_joined ? $e->date_joined->diffInMonths($today) : null;
            $raise = isset($lastRaise[$e->id]) ? Carbon::parse($lastRaise[$e->id]) : $e->date_joined;
            if ($tenure !== null && $tenure >= 12 && $raise && $raise->diffInMonths($today) >= 18) {
                $s += 2;
                $why[] = 'tiada kenaikan gaji lebih 18 bulan';
            }
            if (($lastRating[$e->id] ?? null) !== null && $lastRating[$e->id] <= 2) {
                $s++;
                $why[] = 'penilaian terakhir rendah';
            }
            if (isset($att[$e->id]) && ($att[$e->id]->late + $att[$e->id]->absent * 2) >= 4) {
                $s++;
                $why[] = 'kehadiran merosot';
            }
            if ($s >= 2) {
                $out[] = ['employee_id' => $e->id, 'score' => $s, 'reasons' => $why];
            }
        }
        usort($out, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $out;
    }
}
