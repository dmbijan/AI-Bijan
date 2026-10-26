<?php

namespace Tests\Feature;

use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Blast\Models\BlastSuppression;
use App\Modules\Blast\Services\BlastService;
use App\Modules\Blast\Services\DeliverabilityService;
use App\Modules\Blast\Services\OptOutService;
use App\Modules\Blast\Services\RateLimiterService;
use App\Modules\Blast\Services\TemplateService;
use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Blast Engine (BAB 82 / Fasa 2).
 *
 * Fokus: pagar consent, opt-out, had kadar, deliverability, template.
 */
class BlastTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@blast.my', 'Blast Sdn Bhd');

        // Provider e-mel mod log supaya ujian tidak hantar mesej sebenar
        config([
            'blast.providers.email.mode' => 'log',
            'blast.providers.email.enabled' => true,
            'blast.quotas.daily_per_tenant' => 100,
        ]);
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
        ], $ganti));
    }

    private function kempen(array $ganti = []): BlastCampaign
    {
        $this->dalamTenant($this->t);

        return BlastCampaign::create(array_merge([
            'name' => 'Kempen Ujian',
            'channel' => 'email',
            'subject' => 'Tawaran {{syarikat}}',
            'body' => 'Helo {{nama}}, tawaran untuk {{syarikat}}. {{unsubscribe_link}}',
            'status' => BlastCampaign::STATUS_DRAFT,
            'created_by' => $this->u->id,
        ], $ganti));
    }

    // ---------------------------------------------------------------------
    // Pagar consent
    // ---------------------------------------------------------------------

    public function test_blast_tanpa_consent_tidak_queue_mesej(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen();
        $tanpaConsent = $this->prospect(['consent_status' => Prospect::CONSENT_UNKNOWN]);

        $hasil = app(BlastService::class)->binaPesanan($kempen, [$tanpaConsent]);

        $this->assertSame(0, $hasil['queued']);
        $this->assertSame(1, $hasil['skipped']);
        $this->assertSame(0, BlastMessage::where('campaign_id', $kempen->id)->count());
    }

    public function test_blast_dengan_consent_queue_mesej(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen();
        $adaConsent = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        $hasil = app(BlastService::class)->binaPesanan($kempen, [$adaConsent]);

        $this->assertSame(1, $hasil['queued']);
        $this->assertSame(0, $hasil['skipped']);

        $mesej = BlastMessage::where('campaign_id', $kempen->id)->first();
        $this->assertNotNull($mesej);
        $this->assertSame('queued', $mesej->status);
    }

    public function test_blast_tidak_kirim_ke_penerima_disekat(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen();
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        // Suppress dahulu
        app(OptOutService::class)->suppress($this->t->id, $prospect->email, null, 'email', 'manual');

        $hasil = app(BlastService::class)->binaPesanan($kempen, [$prospect]);

        $this->assertSame(0, $hasil['queued']);
        $this->assertSame(1, $hasil['skipped']);
    }

    // ---------------------------------------------------------------------
    // Penghantaran
    // ---------------------------------------------------------------------

    public function test_hantar_mesej_kempen_berstatus_boleh_hantar(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen(['status' => BlastCampaign::STATUS_APPROVED]);
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        app(BlastService::class)->binaPesanan($kempen, [$prospect]);

        $mesej = BlastMessage::where('campaign_id', $kempen->id)->first();
        $berjaya = app(BlastService::class)->hantarPesanan($mesej);

        $this->assertTrue($berjaya);
        $this->assertSame('sent', $mesej->fresh()->status);
        $this->assertNotNull($mesej->fresh()->sent_at);
    }

    public function test_hantar_mesej_kempen_draf_ditolak(): void
    {
        $this->dalamTenant($this->t);

        // Kempen masih draf — tidak boleh dihantar
        $kempen = $this->kempen(['status' => BlastCampaign::STATUS_DRAFT]);
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        app(BlastService::class)->binaPesanan($kempen, [$prospect]);

        $mesej = BlastMessage::where('campaign_id', $kempen->id)->first();
        $berjaya = app(BlastService::class)->hantarPesanan($mesej);

        $this->assertFalse($berjaya);
        $this->assertSame('skipped', $mesej->fresh()->status);
    }

    public function test_hantar_mesej_consent_ditarik_semasa_queue_ditolak(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen(['status' => BlastCampaign::STATUS_APPROVED]);
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        app(BlastService::class)->binaPesanan($kempen, [$prospect]);
        $mesej = BlastMessage::where('campaign_id', $kempen->id)->first();

        // Consent ditarik balik selepas queue
        $prospect->update(['consent_status' => Prospect::CONSENT_DENIED]);

        $berjaya = app(BlastService::class)->hantarPesanan($mesej);

        $this->assertFalse($berjaya);
        $this->assertSame('skipped', $mesej->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Opt-out
    // ---------------------------------------------------------------------

    public function test_unsubscribe_menambah_suppression_dan_tolak_consent(): void
    {
        $this->dalamTenant($this->t);

        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        $optOut = app(OptOutService::class);
        $optOut->unsubscribe($this->t->id, $prospect->email, null, 'email', 'link', $prospect->id);

        $this->assertTrue($optOut->disekat($this->t->id, $prospect->email, null, 'email'));
        $this->assertSame(Prospect::CONSENT_DENIED, $prospect->fresh()->consent_status);
        $this->assertSame(1, BlastSuppression::where('tenant_id', $this->t->id)->count());
    }

    // ---------------------------------------------------------------------
    // Had kadar
    // ---------------------------------------------------------------------

    public function test_had_kadar_harian_disyek(): void
    {
        config(['blast.quotas.daily_per_tenant' => 2]);

        $this->dalamTenant($this->t);

        $limiter = app(RateLimiterService::class);

        // Bersihkan cache kadar
        \Illuminate\Support\Facades\Cache::forget("blast.rate.{$this->t->id}.email");

        // Ludut kempen supaya boleh guna mesej ujian
        $kempen = $this->kempen();

        $this->assertTrue($limiter->bolehHantar($this->t->id, 'email', 'blast.my'));

        // Simulasikan 2 mesej dihantar
        BlastMessage::create([
            'tenant_id' => $this->t->id,
            'campaign_id' => $kempen->id,
            'channel' => 'email',
            'recipient' => 'a@b.my',
            'status' => 'sent',
        ]);
        BlastMessage::create([
            'tenant_id' => $this->t->id,
            'campaign_id' => $kempen->id,
            'channel' => 'email',
            'recipient' => 'c@d.my',
            'status' => 'sent',
        ]);

        $this->assertFalse($limiter->bolehHantar($this->t->id, 'email', 'blast.my'));
    }

    // ---------------------------------------------------------------------
    // Deliverability
    // ---------------------------------------------------------------------

    public function test_bounce_tinggi_auto_jeda_domain(): void
    {
        $this->dalamTenant($this->t);

        $deliverability = app(DeliverabilityService::class);
        $record = $deliverability->rekod($this->t->id, 'blast.my', BlastMessage::STATUS_SENT);
        $record->update(['sent' => 100, 'bounced' => 10]);

        $deliverability->kiraReputasi($record->fresh());

        $this->assertTrue($record->fresh()->is_paused);
        $this->assertLessThan(100, $record->fresh()->reputation_score);
    }

    public function test_complaint_ketiga_menghenti_domain(): void
    {
        $this->dalamTenant($this->t);

        $deliverability = app(DeliverabilityService::class);
        $record = $deliverability->rekod($this->t->id, 'blast2.my', BlastMessage::STATUS_SENT);
        $record->update(['sent' => 1000, 'complaints' => 5]);

        $deliverability->kiraReputasi($record->fresh());

        $this->assertTrue($record->fresh()->is_paused);
    }

    public function test_domain_bahagi_bounce_tidak_jeda(): void
    {
        $this->dalamTenant($this->t);

        $deliverability = app(DeliverabilityService::class);
        $record = $deliverability->rekod($this->t->id, 'blast3.my', BlastMessage::STATUS_SENT);
        $record->update(['sent' => 1000, 'bounced' => 10]);

        $deliverability->kiraReputasi($record->fresh());

        $this->assertFalse($record->fresh()->is_paused);
    }

    // ---------------------------------------------------------------------
    // Template
    // ---------------------------------------------------------------------

    public function test_template_merge_tag_dan_unknown_dibuang(): void
    {
        $template = new TemplateService();
        $hasil = $template->dengan([
            'nama' => 'Ahmad',
            'syarikat' => 'Sirma Satu',
        ])->render('Helo {{nama}} dari {{syarikat}}. {{tidak_dikenal}}');

        $this->assertStringContainsString('Ahmad', $hasil);
        $this->assertStringContainsString('Sirma Satu', $hasil);
        $this->assertStringNotContainsString('{{', $hasil);
    }

    public function test_template_mesej_blast_ganti_merge_tag(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen();
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        app(BlastService::class)->binaPesanan($kempen, [$prospect]);

        $mesej = BlastMessage::where('campaign_id', $kempen->id)->first();

        $this->assertStringContainsString('Ahmad', $mesej->body);
        $this->assertStringContainsString('Sirma Satu', $mesej->body);
        $this->assertSame('Tawaran Sirma Satu', $mesej->subject);
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_cipta_kempen(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/blast/campaigns', [
                'name' => 'Kempen API',
                'channel' => 'email',
                'subject' => 'Subjek',
                'body' => 'Badan mesej {{nama}}',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', BlastCampaign::STATUS_DRAFT);
    }

    public function test_api_lulus_kempen_tanpa_mesej_gagal(): void
    {
        $kempen = $this->kempen(['status' => BlastCampaign::STATUS_REVIEW]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/blast/campaigns/{$kempen->id}/approve")
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_lulus_kempen_dengan_mesej_berjaya(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen(['status' => BlastCampaign::STATUS_REVIEW]);
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        app(BlastService::class)->binaPesanan($kempen, [$prospect]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/blast/campaigns/{$kempen->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', BlastCampaign::STATUS_APPROVED);
    }

    public function test_api_bina_audience_guna_prospect_id(): void
    {
        $this->dalamTenant($this->t);

        $kempen = $this->kempen();
        $prospect = $this->prospect([
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/blast/campaigns/{$kempen->id}/audience", [
                'prospect_ids' => [$prospect->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.queued', 1);
    }

    public function test_api_preview_merge_tag(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/blast/preview', [
                'subject' => 'Helo {{nama}}',
                'body' => 'Toffer untuk {{syarikat}}',
            ])
            ->assertOk()
            ->assertJsonPath('data.subject', 'Helo Ahmad')
            ->assertJsonPath('data.body', 'Toffer untuk Syarikat Contoh Sdn Bhd');
    }

    public function test_api_kuota(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/blast/quota')
            ->assertOk()
            ->assertJsonStructure(['data' => ['sent_today', 'daily_per_tenant', 'remaining']]);
    }

    public function test_api_tenant_lain_tidak_nampak_kempen(): void
    {
        $kempen = $this->kempen();

        [$u2, $t2] = $this->daftar('pemilik2@blast.my', 'Blast Kedua');

        $this->sebagai($u2, $t2)
            ->getJson('/api/v1/blast/campaigns')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
