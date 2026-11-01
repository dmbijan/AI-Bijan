<?php

namespace Tests\Feature;

use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Kelompok 2 HR: HR-004/009/010/011/012/013/014 + AI-006 AI CHRO. */
class HrTalentTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('hr@bunga.my', 'Kedai Bunga HR');
        $this->sebagai($this->u, $this->t);
    }

    private function pekerja(string $nama, array $x = []): int
    {
        $this->dalamTenant($this->t);

        return Employee::create($x + ['employee_no' => Employee::generateEmployeeNo(), 'first_name' => $nama, 'email' => strtolower($nama).'@bunga.my', 'status' => 'active', 'date_joined' => '2024-01-01'])->id;
    }

    #[Test]
    public function calon_bergerak_ikut_peringkat_dan_diambil_jadi_pekerja_dengan_senarai_semak(): void
    {
        $v = $this->postJson('/api/v1/hrms/recruitment/vacancies', ['title' => 'Florist', 'openings' => 1])->assertCreated()->json('data.id');
        $a = $this->postJson('/api/v1/hrms/recruitment/applicants', ['vacancy_id' => $v, 'name' => 'Siti Aminah', 'email' => 'siti@contoh.my', 'source' => 'Jobstreet'])->assertCreated()->json('data.id');
        $this->postJson("/api/v1/hrms/recruitment/applicants/{$a}/move", ['stage' => 'interview', 'score' => 4])->assertOk();
        $this->postJson("/api/v1/hrms/recruitment/applicants/{$a}/move", ['stage' => 'hired'])->assertStatus(422);
        $emp = $this->postJson("/api/v1/hrms/recruitment/applicants/{$a}/hire", ['date_joined' => now()->toDateString()])->assertCreated()->json('data');
        $this->assertSame('Siti', $emp['first_name']);

        $p = $this->getJson('/api/v1/hrms/recruitment/applicants?vacancy_id='.$v)->json('data.pipeline');
        $this->assertSame(1, $p['hired']);
        $this->assertSame('closed', collect($this->getJson('/api/v1/hrms/recruitment/vacancies')->json('data'))->firstWhere('id', $v)['status']);

        $g = collect($this->getJson('/api/v1/hrms/lifecycle?type=onboarding')->json('data.groups'))->firstWhere('employee_id', $emp['id']);
        $this->assertSame(7, $g['total']);
        $this->postJson('/api/v1/hrms/lifecycle/tasks/'.$g['tasks'][0]['id'].'/toggle')->assertOk();
        $this->assertSame(1, collect($this->getJson('/api/v1/hrms/lifecycle?type=onboarding')->json('data.groups'))->firstWhere('employee_id', $emp['id'])['done']);
        $this->postJson("/api/v1/hrms/recruitment/applicants/{$a}/hire")->assertStatus(422);
    }

    #[Test]
    public function skor_sasaran_berwajaran_dan_penilaian(): void
    {
        $e = $this->pekerja('Ali');
        $this->postJson('/api/v1/hrms/performance/goals', ['employee_id' => $e, 'period' => '2026-H2', 'title' => 'Jualan', 'target' => 10000, 'actual' => 8000, 'weight' => 60])->assertCreated();
        $this->postJson('/api/v1/hrms/performance/goals', ['employee_id' => $e, 'period' => '2026-H2', 'title' => 'Aduan sifar', 'target' => 1, 'actual' => 2, 'weight' => 40])->assertCreated();
        // 0.8×60 + min(2,1.2)×40 = 48 + 48 = 96
        $this->postJson('/api/v1/hrms/performance/reviews', ['employee_id' => $e, 'period' => '2026-H2', 'submit' => true])->assertStatus(422);
        $r = $this->postJson('/api/v1/hrms/performance/reviews', ['employee_id' => $e, 'period' => '2026-H2', 'rating' => 4, 'strengths' => 'Rajin', 'submit' => true])->assertOk()->json('data');
        $this->assertEquals(96.0, $r['goal_score']);
        $this->assertSame('submitted', $r['status']);
        $this->postJson('/api/v1/hrms/performance/reviews', ['employee_id' => $e, 'period' => '2026-H2', 'rating' => 5])->assertStatus(422);
    }

    #[Test]
    public function kenaikan_gaji_perlu_pelulus_lain_dan_tulis_ke_payroll(): void
    {
        $e = $this->pekerja('Abu', ['ic_number' => '900101-10-1234']);
        DB::table('employees')->insert(['tenant_id' => $this->t->id, 'name' => 'Abu', 'ic_no' => '900101-10-1234', 'position' => 'Florist', 'basic_salary' => 2000, 'created_at' => now(), 'updated_at' => now()]);
        $this->postJson('/api/v1/hrms/compensation/bands', ['grade' => 'G1', 'name' => 'Florist', 'min_salary' => 1700, 'max_salary' => 2500])->assertCreated();
        $this->postJson("/api/v1/hrms/compensation/employees/{$e}/grade", ['grade' => 'G1'])->assertOk();

        $c = $this->postJson('/api/v1/hrms/compensation/changes', ['employee_id' => $e, 'type' => 'increment', 'new_salary' => 2200, 'effective_on' => '2026-12-01'])->assertCreated()->json('data');
        $this->assertEquals(200, $c['amount']);
        // Pemohon tidak boleh lulus sendiri.
        $this->postJson("/api/v1/hrms/compensation/changes/{$c['id']}/decide", ['approve' => true])->assertStatus(422);

        $hr = $this->jemput($this->u, $this->t, 'pengurus.hr@bunga.my', ['hr_manager']);
        $this->sebagai($hr, $this->t)->postJson("/api/v1/hrms/compensation/changes/{$c['id']}/decide", ['approve' => true])->assertOk()->assertJsonPath('data.status', 'approved');
        $this->assertEquals(2200, DB::table('employees')->where('ic_no', '900101-10-1234')->value('basic_salary'));
        $staff = collect($this->getJson('/api/v1/hrms/compensation')->json('data.staff'))->firstWhere('employee_id', $e);
        $this->assertSame('within', $staff['position']);
    }

    #[Test]
    public function staf_biasa_tidak_nampak_gaji(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@bunga.my', ['staff']);
        $this->sebagai($staf, $this->t)->getJson('/api/v1/hrms/compensation')->assertForbidden();
    }

    #[Test]
    public function matriks_kemahiran_tunjuk_jurang(): void
    {
        $this->dalamTenant($this->t);
        $des = DB::table('hrms_designations')->insertGetId(['tenant_id' => $this->t->id, 'name' => 'Florist', 'created_at' => now(), 'updated_at' => now()]);
        $e = $this->pekerja('Mei', ['designation_id' => $des]);
        $s = $this->postJson('/api/v1/hrms/skills', ['name' => 'Gubahan bunga', 'category' => 'Teknikal'])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/hrms/skills/require', ['designation_id' => $des, 'skill_id' => $s, 'required_level' => 3])->assertOk();
        $this->postJson('/api/v1/hrms/skills/rate', ['employee_id' => $e, 'skill_id' => $s, 'level' => 1])->assertOk();
        $m = $this->getJson('/api/v1/hrms/skills')->assertOk()->json('data');
        $this->assertSame(1, $m['gaps']);
        $this->assertTrue($m['rows'][0]['skills'][$s]['gap']);
    }

    #[Test]
    public function survey_tanpa_nama_sorok_keputusan_bila_kurang_tiga_jawapan(): void
    {
        $sv = $this->postJson('/api/v1/hrms/engagement/surveys', ['title' => 'Pulse Nov', 'questions' => [['text' => 'Saya gembira bekerja di sini'], ['text' => 'Cadangan', 'type' => 'text']]])->assertCreated()->json('data');
        $q = $sv['questions'][0]['id'];
        $this->postJson("/api/v1/hrms/engagement/surveys/{$sv['id']}/respond", ['answers' => [$q => 4]])->assertCreated();
        $this->postJson("/api/v1/hrms/engagement/surveys/{$sv['id']}/respond", ['answers' => [$q => 5]])->assertStatus(422);
        $this->assertTrue($this->getJson("/api/v1/hrms/engagement/surveys/{$sv['id']}/results")->json('data.hidden'));

        foreach (['s1@bunga.my', 's2@bunga.my'] as $em) {
            $x = $this->jemput($this->u, $this->t, $em, ['hr_manager']);
            $this->sebagai($x, $this->t)->postJson("/api/v1/hrms/engagement/surveys/{$sv['id']}/respond", ['answers' => [$q => 2]])->assertCreated();
        }
        $res = $this->sebagai($this->u, $this->t)->getJson("/api/v1/hrms/engagement/surveys/{$sv['id']}/results")->json('data');
        $this->assertFalse($res['hidden']);
        $this->assertEquals(2.67, $res['questions'][0]['average']);
        $this->assertSame(0, DB::table('hrms_survey_responses')->whereNotNull('employee_id')->count());
    }

    #[Test]
    public function tuntutan_faedah_ikut_had_tahunan(): void
    {
        $e = $this->pekerja('Lina');
        $p = $this->postJson('/api/v1/hrms/benefits/plans', ['name' => 'Klinik panel', 'type' => 'medical', 'annual_limit' => 500, 'per_claim_limit' => 300])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/hrms/benefits/claims', ['employee_id' => $e, 'plan_id' => $p, 'claim_date' => now()->toDateString(), 'amount' => 350])->assertStatus(422);
        $c = $this->postJson('/api/v1/hrms/benefits/claims', ['employee_id' => $e, 'plan_id' => $p, 'claim_date' => now()->toDateString(), 'amount' => 280])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/hrms/benefits/claims', ['employee_id' => $e, 'plan_id' => $p, 'claim_date' => now()->toDateString(), 'amount' => 250])->assertStatus(422);
        $this->postJson("/api/v1/hrms/benefits/claims/{$c}/decide", ['approve' => true, 'approved_amount' => 200])->assertOk();
        $this->postJson('/api/v1/hrms/benefits/claims', ['employee_id' => $e, 'plan_id' => $p, 'claim_date' => now()->toDateString(), 'amount' => 300])->assertCreated();
        $this->postJson("/api/v1/hrms/benefits/claims/{$c}/decide", ['approve' => false])->assertStatus(422);
    }

    #[Test]
    public function keluar_kerja_lumpuhkan_akses_tapi_bukan_pemilik(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'keluar@bunga.my', ['staff']);
        $this->sebagai($this->u, $this->t);
        $e = $this->pekerja('Keluar', ['user_id' => $staf->id, 'email' => 'keluar.hr@bunga.my']);
        $this->postJson("/api/v1/hrms/lifecycle/{$e}/offboarding", ['last_worked_date' => now()->subDay()->toDateString(), 'exit_type' => 'resigned'])->assertOk()->assertJsonPath('data.status', 'resigned');
        $this->assertSame('disabled', DB::table('tenant_users')->where('tenant_id', $this->t->id)->where('user_id', $staf->id)->value('status'));
        $this->postJson("/api/v1/hrms/lifecycle/{$e}/exit-interview", ['reason' => 'kerjaya', 'would_rehire' => true])->assertCreated();

        $own = $this->pekerja('Bos', ['user_id' => $this->u->id, 'email' => 'bos.hr@bunga.my']);
        $this->postJson("/api/v1/hrms/lifecycle/{$own}/offboarding", ['last_worked_date' => now()->toDateString()])->assertStatus(422);
    }

    #[Test]
    public function ai_chro_kesan_cuti_bertindih_dan_lebih_masa(): void
    {
        $this->dalamTenant($this->t);
        $d = Department::create(['name' => 'Kedai', 'code' => 'KD']);
        $a = $this->pekerja('Aina', ['department_id' => $d->id]);
        $b = $this->pekerja('Badrul', ['department_id' => $d->id]);
        $this->pekerja('Chong', ['department_id' => $d->id]);
        $lt = DB::table('hrms_leave_types')->insertGetId(['tenant_id' => $this->t->id, 'name' => 'Tahunan', 'code' => 'AL', 'created_at' => now(), 'updated_at' => now()]);
        foreach ([$a, $b] as $e) {
            DB::table('hrms_leave_requests')->insert(['employee_id' => $e, 'leave_type_id' => $lt, 'start_date' => now()->addDays(3)->toDateString(), 'end_date' => now()->addDays(4)->toDateString(), 'days_requested' => 2, 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('hrms_overtime_requests')->insert(['employee_id' => $a, 'date' => now()->startOfMonth()->toDateString(), 'start_time' => '18:00', 'end_time' => '23:00', 'hours_requested' => 90, 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()]);

        $br = $this->getJson('/api/v1/hrms/talent/brief')->assertOk()->json('data');
        $types = collect($br['items'])->pluck('type');
        $this->assertContains('leave_clash', $types);
        $this->assertContains('overtime', $types);
        $this->assertSame(3, $br['headcount']);
    }

    #[Test]
    public function data_bakat_terasing_antara_syarikat(): void
    {
        $this->postJson('/api/v1/hrms/recruitment/vacancies', ['title' => 'Rahsia A'])->assertCreated();
        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $this->sebagai($u2, $t2);
        $this->assertCount(0, $this->getJson('/api/v1/hrms/recruitment/vacancies')->json('data'));
        $e = $this->pekerja('Orang A');
        $this->sebagai($u2, $t2)->postJson('/api/v1/hrms/performance/goals', ['employee_id' => $e, 'period' => '2026', 'title' => 'x'])->assertStatus(422);
    }
}
