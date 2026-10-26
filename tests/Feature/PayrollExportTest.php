<?php

namespace Tests\Feature;

use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Eksport statutori payroll (BAB 57). */
class PayrollExportTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private int $runId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('gaji@kedai-a.my', 'Kedai A');
        $this->dalamTenant($this->t);

        $run = PayrollRun::create([
            'name' => 'Gaji Oktober 2026',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'status' => 'draft',
        ]);
        $this->runId = $run->id;

        foreach ([
            ['Nurul binti Ahmad', '900101145678', 3000, 330, 390, 14.75, 51.65, 5.9, 5.9, 0],
            ['Ali bin Abu', '880202145679', 6500, 715, 780, 29.75, 104.15, 11.9, 11.9, 240.50],
        ] as [$nama, $ic, $gaji, $epfEe, $epfEr, $socsoEe, $socsoEr, $eisEe, $eisEr, $pcb]) {
            $e = Employee::create([
                'name' => $nama, 'ic_no' => $ic, 'epf_no' => 'E'.$ic, 'socso_no' => 'S'.$ic,
                'eis_no' => 'I'.$ic, 'tax_no' => 'T'.$ic, 'position' => 'Staf',
                'basic_salary' => $gaji, 'bank_name' => 'Maybank', 'bank_account_no' => '5123'.$ic, 'is_active' => true,
            ]);

            PayrollRunDetail::create([
                'payroll_run_id' => $run->id,
                'employee_id' => $e->id,
                'basic_salary' => $gaji,
                'gross_pay' => $gaji,
                'epf_ee' => $epfEe, 'epf_er' => $epfEr,
                'socso_ee' => $socsoEe, 'socso_er' => $socsoEr,
                'eis_ee' => $eisEe, 'eis_er' => $eisEr,
                'pcb' => $pcb,
                'hrdf' => round($gaji * 0.01, 2),
                'net_pay' => $gaji - $epfEe - $socsoEe - $eisEe - $pcb,
                'statutory_year' => '2026',
            ]);
        }
    }

    private function luluskan(): void
    {
        PayrollRun::find($this->runId)->update(['status' => 'approved']);
    }

    public function test_draft_tidak_boleh_dieksport(): void
    {
        $this->sebagai($this->u, $this->t)
            ->get("/api/v1/payroll/runs/{$this->runId}/export/epf")
            ->assertStatus(422);
    }

    public function test_senarai_jenis_eksport(): void
    {
        $this->luluskan();

        $r = $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/payroll/runs/{$this->runId}/export")
            ->assertOk()
            ->json();

        $this->assertTrue($r['boleh_eksport']);
        $this->assertSame(2, $r['employees']);
        $this->assertContains('epf', $r['jenis']);
        $this->assertContains('pcb', $r['jenis']);
    }

    public function test_eksport_epf_ada_bom_dan_jumlah(): void
    {
        $this->luluskan();

        $res = $this->sebagai($this->u, $this->t)
            ->get("/api/v1/payroll/runs/{$this->runId}/export/epf")
            ->assertOk();

        $body = $res->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body, 'CSV mesti ada BOM UTF-8.');
        $this->assertStringContainsString('NO_KP,NO_EPF,NAMA', $body);
        $this->assertStringContainsString('JUMLAH', $body);
        $this->assertStringContainsString('1045.00', $body, 'Jumlah EPF pekerja 330+715');
        $this->assertStringContainsString('1170.00', $body, 'Jumlah EPF majikan 390+780');
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));
        $this->assertNotEmpty($res->headers->get('X-Checksum-Sha256'));
    }

    public function test_eksport_semua_jenis(): void
    {
        $this->luluskan();

        foreach (['socso', 'eis', 'pcb', 'bank'] as $jenis) {
            $res = $this->sebagai($this->u, $this->t)
                ->get("/api/v1/payroll/runs/{$this->runId}/export/{$jenis}")
                ->assertOk();

            $this->assertStringContainsString('JUMLAH', $res->getContent(), "Fail $jenis mesti ada baris JUMLAH.");
        }
    }

    public function test_eksport_semua_pulangkan_metadata(): void
    {
        $this->luluskan();

        $r = $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/payroll/runs/{$this->runId}/export/semua")
            ->assertOk()
            ->json();

        $jenis = array_column($r['files'], 'jenis');
        $this->assertContains('epf', $jenis);
        $this->assertContains('ringkasan', $jenis);
        $this->assertNotEmpty($r['ringkasan_content']);
    }

    public function test_jenis_tidak_sah_404(): void
    {
        $this->luluskan();

        $this->sebagai($this->u, $this->t)
            ->get("/api/v1/payroll/runs/{$this->runId}/export/dosa")
            ->assertNotFound();
    }

    public function test_staf_tidak_boleh_eksport(): void
    {
        $this->luluskan();
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);

        $this->sebagai($staf, $this->t)
            ->get("/api/v1/payroll/runs/{$this->runId}/export/epf")
            ->assertForbidden();
    }

    public function test_tenant_lain_tidak_boleh_eksport(): void
    {
        $this->luluskan();
        [$ub, $tb] = $this->daftar('gaji@kedai-b.my', 'Kedai B');

        $this->sebagai($ub, $tb)
            ->get("/api/v1/payroll/runs/{$this->runId}/export/epf")
            ->assertNotFound();
    }

    public function test_eksport_direkod_dalam_audit(): void
    {
        $this->luluskan();

        $this->sebagai($this->u, $this->t)->get("/api/v1/payroll/runs/{$this->runId}/export/pcb")->assertOk();

        $this->assertTrue(
            \App\Models\AuditLog::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->t->id)
                ->where('action', 'payroll.export')
                ->exists()
        );
    }
}
