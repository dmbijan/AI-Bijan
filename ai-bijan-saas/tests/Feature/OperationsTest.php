<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Modules\Helpdesk\HelpdeskService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('a@kedai-a.my', 'Kedai A');
    }

    // ================= B-Projects
    public function test_projek_kebergantungan_kitaran_dan_ringkasan(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);
        $lain = $this->jemput($this->u, $this->t, 'lain@kedai-a.my', ['staff']);
        $p = $this->sebagai($this->u, $this->t)->postJson('/api/v1/projects', ['code' => 'PRJ-1', 'name' => 'Majlis Hotel', 'hourly_rate' => 50, 'budget' => 1000, 'members' => [$staf->id], 'start_date' => '2026-10-01', 'end_date' => '2026-10-31'])->assertCreated()->json('id');
        $a = $this->postJson("/api/v1/projects/$p/tasks", ['title' => 'Reka konsep', 'start_date' => '2026-10-01', 'due_date' => '2026-10-05', 'estimate_hours' => 8])->json('id');
        $b = $this->postJson("/api/v1/projects/$p/tasks", ['title' => 'Pasang pelamin', 'start_date' => '2026-10-06', 'due_date' => '2026-10-10', 'estimate_hours' => 16, 'assignee_id' => $staf->id])->json('id');
        $this->postJson("/api/v1/tasks/$b/dependencies", ['depends_on_id' => $a])->assertOk();
        $this->postJson("/api/v1/tasks/$a/dependencies", ['depends_on_id' => $b])->assertStatus(422); // kitaran
        $this->postJson("/api/v1/tasks/$b/status", ['status' => 'doing'])->assertStatus(422); // A belum siap
        $this->postJson("/api/v1/tasks/$a/status", ['status' => 'done'])->assertOk();
        $this->postJson("/api/v1/tasks/$b/status", ['status' => 'doing'])->assertOk();
        $g = $this->getJson("/api/v1/projects/$p/gantt")->json();
        $this->assertSame([$a], $g[1]['depends_on']);
        $this->assertSame(100, $g[0]['progress']);
        // Ahli lihat & rekod jam; bukan ahli tak nampak projek
        $this->sebagai($staf, $this->t)->getJson("/api/v1/projects/$p")->assertOk();
        $ts = $this->postJson("/api/v1/projects/$p/timesheets", ['work_date' => now()->toDateString(), 'hours' => 6, 'task_id' => $b])->assertCreated()->json('id');
        $this->postJson("/api/v1/projects/$p/timesheets", ['work_date' => now()->toDateString(), 'hours' => 19])->assertStatus(422); // >24 sehari
        $this->postJson("/api/v1/timesheets/$ts/decide", ['decision' => 'diluluskan'])->assertForbidden();
        $this->sebagai($lain, $this->t)->getJson("/api/v1/projects/$p")->assertNotFound();
        $this->assertCount(0, $this->getJson('/api/v1/projects')->json());
        $this->sebagai($this->u, $this->t)->postJson("/api/v1/timesheets/$ts/decide", ['decision' => 'diluluskan'])->assertOk();
        $s = $this->getJson("/api/v1/projects/$p")->json('summary');
        $this->assertSame('6.00', $s['hours']['approved']);
        $this->assertSame('300.00', $s['billable_amount']);
        $this->assertEquals(30.0, $s['budget_used_pct']);
        $this->assertEquals(50.0, $s['tasks']['progress_pct']);
    }

    // ================= B-Assets
    public function test_susut_nilai_garis_lurus_dan_pelupusan(): void
    {
        $this->sebagai($this->u, $this->t);
        $c = $this->postJson('/api/v1/assets/categories', ['name' => 'Komputer', 'useful_life_months' => 36, 'method' => 'garis_lurus'])->json('id');
        $a = $this->postJson('/api/v1/assets', ['tag' => 'AST-001', 'name' => 'Laptop', 'category_id' => $c, 'purchase_date' => '2026-01-15', 'cost' => 3600, 'residual_value' => 0])->assertCreated()->json('id');
        $j = collect($this->getJson("/api/v1/assets/$a")->json('schedule'));
        $this->assertCount(36, $j);
        $this->assertSame('100.00', $j->first()['amount']);
        $this->assertSame('0.00', $j->last()['book_value']);
        $this->assertSame('3600.00', number_format($j->sum('amount'), 2, '.', ''));
        $this->postJson('/api/v1/assets/depreciation/post', ['period' => '2026-06'])->assertOk()->assertJson(['rows' => 6, 'total' => '600.00']);
        $this->assertSame('3000.00', $this->getJson("/api/v1/assets/$a")->json('book_value'));
        // Lupus Ogos: pos Jul–Ogos (2×100) → buku 2800; jual 2500 → rugi 300; jadual depan dibuang
        $d = $this->postJson("/api/v1/assets/$a/dispose", ['date' => '2026-08-20', 'proceeds' => 2500, 'reason' => 'Jual kepada staf'])->assertOk()->json();
        $this->assertSame('-300.00', $d['disposal_gain_loss']);
        $this->assertCount(8, $this->getJson("/api/v1/assets/$a")->json('schedule'));
        $this->postJson("/api/v1/assets/$a/move", ['to_location' => 'Stor'])->assertStatus(422);
    }

    public function test_baki_berkurangan_tidak_bawah_nilai_sisa_dan_penyelenggaraan_berulang(): void
    {
        $this->sebagai($this->u, $this->t);
        $c = $this->postJson('/api/v1/assets/categories', ['name' => 'Kenderaan', 'useful_life_months' => 60, 'method' => 'baki_berkurangan', 'residual_pct' => 10])->json('id');
        $a = $this->postJson('/api/v1/assets', ['tag' => 'VAN-1', 'name' => 'Van penghantaran', 'category_id' => $c, 'purchase_date' => '2026-01-01', 'cost' => 80000])->json('id');
        $j = collect($this->getJson("/api/v1/assets/$a")->json('schedule'));
        $this->assertSame('2666.67', $j->first()['amount']); // 80000 × 2/60
        $this->assertSame('8000.00', $j->last()['book_value']); // nilai sisa 10%
        $this->assertTrue($j->every(fn ($r) => (float) $r['book_value'] >= 8000));
        $m = $this->postJson("/api/v1/assets/$a/maintenance", ['type' => 'pencegahan', 'scheduled_on' => '2026-03-01', 'repeat_months' => 6])->json('id');
        $this->postJson("/api/v1/assets/maintenance/$m/complete", ['done_on' => '2026-03-02', 'cost' => 350])->assertOk();
        $next = collect($this->getJson("/api/v1/assets/$a")->json('maintenances'))->firstWhere('status', 'dijadual');
        $this->assertSame('2026-09-02', $next['scheduled_on']);
    }

    // ================= B-Helpdesk
    public function test_tiket_sla_nota_dalaman_dan_portal_pelanggan(): void
    {
        $slug = $this->t->slug;
        $r = $this->postJson("/api/v1/public/$slug/helpdesk/tickets", ['subject' => 'Bunga layu', 'description' => 'Bunga sampai dalam keadaan layu.', 'requester_name' => 'Puan Ros', 'requester_email' => 'ros@contoh.my', 'consent' => true])->assertCreated()->json();
        $this->postJson("/api/v1/public/$slug/helpdesk/tickets", ['subject' => 'x', 'description' => 'y', 'requester_name' => 'z', 'requester_email' => 'a@b.my'])->assertStatus(422); // tanpa persetujuan PDPA
        $agen = $this->jemput($this->u, $this->t, 'agen@kedai-a.my', ['staff']);
        $id = Ticket::withoutGlobalScopes()->where('number', $r['number'])->value('id');
        $this->sebagai($agen, $this->t)->postJson("/api/v1/helpdesk/tickets/$id/reply", ['body' => 'Pelanggan ini pernah komplen sebelum ini.', 'internal' => true])->assertCreated();
        $this->postJson("/api/v1/helpdesk/tickets/$id/reply", ['body' => 'Maaf puan, kami ganti hari ini.'])->assertCreated();
        $this->putJson("/api/v1/helpdesk/tickets/$id", ['assignee_id' => $agen->id])->assertForbidden(); // agen tak boleh tugas semula
        $tk = $this->getJson("/api/v1/helpdesk/tickets/$id")->json('ticket');
        $this->assertNotNull($tk['first_responded_at']);
        $this->assertFalse($tk['response_breached']);
        $this->assertSame('menunggu_pelanggan', $tk['status']);
        // Portal: nampak balasan awam sahaja
        $this->flushSession();
        $p = $this->getJson("/api/v1/public/$slug/helpdesk/tickets/{$r['token']}")->assertOk();
        $this->assertStringNotContainsString('pernah komplen', $p->getContent());
        $this->assertStringContainsString('kami ganti', $p->getContent());
        $this->postJson("/api/v1/public/$slug/helpdesk/tickets/{$r['token']}/reply", ['body' => 'Terima kasih'])->assertCreated();
        $this->getJson("/api/v1/public/$slug/helpdesk/tickets/".str_repeat('x', 48))->assertNotFound();
        $this->assertSame('dibuka', Ticket::withoutGlobalScopes()->find($id)->status);
    }

    public function test_langgar_sla_dieskalasi_dan_tenant_lain_tak_nampak(): void
    {
        $this->sebagai($this->u, $this->t);
        $id = $this->postJson('/api/v1/helpdesk/tickets', ['subject' => 'Sistem POS rosak', 'description' => 'Tak boleh cetak resit', 'requester_name' => 'Cawangan Kajang', 'priority' => 'kritikal', 'channel' => 'dalaman'])->assertCreated()->json('ticket.id');
        $tk = $this->getJson("/api/v1/helpdesk/tickets/$id")->json('ticket');
        $this->assertEqualsWithDelta(30 * 60, strtotime($tk['first_response_due']) - strtotime($tk['created_at']), 5);
        $this->travel(31)->minutes();
        app(TenantContext::class)->set($this->t);
        $this->assertSame(1, app(HelpdeskService::class)->checkSla());
        $this->assertSame(0, app(HelpdeskService::class)->checkSla()); // tidak berganda
        $x = Ticket::find($id);
        $this->assertTrue($x->response_breached);
        $this->assertSame(1, $x->escalation_level);
        $this->travelBack();
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $this->sebagai($ub, $tb)->getJson("/api/v1/helpdesk/tickets/$id")->assertNotFound();
        $this->getJson('/api/v1/assets')->assertOk()->assertJsonCount(0);
        $this->getJson('/api/v1/projects')->assertOk()->assertJsonCount(0);
    }
}
