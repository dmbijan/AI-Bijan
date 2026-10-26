<?php

namespace Tests\Feature;

use App\Modules\Prospecting\Models\Prospect;
use App\Modules\SalesCopilot\Services\DealBriefService;
use App\Modules\SalesCopilot\Services\FollowUpNudgerService;
use App\Modules\SalesCopilot\Services\ReplyAnalyzerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Sales Copilot (BAB 82 / Fasa 3).
 *
 * Fokus: klasifikasi balasan, brief, follow-up dengan pagar consent.
 */
class SalesCopilotTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@sales.my', 'Sales Sdn Bhd');
    }

    private function prospect(array $ganti = []): Prospect
    {
        $this->dalamTenant($this->t);

        return Prospect::create(array_merge([
            'first_name' => 'Ahmad',
            'last_name' => 'Ali',
            'email' => 'ahmad@sirma.my',
            'phone' => '+60123456789',
            'company' => 'Sirma Satu',
            'job_title' => 'Pengarah',
            'score' => 80,
            'status' => Prospect::STATUS_CONTACTED,
        ], $ganti));
    }

    // ---------------------------------------------------------------------
    // Klasifikasi balasan
    // ---------------------------------------------------------------------

    public function test_klasifikasi_balasan_minat(): void
    {
        $hasil = app(ReplyAnalyzerService::class)->analyze('Saya minat. Boleh kongsi harga?');

        $this->assertSame(ReplyAnalyzerService::INTERESTED, $hasil['category']);
        // Balasan mengandungi beberapa isyarat (minat + harga + soalan),
        // jadi ia perlu disemak manusia walaupun kategori utama dikenal pasti.
        $this->assertTrue($hasil['needs_human']);
    }

    public function test_klasifikasi_balasan_tarik_langganan(): void
    {
        $hasil = app(ReplyAnalyzerService::class)->analyze('Please unsubscribe me from this list');

        $this->assertSame(ReplyAnalyzerService::UNSUBSCRIBE, $hasil['category']);
        // Unsubscribe mesti keyakinan tinggi
        $this->assertGreaterThanOrEqual(0.9, $hasil['confidence']);
    }

    public function test_klasifikasi_balasan_tidak_minat(): void
    {
        $hasil = app(ReplyAnalyzerService::class)->analyze('Not interested, thanks');

        $this->assertSame(ReplyAnalyzerService::NEGATIVE, $hasil['category']);
    }

    public function test_klasifikasi_balasan_bahasa_melayu(): void
    {
        $hasil = app(ReplyAnalyzerService::class)->analyze('Saya minat, boleh hubungi saya');

        $this->assertSame(ReplyAnalyzerService::INTERESTED, $hasil['category']);
    }

    public function test_klasifikasi_balasan_kosong_unknown(): void
    {
        $hasil = app(ReplyAnalyzerService::class)->analyze('');

        $this->assertSame(ReplyAnalyzerService::UNKNOWN, $hasil['category']);
        $this->assertTrue($hasil['needs_human']);
    }

    public function test_proses_balasan_unsubscribe_tarik_consent(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        $hasil = app(ReplyAnalyzerService::class)->prosesBalasan(
            $prospect,
            'stop sending me emails'
        );

        $this->assertSame(ReplyAnalyzerService::UNSUBSCRIBE, $hasil['category']);
        $this->assertContains('suppress', $hasil['actions']);
        $this->assertSame(Prospect::CONSENT_DENIED, $prospect->fresh()->consent_status);
    }

    public function test_proses_balasan_minat_tanda_status_replied(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        app(ReplyAnalyzerService::class)->prosesBalasan($prospect, 'Interested, send me the pricing');

        $this->assertSame(Prospect::STATUS_REPLIED, $prospect->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Deal brief
    // ---------------------------------------------------------------------

    public function test_brief_mengandungi_kandungan_penting(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        $brief = app(DealBriefService::class)->brief($this->t->id, $prospect->id);

        $this->assertNotNull($brief);
        $this->assertSame('Ahmad Ali', $brief['prospect']['nama']);
        $this->assertSame('Sirma Satu', $brief['prospect']['syarikat']);
        $this->assertSame(80, $brief['skor']['nilai']);
        $this->assertSame('panas', $brief['skor']['label']);
        $this->assertTrue($brief['consent']['boleh_email']);
        $this->assertNotEmpty($brief['ringkasan']);
        $this->assertNotEmpty($brief['cadangan_berturut']);
    }

    public function test_brief_tenant_lain_null(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect();

        [$u2, $t2] = $this->daftar('pemilik2@sales.my', 'Sales Kedua');

        $this->assertNull(app(DealBriefService::class)->brief($t2->id, $prospect->id));
    }

    // ---------------------------------------------------------------------
    // Follow-up nudger — PAGAR CONSENT
    // ---------------------------------------------------------------------

    public function test_follow_up_hanya_untuk_prospect_dengan_consent(): void
    {
        $this->dalamTenant($this->t);

        // Dengan consent
        $this->prospect([
            'email' => 'ada@consent.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
            'last_touched_at' => now()->subDays(10),
        ]);

        // Tanpa consent
        $this->prospect([
            'email' => 'tiada@consent.my',
            'consent_status' => Prospect::CONSENT_UNKNOWN,
            'last_touched_at' => now()->subDays(10),
        ]);

        $cadangan = app(FollowUpNudgerService::class)->cadangFollowUp($this->t->id);

        $this->assertCount(1, $cadangan);
        $this->assertSame('Ahmad Ali', $cadangan[0]['nama']);
    }

    public function test_follow_up_hanya_untuk_prospect_senyap(): void
    {
        $this->dalamTenant($this->t);

        // Baru contacted — jangan follow up
        $this->prospect([
            'email' => 'baru@consent.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
            'last_touched_at' => now(),
        ]);

        $cadangan = app(FollowUpNudgerService::class)->cadangFollowUp($this->t->id);

        $this->assertCount(0, $cadangan);
    }

    public function test_follow_up_cadangan_saluran(): void
    {
        $this->dalamTenant($this->t);

        $this->prospect([
            'email' => 'wa@consent.my',
            'whatsapp_number' => '+60123456789',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
            'last_touched_at' => now()->subDays(10),
        ]);

        $cadangan = app(FollowUpNudgerService::class)->cadangFollowUp($this->t->id);

        $this->assertCount(1, $cadangan);
        $this->assertSame('whatsapp', $cadangan[0]['cadangan_saluran']);
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_brief_prospect(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect();

        $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/sales-copilot/brief/{$prospect->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.prospect.nama', 'Ahmad Ali');
    }

    public function test_api_analisis_balasan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/sales-copilot/analyze-reply', [
                'reply' => 'Interested, can we schedule a demo?',
            ])
            ->assertOk()
            ->assertJsonPath('data.category', ReplyAnalyzerService::INTERESTED);
    }

    public function test_api_proses_balasan(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/sales-copilot/prospects/{$prospect->id}/reply", [
                'reply' => 'Saya minat, boleh kongsi harga?',
            ])
            ->assertOk()
            ->assertJsonPath('data.category', ReplyAnalyzerService::INTERESTED);
    }

    public function test_api_follow_up_queue(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/sales-copilot/follow-up-queue')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
