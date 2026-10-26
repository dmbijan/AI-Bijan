<?php

namespace Tests\Feature;

use App\Modules\Prospecting\Models\Prospect;
use App\Modules\Prospecting\Models\ProspectSource;
use App\Modules\Prospecting\Services\ConsentService;
use App\Modules\Prospecting\Services\DedupeService;
use App\Modules\Prospecting\Services\ProspectScoringService;
use App\Modules\Prospecting\Services\PublicScraperService;
use App\Modules\Prospecting\Services\RobotRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Prospecting Engine (BAB 82 / Fasa 1).
 *
 * Fokus: consent gate, dedupe, robots.txt, import CSV, skor.
 */
class ProspectingTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@prospekt.my', 'Prospekt Sdn Bhd');
    }

    // ---------------------------------------------------------------------
    // Consent — PAGAR PALING KRITIKAL
    // ---------------------------------------------------------------------

    public function test_prospect_tanpa_consent_tidak_boleh_dihantar(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'first_name' => 'Ahmad',
            'last_name' => 'Ali',
            'email' => 'ahmad@sirma.com.my',
            'company' => 'Sirma Sendirian',
            'consent_status' => Prospect::CONSENT_UNKNOWN,
        ]);

        $consent = app(ConsentService::class);

        $this->assertFalse($consent->bolehDihantar($prospect, 'email'));
        $this->assertFalse($consent->bolehDihantar($prospect, 'whatsapp'));
        $this->assertFalse($consent->bolehDihantar($prospect, 'sms'));
    }

    public function test_consent_granted_tanpa_bukti_tidak_boleh_dihantar(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'email' => 'tanpa@bukti.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            // consent_evidence TIADA
        ]);

        $this->assertFalse(app(ConsentService::class)->bolehDihantar($prospect, 'email'));
    }

    public function test_consent_granted_dengan_bukti_boleh_dihantar(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'email' => 'ada@bukti.my',
            'phone' => '+60123456789',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-tandatangan-2026-10',
        ]);

        $consent = app(ConsentService::class);

        $this->assertTrue($consent->bolehDihantar($prospect, 'email'));
        $this->assertTrue($consent->bolehDihantar($prospect, 'sms'));
        $this->assertTrue($consent->bolehDihantar($prospect, 'whatsapp'));
    }

    public function test_consent_tarik_balik_menolak_penghantaran(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'email' => 'tarik@bukti.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang-2026',
        ]);

        $consent = app(ConsentService::class);
        $this->assertTrue($consent->bolehDihantar($prospect, 'email'));

        $consent->tarikConsent($prospect, 'permintaan-pelanggan');

        $this->assertFalse($consent->bolehDihantar($prospect, 'email'));
        $this->assertSame(Prospect::CONSENT_DENIED, $prospect->fresh()->consent_status);
    }

    public function test_consent_luput_tidak_boleh_dihantar(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'email' => 'luput@bukti.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now()->subDays(400),
            'consent_evidence' => 'borang-2024',
            'consent_expires_at' => now()->subDay(),
        ]);

        $this->assertFalse(app(ConsentService::class)->bolehDihantar($prospect, 'email'));
    }

    public function test_laporan_consent_kaunter_benar(): void
    {
        $this->dalamTenant($this->t);

        Prospect::create(['email' => 'a@x.my', 'consent_status' => Prospect::CONSENT_GRANTED, 'consent_at' => now(), 'consent_evidence' => 'b1']);
        Prospect::create(['email' => 'b@x.my', 'consent_status' => Prospect::CONSENT_DENIED]);
        Prospect::create(['email' => 'c@x.my', 'consent_status' => Prospect::CONSENT_UNKNOWN]);

        $laporan = app(ConsentService::class)->laporanConsent($this->t->id);

        $this->assertSame(3, $laporan['jumlah']);
        $this->assertSame(1, $laporan['granted']);
        $this->assertSame(1, $laporan['denied']);
        $this->assertSame(1, $laporan['unknown']);
    }

    // ---------------------------------------------------------------------
    // Dedupe
    // ---------------------------------------------------------------------

    public function test_email_normalisasi_gmail_buang_titik_dan_alias(): void
    {
        $dedupe = app(DedupeService::class);

        // Gmail: titik dibuang DAN +alias dibuang
        $this->assertSame('ahmadtest@gmail.com', $dedupe->normalkanEmail('Ahmad.Test+news@gmail.com'));
        $this->assertSame('ahmadtest@gmail.com', $dedupe->normalkanEmail('ahmadtest@gmail.com'));
        // Domain lain: hanya +alias dibuang, titik kekal
        $this->assertSame('ahmad@sirma.com.my', $dedupe->normalkanEmail('  Ahmad@SIRMA.com.my '));
        $this->assertSame('ahmad@sirma.com.my', $dedupe->normalkanEmail('ahmad+promo@sirma.com.my'));
        $this->assertNull($dedupe->normalkanEmail('bukan-email'));
    }

    public function test_telefon_normalisasi_e164_malaysia(): void
    {
        $dedupe = app(DedupeService::class);

        $this->assertSame('+60123456789', $dedupe->normalkanPhone('012-345 6789'));
        $this->assertSame('+60123456789', $dedupe->normalkanPhone('+60123456789'));
        $this->assertSame('+60123456789', $dedupe->normalkanPhone('60123456789'));
        $this->assertNull($dedupe->normalkanPhone('123'));
    }

    public function test_prospect_dupe_dikesan_dan_digabung(): void
    {
        $this->dalamTenant($this->t);

        $dedupe = app(DedupeService::class);

        $pertama = $dedupe->simpanDenganSemakanDupe($this->t->id, [
            'first_name' => 'Ahmad',
            'email' => 'Ahmad@Sirma.com.my',
            'company' => 'Sirma lama',
        ]);

        $this->assertTrue($pertama['created']);
        $this->assertFalse($pertama['duplicate']);

        $kedua = $dedupe->simpanDenganSemakanDupe($this->t->id, [
            'first_name' => 'Ahmad',
            'email' => 'ahmad@sirma.com.my',
            'company' => 'Sirma Baharu',
        ]);

        $this->assertFalse($kedua['created']);
        $this->assertTrue($kedua['duplicate']);
        $this->assertSame('Sirma Baharu', $kedua['prospect']->company);
        $this->assertSame(1, Prospect::where('tenant_id', $this->t->id)->count());
    }

    public function test_dupe_gmail_dengan_titik_dikenali(): void
    {
        $this->dalamTenant($this->t);

        $dedupe = app(DedupeService::class);

        $dedupe->simpanDenganSemakanDupe($this->t->id, ['email' => 'ahmad.test@gmail.com']);
        $kedua = $dedupe->simpanDenganSemakanDupe($this->t->id, ['email' => 'ahmadtest@gmail.com']);

        $this->assertTrue($kedua['duplicate']);
        $this->assertSame(1, Prospect::where('tenant_id', $this->t->id)->count());
    }

    // ---------------------------------------------------------------------
    // Robots.txt
    // ---------------------------------------------------------------------

    public function test_robots_menolak_disallow(): void
    {
        $rules = new RobotRules("User-agent: *\nDisallow: /admin\nDisallow: /private", '*');

        $this->assertFalse($rules->dibenarkan('/admin/users'));
        $this->assertFalse($rules->dibenarkan('/private/data'));
        $this->assertTrue($rules->dibenarkan('/produk'));
    }

    public function test_robots_allow_menangi_disallow_lebih_panjang(): void
    {
        $rules = new RobotRules("User-agent: *\nDisallow: /data\nAllow: /data/public", '*');

        $this->assertFalse($rules->dibenarkan('/data/private'));
        $this->assertTrue($rules->dibenarkan('/data/public'));
    }

    public function test_robots_wildcard_dan_anchor(): void
    {
        $rules = new RobotRules("User-agent: *\nDisallow: /*.pdf$", '*');

        $this->assertFalse($rules->dibenarkan('/dokumen/laporan.pdf'));
        $this->assertTrue($rules->dibenarkan('/dokumen/laporan'));
    }

    public function test_robots_user_agent_khusus(): void
    {
        // Group 'AI-Bijan' ada Disallow: / — jadi ejen itu disekat sepenuhnya.
        $rules = new RobotRules("User-agent: AI-Bijan\nDisallow: /\n\nUser-agent: *\nDisallow: /secret", 'AI-Bijan-Prospecting/1.0');

        $this->assertFalse($rules->dibenarkan('/mana-mana'));

        // Ejen lain tertakluk kepada kumpulan '*' — hanya /secret disekat.
        $lain = new RobotRules("User-agent: AI-Bijan\nDisallow: /\n\nUser-agent: *\nDisallow: /secret", 'Googlebot/2.1');

        $this->assertTrue($lain->dibenarkan('/lain'));
        $this->assertFalse($lain->dibenarkan('/secret'));
    }

    public function test_scraper_tolak_url_login_dan_media(): void
    {
        $scraper = app(PublicScraperService::class);

        $this->assertFalse($scraper->dibenarkanUrl('https://example.com/login'));
        $this->assertFalse($scraper->dibenarkanUrl('https://example.com/wp-admin/x'));
        $this->assertFalse($scraper->dibenarkanUrl('https://example.com/gambar.jpg'));
        $this->assertFalse($scraper->dibenarkanUrl('ftp://example.com/ fail'));
        $this->assertTrue($scraper->dibenarkanUrl('https://example.com/produk'));
    }

    // ---------------------------------------------------------------------
    // Import CSV
    // ---------------------------------------------------------------------

    public function test_import_csv_dengan_kolum_biasa(): void
    {
        $this->dalamTenant($this->t);

        $csv = "email,first name,last name,company,job title,consent,consent_source\n";
        $csv .= "ali@sirma1.my,Ahmad,Ali,Sirma Satu,Pengarah,yes,import\n";
        $csv .= "beti@sirma2.my,Beti,Siti,Sirma Dua,Manager,yes,import\n";
        $csv .= "bukan-email,Cahaya,Lan,Sirma Tiga,Staf,no,import\n";

        $path = tempnam(sys_get_temp_dir(), 'csv').'.csv';
        file_put_contents($path, $csv);

        $hasil = app(\App\Modules\Prospecting\Services\CsvImporterService::class)
            ->importFile($path, $this->t->id);

        @unlink($path);

        $this->assertSame(2, $hasil['imported']);
        $this->assertSame(0, $hasil['duplicates']);
        $this->assertSame(1, $hasil['skipped']);

        $this->assertSame(2, Prospect::where('tenant_id', $this->t->id)->count());
        $this->assertSame(2, Prospect::where('tenant_id', $this->t->id)
            ->where('consent_status', Prospect::CONSENT_GRANTED)->count());
    }

    public function test_import_csv_tanpa_kolum_kontak_gagal(): void
    {
        $this->dalamTenant($this->t);

        $path = tempnam(sys_get_temp_dir(), 'csv').'.csv';
        file_put_contents($path, "produk,harga\nBarang A,10\n");

        $hasil = app(\App\Modules\Prospecting\Services\CsvImporterService::class)
            ->importFile($path, $this->t->id);

        @unlink($path);

        $this->assertSame(0, $hasil['imported']);
        $this->assertNotEmpty($hasil['errors']);
    }

    // ---------------------------------------------------------------------
    // Skor
    // ---------------------------------------------------------------------

    public function test_skor_meningkat_dengan_data_lengkap_dan_consent(): void
    {
        $this->dalamTenant($this->t);

        $scoring = app(ProspectScoringService::class);

        $minimal = Prospect::create(['email' => 'min@x.my']);
        $scoring->skor($minimal);

        $penuh = Prospect::create([
            'email' => 'penuh@x.my',
            'phone' => '+60123456789',
            'company' => 'Sirma Penuh',
            'job_title' => 'Pengarah',
            'website' => 'https://sirma.my',
            'linkedin' => 'https://linkedin.com/in/x',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
            'verification_status' => 'verified',
        ]);
        $scoring->skor($penuh);

        $this->assertGreaterThan((int) $minimal->score, (int) $penuh->score);
        $this->assertSame('panas', $scoring->label((int) $penuh->score));
        $this->assertNotEmpty($penuh->score_breakdown);
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_senarai_prospect(): void
    {
        $this->dalamTenant($this->t);

        Prospect::create(['email' => 'api@x.my', 'company' => 'API Sdn Bhd']);

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/prospecting/prospects')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.email', 'api@x.my');
    }

    public function test_api_rekod_consent(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create(['email' => 'consent@x.my']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/prospecting/prospects/{$prospect->id}/consent", [
                'source' => 'borang-web',
                'evidence' => 'https://contoh.my/borang/123',
            ])
            ->assertOk()
            ->assertJsonPath('data.consent_status', Prospect::CONSENT_GRANTED);
    }

    public function test_api_tarik_balik_consent(): void
    {
        $this->dalamTenant($this->t);

        $prospect = Prospect::create([
            'email' => 'tarik@x.my',
            'consent_status' => Prospect::CONSENT_GRANTED,
            'consent_at' => now(),
            'consent_evidence' => 'borang',
        ]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/prospecting/prospects/{$prospect->id}/consent/revoke", [
                'reason' => 'permintaan',
            ])
            ->assertOk()
            ->assertJsonPath('data.consent_status', Prospect::CONSENT_DENIED);
    }

    public function test_api_laporan_consent(): void
    {
        $this->dalamTenant($this->t);

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/prospecting/consent-report')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['jumlah', 'granted', 'denied', 'unknown']]);
    }

    public function test_api_semak_url_robot(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/prospecting/check-url', ['url' => 'https://contoh.my/produk'])
            ->assertOk()
            ->assertJsonPath('data.url_pattern_allowed', true);
    }

    public function test_api_tenant_lain_tidak_nampak_prospect(): void
    {
        $this->dalamTenant($this->t);

        Prospect::create(['email' => 'terasing@x.my']);

        [$u2, $t2] = $this->daftar('pemilik2@prospekt.my', 'Syarikat Kedua');

        $this->sebagai($u2, $t2)
            ->getJson('/api/v1/prospecting/prospects')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
