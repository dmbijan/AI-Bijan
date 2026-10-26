<?php

namespace Tests\Feature;

use App\Modules\ServiceOS\Models\ServiceAssignment;
use App\Modules\ServiceOS\Models\ServiceClient;
use App\Modules\ServiceOS\Models\ServiceContract;
use App\Modules\ServiceOS\Models\ServiceCrew;
use App\Modules\ServiceOS\Models\ServiceDelivery;
use App\Modules\ServiceOS\Models\ServiceDomainEvent;
use App\Modules\ServiceOS\Models\ServiceEquipment;
use App\Modules\ServiceOS\Models\ServicePackage;
use App\Modules\ServiceOS\Models\ServiceProject;
use App\Modules\ServiceOS\Models\ServiceQuotation;
use App\Modules\ServiceOS\Models\ServiceSession;
use App\Modules\ServiceOS\Models\ServiceTask;
use App\Modules\ServiceOS\Services\ServiceOSService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-ServiceOS — Service Business Operating System (BAB 87 P1).
 *
 * Liputan: pelanggan, pakej, projek, peralihan status, sesi, sebut harga,
 * kontrak, kru + konflik, tugasan + checklist, peralatan, penyerahan,
 * peristiwa domain (outbox) dan pengasingan tenant.
 */
class ServiceOSTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private ServiceOSService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@serviceos.my', 'ServiceOS Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(ServiceOSService::class);
    }

    // ===================================================================
    // PELANGGAN
    // ===================================================================

    public function test_cipta_pelanggan(): void
    {
        $c = $this->s->ciptaPelanggan($this->t->id, [
            'display_name' => 'Aminah binti Ali',
            'email' => 'aminah@contoh.my',
            'phone' => '0123456789',
        ]);

        $this->assertSame('Aminah binti Ali', $c->display_name);
        $this->assertSame(ServiceClient::TIER_REGULAR, $c->tier);
        $this->assertTrue($c->is_active);
        $this->assertSame('MY', $c->country);
    }

    public function test_cipta_pelanggan_idempoten_mengikut_emel(): void
    {
        $a = $this->s->ciptaPelanggan($this->t->id, [
            'display_name' => 'Siti',
            'email' => 'SITI@Contoh.MY',
        ]);
        $b = $this->s->ciptaPelanggan($this->t->id, [
            'display_name' => 'Siti (nama lain)',
            'email' => 'siti@contoh.my',
        ]);

        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, ServiceClient::where('tenant_id', $this->t->id)->count());
    }

    public function test_pelanggan_tanpa_emel_boleh_dicipta_berulang(): void
    {
        $a = $this->s->ciptaPelanggan($this->t->id, ['display_name' => 'Tanpa Emel 1']);
        $b = $this->s->ciptaPelanggan($this->t->id, ['display_name' => 'Tanpa Emel 2']);

        $this->assertNotSame($a->id, $b->id);
    }

    public function test_kira_semula_nilai_pelanggan(): void
    {
        $c = $this->s->ciptaPelanggan($this->t->id, ['display_name' => 'Tetap']);

        $p1 = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Projek 1',
            'client_id' => $c->id,
            'contract_value' => 5000,
        ]);
        $this->s->tukarStatus($this->t->id, $p1->id, ServiceProject::STATUS_QUOTED);
        $this->s->tukarStatus($this->t->id, $p1->id, ServiceProject::STATUS_CONFIRMED);
        $this->s->tukarStatus($this->t->id, $p1->id, ServiceProject::STATUS_IN_PROGRESS);
        $this->s->tukarStatus($this->t->id, $p1->id, ServiceProject::STATUS_DELIVERED);
        $this->s->tukarStatus($this->t->id, $p1->id, ServiceProject::STATUS_COMPLETED);

        $this->s->ciptaProjek($this->t->id, [
            'title' => 'Projek 2',
            'client_id' => $c->id,
            'contract_value' => 3000,
        ]);

        $fresh = $this->s->kiraSemulaNilaiPelanggan($this->t->id, $c->id);

        $this->assertSame(2, $fresh->total_projects);
        $this->assertSame('5000.00', number_format((float) $fresh->lifetime_value, 2, '.', ''));
    }

    // ===================================================================
    // PAKEJ
    // ===================================================================

    public function test_cipta_pakej_dan_kira_deposit(): void
    {
        $p = $this->s->ciptaPakej($this->t->id, [
            'code' => 'PKG-FOTO',
            'name' => 'Pakej Fotografi Penuh',
            'base_price' => 5200,
            'deposit_percent' => 30,
            'duration_minutes' => 480,
            'buffer_minutes' => 60,
        ]);

        $this->assertSame('1560.00', number_format($p->kiraDeposit(), 2, '.', ''));
        $this->assertSame(540, $p->jumlahDurasi());
        $this->assertSame('MYR', $p->currency);
    }

    public function test_pakej_tanpa_deposit_kembalikan_sifar(): void
    {
        $p = $this->s->ciptaPakej($this->t->id, [
            'code' => 'PKG-ND',
            'name' => 'Tanpa Deposit',
            'base_price' => 1000,
            'requires_deposit' => false,
        ]);

        $this->assertSame('0.00', number_format($p->kiraDeposit(), 2, '.', ''));
    }

    public function test_katalog_pakej_dikumpul_ikut_kategori(): void
    {
        $this->s->ciptaPakej($this->t->id, ['code' => 'A1', 'name' => 'A1', 'category' => 'foto']);
        $this->s->ciptaPakej($this->t->id, ['code' => 'A2', 'name' => 'A2', 'category' => 'foto']);
        $this->s->ciptaPakej($this->t->id, ['code' => 'B1', 'name' => 'B1', 'category' => 'video']);
        $this->s->ciptaPakej($this->t->id, ['code' => 'X1', 'name' => 'X1', 'is_active' => false]);

        $katalog = $this->s->katalogPakej($this->t->id);

        $this->assertArrayHasKey('foto', $katalog);
        $this->assertCount(2, $katalog['foto']);
        $this->assertCount(1, $katalog['video']);
        $this->assertArrayNotHasKey('umum', $katalog);
    }

    // ===================================================================
    // PROJEK
    // ===================================================================

    public function test_cipta_projek_jana_nombor_dan_peristiwa(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Majlis Perkahwinan',
            'industry' => 'wedding',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
        ]);

        $this->assertStringStartsWith('SVP-' . now()->format('Y') . '-', $p->project_no);
        $this->assertSame(ServiceProject::STATUS_DRAFT, $p->status);
        $this->assertSame('MYR', $p->currency);

        $this->assertSame(1, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('event_name', ServiceDomainEvent::LEAD_QUALIFIED)->count());
    }

    public function test_cipta_projek_dengan_pelanggan_luar_tenant_gagal(): void
    {
        [, $t2] = $this->daftar('lain@serviceos.my', 'Tenant Lain Sdn Bhd');
        $this->dalamTenant($t2);
        $c2 = $this->s->ciptaPelanggan($t2->id, ['display_name' => 'Pelanggan Tenant 2']);

        $this->dalamTenant($this->t);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->s->ciptaProjek($this->t->id, [
            'title' => 'Cuba Curi',
            'client_id' => $c2->id,
        ]);
    }

    public function test_aliran_status_penuh(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Aliran Penuh']);

        foreach ([
            ServiceProject::STATUS_QUOTED,
            ServiceProject::STATUS_CONFIRMED,
            ServiceProject::STATUS_IN_PROGRESS,
            ServiceProject::STATUS_DELIVERED,
            ServiceProject::STATUS_COMPLETED,
        ] as $status) {
            $p = $this->s->tukarStatus($this->t->id, $p->id, $status);
            $this->assertSame($status, $p->status);
        }

        $this->assertNotNull($p->completed_at);
        $this->assertSame(100, $p->progress_percent);
    }

    public function test_peralihan_status_tidak_sah_ditolak(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Tak Sah']);

        $this->expectException(\RuntimeException::class);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_COMPLETED);
    }

    public function test_status_sama_tidak_ubah_apa_apa(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Sama']);

        $selepas = $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_DRAFT);

        $this->assertSame($p->status, $selepas->status);
        $this->assertSame(
            1,
            ServiceDomainEvent::where('tenant_id', $this->t->id)
                ->where('event_name', ServiceDomainEvent::LEAD_QUALIFIED)->count()
        );
    }

    public function test_batal_projek_rekod_sebab(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Batal']);

        $p = $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_CANCELLED, 'Pelanggan tarik diri');

        $this->assertSame(ServiceProject::STATUS_CANCELLED, $p->status);
        $this->assertNotNull($p->cancelled_at);
        $this->assertSame('Pelanggan tarik diri', $p->cancellation_reason);
    }

    public function test_kira_margin_projek(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Margin']);

        $p->update(['contract_value' => 5200, 'actual_cost' => 3120]);

        $p = $this->s->kiraMargin($this->t->id, $p->id);

        $this->assertSame('2080.00', number_format((float) $p->margin_amount, 2, '.', ''));
        $this->assertSame('40.00', number_format((float) $p->margin_percent, 2, '.', ''));
    }

    public function test_kira_margin_sifar_kontrak(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Margin Sifar']);

        $p = $this->s->kiraMargin($this->t->id, $p->id);

        $this->assertSame('0.00', number_format((float) $p->margin_percent, 2, '.', ''));
    }

    public function test_semak_konflik_jadual_projek(): void
    {
        $a = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Projek A',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
        ]);

        $this->s->ciptaProjek($this->t->id, [
            'title' => 'Projek B',
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-08',
        ]);

        $this->s->ciptaProjek($this->t->id, [
            'title' => 'Projek C (tiada konflik)',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);

        $konflik = $this->s->semakKonflik($this->t->id, $a->id);

        $this->assertCount(1, $konflik);
        $this->assertSame('Projek B', $konflik[0]['title']);

        $this->assertSame(1, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('event_name', ServiceDomainEvent::SCHEDULE_CONFLICT_DETECTED)->count());
    }

    public function test_konflik_tidak_termasuk_projek_selesai(): void
    {
        $a = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Aktif',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
        ]);

        $b = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Selesai',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-03',
        ]);

        $b->update(['status' => ServiceProject::STATUS_COMPLETED]);

        $this->assertCount(0, $this->s->semakKonflik($this->t->id, $a->id));
    }

    public function test_projek_tanpa_tarikh_tiada_konflik(): void
    {
        $a = $this->s->ciptaProjek($this->t->id, ['title' => 'Tanpa Tarikh']);

        $this->assertSame([], $this->s->semakKonflik($this->t->id, $a->id));
    }

    // ===================================================================
    // SESI
    // ===================================================================

    public function test_cipta_sesi_ikut_urutan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Ada Sesi']);

        $s1 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 1',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
        ]);
        $s2 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 2',
            'start_at' => '2026-11-02 09:00:00',
            'end_at' => '2026-11-02 13:00:00',
        ]);

        $this->assertSame(1, $s1->sequence);
        $this->assertSame(2, $s2->sequence);
        $this->assertSame(ServiceSession::STATUS_PLANNED, $s1->status);
        $this->assertSame(480, $s1->durasiMinit());
    }

    public function test_had_sesi_per_projek(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Had Sesi']);

        // isi terus ke had tanpa loop melalui servis
        for ($i = 1; $i <= ServiceOSService::MAX_SESSIONS_PER_PROJECT; $i++) {
            ServiceSession::create([
                'tenant_id' => $this->t->id,
                'service_project_id' => $p->id,
                'name' => "Sesi {$i}",
                'start_at' => '2026-11-01 09:00:00',
                'end_at' => '2026-11-01 10:00:00',
                'sequence' => $i,
            ]);
        }

        $this->expectException(\RuntimeException::class);
        $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Terlebih',
            'start_at' => '2026-11-05 09:00:00',
            'end_at' => '2026-11-05 10:00:00',
        ]);
    }

    public function test_konflik_kru_sesi(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Konflik Kru']);

        $s1 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 1',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 12:00:00',
        ]);
        $s2 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 2',
            'start_at' => '2026-11-01 11:00:00',
            'end_at' => '2026-11-01 14:00:00',
        ]);

        $kru = $this->s->ciptaKru($this->t->id, ['name' => 'Kru A', 'day_rate' => 200]);
        $kru2 = $this->s->ciptaKru($this->t->id, ['name' => 'Kru B', 'day_rate' => 180]);

        // Kru A pada sesi 1 sahaja; Kru B pada sesi 2 sahaja → tiada konflik.
        $this->s->tugsakanKru($this->t->id, $p->id, $kru->id, ['service_session_id' => $s1->id]);
        $this->s->tugsakanKru($this->t->id, $p->id, $kru2->id, ['service_session_id' => $s2->id]);

        $this->assertCount(0, $this->s->konflikKruSesi($this->t->id, $s1->id));
        $this->assertCount(0, $this->s->konflikKruSesi($this->t->id, $s2->id));

        // Cuba tugaskan Kru A ke sesi 2 → ditolak oleh semakan masa
        $this->expectException(\RuntimeException::class);
        $this->s->tugsakanKru($this->t->id, $p->id, $kru->id, [
            'service_session_id' => $s2->id,
            'role' => 'assistant',
        ]);
    }

    public function test_konflik_kru_sesi_dikesan_apabila_ditugaskan_paksa(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Konflik Paksa']);

        $s1 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 1',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 12:00:00',
        ]);
        $s2 = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi 2',
            'start_at' => '2026-11-01 11:00:00',
            'end_at' => '2026-11-01 14:00:00',
        ]);

        $kru = $this->s->ciptaKru($this->t->id, ['name' => 'Kru A']);

        $this->s->tugsakanKru($this->t->id, $p->id, $kru->id, ['service_session_id' => $s1->id]);

        // Paksa cipta penugasan bertindih secara terus (memintas servis)
        ServiceAssignment::create([
            'tenant_id' => $this->t->id,
            'service_project_id' => $p->id,
            'service_session_id' => $s2->id,
            'crew_id' => $kru->id,
            'role' => 'lead',
            'status' => ServiceAssignment::STATUS_ASSIGNED,
            'start_at' => '2026-11-01 11:00:00',
            'end_at' => '2026-11-01 14:00:00',
        ]);

        $konflik = $this->s->konflikKruSesi($this->t->id, $s1->id);

        $this->assertCount(1, $konflik);
        $this->assertSame($kru->id, $konflik[0]['crew_id']);
    }

    // ===================================================================
    // SEBUT HARGA
    // ===================================================================

    public function test_cipta_sebut_harga_kira_jumlah(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Sebut Harga']);

        $q = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [
                ['description' => 'Fotografi', 'qty' => 1, 'unit_price' => 4000],
                ['description' => 'Album', 'qty' => 2, 'unit_price' => 600],
            ],
            'discount_amount' => 200,
            'deposit_amount' => 1500,
            'tax_rate' => 0.06,
        ]);

        $this->assertSame('5200.00', number_format((float) $q->subtotal, 2, '.', ''));
        // subtotal 5200 − diskaun 200 = 5000; + cukai 6% (300) = 5300
        $this->assertSame('300.00', number_format((float) $q->tax_amount, 2, '.', ''));
        $this->assertSame('5300.00', number_format((float) $q->total_amount, 2, '.', ''));
        $this->assertSame('3800.00', number_format((float) $q->balance_amount, 2, '.', ''));
        $this->assertSame(1, $q->version);
        $this->assertTrue($q->is_current);
        $this->assertStringContainsString('V1', $q->quote_no);
    }

    public function test_sebut_harga_versi_baru_nyahaktif_versi_lama(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Versi']);

        $q1 = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'A', 'qty' => 1, 'unit_price' => 1000]],
        ]);
        $q2 = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'B', 'qty' => 1, 'unit_price' => 2000]],
        ]);

        $this->assertSame(1, $q1->version);
        $this->assertSame(2, $q2->version);
        $this->assertFalse($q1->fresh()->is_current);
        $this->assertTrue($q2->is_current);
        $this->assertSame(1, ServiceQuotation::where('tenant_id', $this->t->id)
            ->where('service_project_id', $p->id)->where('is_current', true)->count());
    }

    public function test_terima_sebut_harga_cipta_kontrak_dan_konfirmasi_projek(): void
    {
        $c = $this->s->ciptaPelanggan($this->t->id, ['display_name' => 'Klien']);
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Terima', 'client_id' => $c->id]);

        $q = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'Pakej', 'qty' => 1, 'unit_price' => 5200]],
        ]);

        $kontrak = $this->s->terimaSebutHarga($this->t->id, $q->id, [
            'name' => 'Aminah',
            'email' => 'aminah@contoh.my',
            'ip' => '203.0.113.10',
        ]);

        $this->assertSame(ServiceContract::STATUS_ACCEPTED, $kontrak->status);
        $this->assertSame('5200.00', number_format((float) $kontrak->contract_value, 2, '.', ''));
        $this->assertSame('Aminah', $kontrak->accepted_by_name);
        $this->assertNotNull($kontrak->accepted_at);
        $this->assertTrue($kontrak->mengikat());

        $this->assertSame(ServiceQuotation::STATUS_ACCEPTED, $q->fresh()->status);

        $projek = $p->fresh();
        $this->assertSame(ServiceProject::STATUS_CONFIRMED, $projek->status);
        $this->assertSame('5200.00', number_format((float) $projek->contract_value, 2, '.', ''));
    }

    public function test_terima_sebut_harga_idempoten(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Idempoten']);
        $q = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'X', 'qty' => 1, 'unit_price' => 1000]],
        ]);

        $k1 = $this->s->terimaSebutHarga($this->t->id, $q->id);
        $k2 = $this->s->terimaSebutHarga($this->t->id, $q->id);

        $this->assertSame($k1->id, $k2->id);
        $this->assertSame(1, ServiceContract::where('tenant_id', $this->t->id)->count());
    }

    public function test_tolak_sebut_harga(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Tolak']);
        $q = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'X', 'qty' => 1, 'unit_price' => 1000]],
        ]);

        $q = $this->s->tolakSebutHarga($this->t->id, $q->id, 'Harga terlalu tinggi');

        $this->assertSame(ServiceQuotation::STATUS_REJECTED, $q->status);
        $this->assertSame('Harga terlalu tinggi', $q->rejection_reason);
        $this->assertNotNull($q->rejected_at);
    }

    public function test_sebut_harga_masih_sah(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Sah']);
        $q = $this->s->ciptaSebutHarga($this->t->id, $p->id, [
            'line_items' => [['description' => 'X', 'qty' => 1, 'unit_price' => 1000]],
            'valid_until' => now()->addDays(7)->toDateString(),
        ]);

        $this->assertTrue($q->masihSah());
        $this->assertFalse($q->masihSah(now()->addDays(30)));
    }

    // ===================================================================
    // KRU & PENUGASAN
    // ===================================================================

    public function test_cipta_kru_dan_semak_kemahiran(): void
    {
        $k = $this->s->ciptaKru($this->t->id, [
            'name' => 'Juru Gambar',
            'skills' => ['Fotografi', 'Lightroom'],
            'day_rate' => 350,
        ]);

        $this->assertTrue($k->adaKemahiran('fotografi'));
        $this->assertTrue($k->adaKemahiran('LIGHTROOM'));
        $this->assertFalse($k->adaKemahiran('videografi'));
        $this->assertSame('700.00', number_format($k->kiraKosHarian(2), 2, '.', ''));
        $this->assertFalse($k->is_freelance);
        $this->assertTrue($k->is_active);
    }

    public function test_tugaskan_kru_ke_sesi(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Tugas']);
        $sesi = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
        ]);
        $k = $this->s->ciptaKru($this->t->id, ['name' => 'Kru', 'day_rate' => 250]);

        $a = $this->s->tugsakanKru($this->t->id, $p->id, $k->id, ['service_session_id' => $sesi->id]);

        $this->assertSame($k->id, $a->crew_id);
        $this->assertSame('250.00', number_format((float) $a->agreed_rate, 2, '.', ''));
        $this->assertSame(ServiceAssignment::STATUS_ASSIGNED, $a->status);
        $this->assertSame(1, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('event_name', ServiceDomainEvent::ASSIGNMENT_CREATED)->count());
    }

    public function test_tugaskan_kru_bertindih_ditolak(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Bertindih']);
        $sesi = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
        ]);
        $k = $this->s->ciptaKru($this->t->id, ['name' => 'Kru Sibuk']);

        $this->s->tugsakanKru($this->t->id, $p->id, $k->id, ['service_session_id' => $sesi->id]);

        $this->expectException(\RuntimeException::class);
        $this->s->tugsakanKru($this->t->id, $p->id, $k->id, [
            'start_at' => '2026-11-01 10:00:00',
            'end_at' => '2026-11-01 12:00:00',
            'role' => 'lead',
        ]);
    }

    public function test_cadang_kru_ikut_kemahiran_dan_harga(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Cadang']);
        $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
        ]);

        $this->s->ciptaKru($this->t->id, ['name' => 'Mahal', 'skills' => ['fotografi'], 'day_rate' => 500]);
        $this->s->ciptaKru($this->t->id, ['name' => 'Murah', 'skills' => ['fotografi'], 'day_rate' => 150]);
        $this->s->ciptaKru($this->t->id, ['name' => 'Tak Mahir', 'skills' => ['masak'], 'day_rate' => 100]);

        $calon = $this->s->cadangKru($this->t->id, $p->id, 'fotografi', 5);

        $this->assertCount(2, $calon);
        $this->assertSame('Murah', $calon[0]['name']);
        $this->assertSame('Mahal', $calon[1]['name']);
    }

    public function test_cadang_kru_tidak_termasuk_yang_sibuk(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Sibuk']);
        $sesi = $this->s->ciptaSesi($this->t->id, $p->id, [
            'name' => 'Sesi',
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
        ]);

        $sibuk = $this->s->ciptaKru($this->t->id, ['name' => 'Sibuk', 'day_rate' => 100]);
        $this->s->ciptaKru($this->t->id, ['name' => 'Bebas', 'day_rate' => 200]);

        $this->s->tugsakanKru($this->t->id, $p->id, $sibuk->id, ['service_session_id' => $sesi->id]);

        $calon = $this->s->cadangKru($this->t->id, $p->id);

        $this->assertCount(1, $calon);
        $this->assertSame('Bebas', $calon[0]['name']);
    }

    public function test_kira_kos_sebenar_daripada_penugasan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Kos']);
        $k1 = $this->s->ciptaKru($this->t->id, ['name' => 'K1', 'day_rate' => 300]);
        $k2 = $this->s->ciptaKru($this->t->id, ['name' => 'K2', 'day_rate' => 200]);

        $this->s->tugsakanKru($this->t->id, $p->id, $k1->id, [
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
            'role' => 'lead',
        ]);
        $this->s->tugsakanKru($this->t->id, $p->id, $k2->id, [
            'start_at' => '2026-11-01 09:00:00',
            'end_at' => '2026-11-01 17:00:00',
            'role' => 'crew',
        ]);

        $this->assertSame(500.0, $this->s->kiraKosSebenar($this->t->id, $p->id));
    }

    // ===================================================================
    // TUGAS & CHECKLIST
    // ===================================================================

    public function test_cipta_tugas_ikut_urutan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Tugas']);

        $t1 = $this->s->ciptaTugas($this->t->id, $p->id, ['title' => 'Tugas 1']);
        $t2 = $this->s->ciptaTugas($this->t->id, $p->id, ['title' => 'Tugas 2']);

        $this->assertSame(1, $t1->sequence);
        $this->assertSame(2, $t2->sequence);
        $this->assertSame(ServiceTask::STATUS_PENDING, $t1->status);
        $this->assertSame(ServiceTask::PHASE_PREPARATION, $t1->phase);
    }

    public function test_jana_checklist_fotografi(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Fotografi', 'industry' => 'fotografi']);

        $tugas = $this->s->janaChecklist($this->t->id, $p->id, 'fotografi');

        $this->assertCount(count(ServiceOSService::TEMPLAT_CHECKLIST['fotografi']), $tugas);
        $this->assertSame(count($tugas), ServiceTask::where('tenant_id', $this->t->id)->count());
        $this->assertTrue(collect($tugas)->contains(fn ($t) => (bool) $t->is_mandatory));
    }

    public function test_jana_checklist_industri_tidak_dikenali_guna_umum(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'X']);

        $tugas = $this->s->janaChecklist($this->t->id, $p->id, 'industri_tak_wujud');

        $this->assertCount(count(ServiceOSService::TEMPLAT_CHECKLIST['umum']), $tugas);
    }

    public function test_siapkan_tugas_kira_kemajuan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Kemajuan']);

        $t1 = $this->s->ciptaTugas($this->t->id, $p->id, ['title' => 'A']);
        $t2 = $this->s->ciptaTugas($this->t->id, $p->id, ['title' => 'B']);

        $this->s->siapkanTugas($this->t->id, $t1->id);

        $this->assertSame(50, $p->fresh()->progress_percent);

        $this->s->siapkanTugas($this->t->id, $t2->id);

        $this->assertSame(100, $p->fresh()->progress_percent);
        $this->assertNotNull($t1->fresh()->completed_at);
    }

    public function test_tugas_lewat_dikesan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Lewat']);

        $this->s->ciptaTugas($this->t->id, $p->id, [
            'title' => 'Lewat',
            'due_at' => now()->subDays(3)->toDateTimeString(),
        ]);
        $this->s->ciptaTugas($this->t->id, $p->id, [
            'title' => 'Akan datang',
            'due_at' => now()->addDays(3)->toDateTimeString(),
        ]);

        $lewat = $this->s->tugasLewat($this->t->id);

        $this->assertCount(1, $lewat);
        $this->assertSame('Lewat', $lewat[0]['title']);
        $this->assertGreaterThanOrEqual(2, $lewat[0]['hari_lewat']);
    }

    public function test_tugas_selesai_tidak_dikira_lewat(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Selesai']);
        $t = $this->s->ciptaTugas($this->t->id, $p->id, [
            'title' => 'Dah siap',
            'due_at' => now()->subDays(5)->toDateTimeString(),
        ]);

        $this->s->siapkanTugas($this->t->id, $t->id);

        $this->assertCount(0, $this->s->tugasLewat($this->t->id));
        $this->assertFalse($t->fresh()->lewat());
    }

    public function test_had_tugas_per_projek(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Had Tugas']);

        for ($i = 1; $i <= ServiceOSService::MAX_TASKS_PER_PROJECT; $i++) {
            ServiceTask::create([
                'tenant_id' => $this->t->id,
                'service_project_id' => $p->id,
                'title' => "T{$i}",
                'sequence' => $i,
            ]);
        }

        $this->expectException(\RuntimeException::class);
        $this->s->ciptaTugas($this->t->id, $p->id, ['title' => 'Terlebih']);
    }

    // ===================================================================
    // PERALATAN
    // ===================================================================

    public function test_cipta_peralatan(): void
    {
        $e = $this->s->ciptaPeralatan($this->t->id, [
            'code' => 'CAM-01',
            'name' => 'Kamera Utama',
            'quantity_total' => 3,
            'replacement_value' => 8000,
        ]);

        $this->assertSame(3, $e->quantity_total);
        $this->assertTrue($e->is_active);
    }

    public function test_tempah_peralatan_kurangkan_baki(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Tempah']);
        $e = $this->s->ciptaPeralatan($this->t->id, [
            'code' => 'LENS-01',
            'name' => 'Lensa',
            'quantity_total' => 2,
        ]);

        $this->assertSame(2, $e->bakiUntukTempoh(now(), now()->addDay()));

        $this->s->tempahPeralatan($this->t->id, $p->id, $e->id, [
            'quantity' => 1,
            'reserved_from' => now()->toDateTimeString(),
            'reserved_to' => now()->addDay()->toDateTimeString(),
        ]);

        $this->assertSame(1, $e->bakiUntukTempoh(now(), now()->addDay()));
    }

    public function test_tempah_peralatan_melebihi_stok_ditolak(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Lebih']);
        $e = $this->s->ciptaPeralatan($this->t->id, [
            'code' => 'TRIPOD',
            'name' => 'Tripod',
            'quantity_total' => 1,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->s->tempahPeralatan($this->t->id, $p->id, $e->id, [
            'quantity' => 5,
            'reserved_from' => now()->toDateTimeString(),
            'reserved_to' => now()->addDay()->toDateTimeString(),
        ]);
    }

    // ===================================================================
    // PENYERAHAN
    // ===================================================================

    public function test_cipta_dan_terima_penyerahan(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Serah']);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_QUOTED);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_CONFIRMED);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_IN_PROGRESS);

        $d = $this->s->ciptaPenyerahan($this->t->id, $p->id, [
            'delivery_type' => ServiceDelivery::TYPE_GALLERY,
            'items' => [['name' => 'Galeri', 'url' => 'https://contoh.my/g']],
        ]);

        $this->assertSame(ServiceDelivery::STATUS_PENDING, $d->status);

        $d = $this->s->terimaPenyerahan($this->t->id, $d->id, 'Aminah');

        $this->assertSame(ServiceDelivery::STATUS_ACCEPTED, $d->status);
        $this->assertTrue($d->diterima());
        $this->assertSame('Aminah', $d->received_by_name);
        $this->assertSame(ServiceProject::STATUS_DELIVERED, $p->fresh()->status);
    }

    // ===================================================================
    // PERISTIWA DOMAIN
    // ===================================================================

    public function test_rekod_peristiwa_idempoten(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Idem']);

        $e1 = $this->s->rekodPeristiwa($this->t->id, ServiceDomainEvent::PROJECT_STARTED, $p, ['a' => 1]);
        $e2 = $this->s->rekodPeristiwa($this->t->id, ServiceDomainEvent::PROJECT_STARTED, $p, ['a' => 1]);

        $this->assertSame($e1->id, $e2->id);
        $this->assertSame(1, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('event_name', ServiceDomainEvent::PROJECT_STARTED)->count());
    }

    public function test_rekod_peristiwa_payload_berbeza_cipta_baharu(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Beza']);

        $this->s->rekodPeristiwa($this->t->id, ServiceDomainEvent::PROJECT_STARTED, $p, ['a' => 1]);
        $this->s->rekodPeristiwa($this->t->id, ServiceDomainEvent::PROJECT_STARTED, $p, ['a' => 2]);

        $this->assertSame(2, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('event_name', ServiceDomainEvent::PROJECT_STARTED)->count());
    }

    public function test_proses_outbox_tanda_dispatched(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Outbox']);

        $hasil = $this->s->prosesOutbox($this->t->id);

        $this->assertGreaterThanOrEqual(1, $hasil['diproses']);
        $this->assertSame(0, $hasil['gagal']);
        $this->assertSame(0, ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->where('status', ServiceDomainEvent::STATUS_PENDING)->count());
    }

    public function test_proses_outbox_tidak_proses_lebih_3_kali(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'Cubaan']);

        ServiceDomainEvent::where('tenant_id', $this->t->id)
            ->update(['status' => ServiceDomainEvent::STATUS_FAILED, 'attempts' => 3]);

        $hasil = $this->s->prosesOutbox($this->t->id);

        $this->assertSame(0, $hasil['diproses']);
    }

    // ===================================================================
    // RINGKASAN
    // ===================================================================

    public function test_ringkasan_mengira_metrik(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, [
            'title' => 'Ringkasan',
            'contract_value' => 10000,
        ]);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_QUOTED);
        $this->s->tukarStatus($this->t->id, $p->id, ServiceProject::STATUS_CONFIRMED);

        $r = $this->s->ringkasan($this->t->id);

        $this->assertSame(1, $r['jumlah_projek']);
        $this->assertSame(1, $r['projek_aktif']);
        $this->assertSame(10000.0, $r['nilai_kontrak_aktif']);
        $this->assertArrayHasKey('ikut_status', $r);
        $this->assertSame(0, $r['tugas_lewat']);
    }

    public function test_ringkasan_kosong_untuk_tenant_baru(): void
    {
        $r = $this->s->ringkasan($this->t->id);

        $this->assertSame(0, $r['jumlah_projek']);
        $this->assertSame(0.0, $r['nilai_kontrak_aktif']);
        $this->assertSame(0, $r['tugas_lewat']);
    }

    public function test_senarai_industri(): void
    {
        $senarai = $this->s->senaraiIndustri();

        $this->assertContains('umum', $senarai);
        $this->assertContains('fotografi', $senarai);
        $this->assertContains('wedding', $senarai);
        $this->assertContains('kontraktor', $senarai);
        $this->assertContains('katering', $senarai);
    }

    // ===================================================================
    // PENJANA NOMBOR
    // ===================================================================

    public function test_penjana_nombor_unik(): void
    {
        $a = $this->s->janaNoProjek($this->t->id);
        $this->s->ciptaProjek($this->t->id, ['title' => 'P1']);
        $b = $this->s->janaNoProjek($this->t->id);

        $this->assertNotSame($a, $b);
        $this->assertStringStartsWith('SVP-', $a);
    }

    // ===================================================================
    // PENGASINGAN TENANT
    // ===================================================================

    public function test_pengasingan_tenant_projek(): void
    {
        [, $t2] = $this->daftar('t2@serviceos.my', 'Tenant Dua Sdn Bhd');

        $this->s->ciptaProjek($this->t->id, ['title' => 'Tenant 1']);

        $this->dalamTenant($t2);
        $this->s->ciptaProjek($t2->id, ['title' => 'Tenant 2']);

        $this->assertSame(1, ServiceProject::withoutGlobalScopes()->where('tenant_id', $this->t->id)->count());
        $this->assertSame(1, ServiceProject::withoutGlobalScopes()->where('tenant_id', $t2->id)->count());
    }

    public function test_pengasingan_tenant_pelanggan(): void
    {
        [, $t2] = $this->daftar('t3@serviceos.my', 'Tenant Tiga Sdn Bhd');

        $this->s->ciptaPelanggan($this->t->id, ['display_name' => 'A', 'email' => 'a@x.my']);

        $this->dalamTenant($t2);
        $this->s->ciptaPelanggan($t2->id, ['display_name' => 'A', 'email' => 'a@x.my']);

        // Emel sama dibenarkan merentas tenant
        $this->assertSame(1, ServiceClient::withoutGlobalScopes()->where('tenant_id', $this->t->id)->count());
        $this->assertSame(1, ServiceClient::withoutGlobalScopes()->where('tenant_id', $t2->id)->count());
    }

    // ===================================================================
    // API
    // ===================================================================

    public function test_api_ringkasan(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/service-os/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['jumlah_projek', 'projek_aktif', 'ikut_status']]);
    }

    public function test_api_industri(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/service-os/industries')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_cipta_pelanggan(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->postJson('/api/v1/service-os/clients', [
            'display_name' => 'API Klien',
            'email' => 'api@klien.my',
        ])
            ->assertCreated()
            ->assertJsonPath('data.display_name', 'API Klien');
    }

    public function test_api_cipta_projek_dan_tukar_status(): void
    {
        $this->sebagai($this->u, $this->t);

        $res = $this->postJson('/api/v1/service-os/projects', [
            'title' => 'Projek API',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
        ])->assertCreated();

        $id = $res->json('data.id');

        $this->patchJson("/api/v1/service-os/projects/{$id}/status", [
            'status' => ServiceProject::STATUS_QUOTED,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', ServiceProject::STATUS_QUOTED);
    }

    public function test_api_tukar_status_tidak_sah_pulang_422(): void
    {
        $p = $this->s->ciptaProjek($this->t->id, ['title' => 'API Tak Sah']);
        $pid = $p->id;

        $this->sebagai($this->u, $this->t);

        $this->patchJson("/api/v1/service-os/projects/{$pid}/status", [
            'status' => ServiceProject::STATUS_COMPLETED,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_tugas_lewat(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/service-os/overdue-tasks')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_templat_checklist(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/service-os/checklist-template?industry=fotografi')
            ->assertOk()
            ->assertJsonPath('industry', 'fotografi');
    }

    public function test_api_tanpa_kebenaran_tidak_dapat_akses_tenant_lain(): void
    {
        [$u2, $t2] = $this->daftar('staff@serviceos.my', 'Tenant Staff');

        $this->s->ciptaProjek($this->t->id, ['title' => 'Rahsia Tenant 1']);

        $this->sebagai($u2, $t2);

        $this->getJson('/api/v1/service-os/summary')
            ->assertOk()
            ->assertJsonPath('data.jumlah_projek', 0);
    }
}
