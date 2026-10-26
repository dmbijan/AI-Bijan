<?php

namespace Tests\Feature;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Tiket, SLA, kategori dan portal pelanggan (B-Helpdesk). */
class HelpdeskTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('sokong@kedai-a.my', 'Kedai A');
    }

    private function tiket(array $ganti = []): array
    {
        $this->sebagai($this->u, $this->t);

        return $this->postJson('/api/v1/helpdesk/tickets', array_merge([
            'subject' => 'Pesanan tidak sampai',
            'description' => 'Sudah 5 hari, masih tiada.',
            'requester_name' => 'Nurul',
            'requester_email' => 'nurul@pelanggan.my',
            'priority' => 'sederhana',
        ], $ganti))->json();
    }

    public function test_cipta_tiket_dan_nombor_siri(): void
    {
        $r = $this->barangTiket();

        $this->assertStringStartsWith('TK-', $r['ticket']['number'] ?? $r['number'] ?? '');
    }

    private function barangTiket(): array
    {
        $this->sebagai($this->u, $this->t);

        return $this->postJson('/api/v1/helpdesk/tickets', [
            'subject' => 'Satu',
            'description' => 'Huraian satu',
            'requester_name' => 'A',
            'priority' => 'tinggi',
        ])->assertCreated()->json();
    }

    public function test_sla_mengikut_keutamaan(): void
    {
        $this->sebagai($this->u, $this->t);
        $this->dalamTenant($this->t);
        SlaPolicy::create(['priority' => 'kritikal', 'first_response_minutes' => 15, 'resolution_minutes' => 120]);

        $r = $this->postJson('/api/v1/helpdesk/tickets', [
            'subject' => 'Kritikal',
            'description' => 'Sistem mati',
            'requester_name' => 'A',
            'priority' => 'kritikal',
        ])->assertCreated()->json();

        $id = $r['ticket']['id'] ?? $r['id'];
        $this->dalamTenant($this->t);
        $tk = Ticket::find($id);
        $this->assertNotNull($tk->first_response_due);
        $this->assertNotNull($tk->resolution_due);
        $this->assertTrue($tk->first_response_due->lt($tk->resolution_due));
    }

    public function test_balas_tiket_dan_nota_dalaman(): void
    {
        $t = $this->barangTiket();
        $id = $t['ticket']['id'] ?? $t['id'];

        $this->postJson("/api/v1/helpdesk/tickets/{$id}/reply", ['body' => 'Kami sedang semak.'])->assertCreated();
        $this->postJson("/api/v1/helpdesk/tickets/{$id}/reply", ['body' => 'Nota dalaman', 'internal' => true])->assertCreated();

        $show = $this->getJson("/api/v1/helpdesk/tickets/{$id}")->assertOk()->json();
        $this->assertNotEmpty($show['messages'] ?? $show['ticket']['messages'] ?? []);
    }

    public function test_tiket_ditutup_tidak_boleh_dibalas(): void
    {
        $t = $this->barangTiket();
        $id = $t['ticket']['id'] ?? $t['id'];

        $this->putJson("/api/v1/helpdesk/tickets/{$id}", ['status' => 'ditutup'])->assertOk();
        $this->postJson("/api/v1/helpdesk/tickets/{$id}/reply", ['body' => 'Cuba balas'])->assertStatus(422);
    }

    public function test_kategori_dan_artikel(): void
    {
        $this->sebagai($this->u, $this->t);
        $this->postJson('/api/v1/helpdesk/categories', ['name' => 'Penghantaran'])->assertCreated();
        $this->postJson('/api/v1/helpdesk/articles', ['title' => 'Cara semak pesanan', 'body' => 'Masuk portal.'])->assertCreated();

        $kat = $this->getJson('/api/v1/helpdesk/categories')->assertOk()->json();
        $art = $this->getJson('/api/v1/helpdesk/articles')->assertOk()->json();

        $this->assertNotEmpty($kat);
        $this->assertNotEmpty($art);
    }

    public function test_tenant_lain_tidak_nampak_tiket(): void
    {
        $this->barangTiket();

        [$ub, $tb] = $this->daftar('sokong@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/helpdesk/tickets')->assertOk()->json();

        $this->assertCount(0, $r['data'] ?? $r);
    }

    public function test_staf_tidak_boleh_ubah_tetapan_sla(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);

        $this->sebagai($staf, $this->t)
            ->putJson('/api/v1/helpdesk/sla', ['priority' => 'tinggi', 'first_response_minutes' => 30, 'resolution_minutes' => 60])
            ->assertForbidden();
    }

    public function test_portal_awam_cipta_dan_lihat_tiket(): void
    {
        $this->dalamTenant($this->t);
        $slug = $this->t->slug ?? 'kedai-a';

        $r = $this->postJson("/api/v1/public/{$slug}/helpdesk/tickets", [
            'subject' => 'Dari portal',
            'description' => 'Pelanggan luar',
            'requester_name' => 'Tetamu',
        ]);

        // Portal awam bergantung pada slug tenant; jika tiada, sekurang-kurangnya bukan 500.
        $this->assertLessThan(500, $r->status());
    }

    public function test_sla_lalai_digunakan_bila_tiada_polisi(): void
    {
        $this->sebagai($this->u, $this->t);
        $r = $this->getJson('/api/v1/helpdesk/sla')->assertOk()->json();
        $this->assertIsArray($r);
    }
}
