<?php

namespace Tests\Feature;

use App\Documents\Format;
use App\Documents\Registry;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ContohDokumen;
use Tests\TestCase;
use ZipArchive;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private array $pw = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Referer', config('app.url'));
    }

    private function daftar(string $email, string $syarikat): array
    {
        $this->pw[$email] = 'RahsiaKuat123';
        $this->postJson('/api/v1/auth/register', ['name' => 'Pemilik', 'email' => $email, 'password' => 'RahsiaKuat123', 'company' => $syarikat])->assertCreated();

        return [User::where('email', $email)->first(), Tenant::where('name', $syarikat)->first()];
    }

    private function sebagai(User $u, Tenant $t)
    {
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();
        $this->flushSession();
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => $this->pw[$u->email]])->assertOk();
        $this->app->forgetScopedInstances();

        return $this->withHeaders(['X-Tenant' => $t->ulid]);
    }

    private function jemput(User $owner, Tenant $t, string $email, array $roles): User
    {
        $tok = basename($this->sebagai($owner, $t)->postJson('/api/v1/members/invite', ['email' => $email, 'roles' => $roles])->assertCreated()->json('invite_url'));
        $this->flushSession();
        $this->pw[$email] = 'KataLaluan12345';
        $this->postJson('/api/v1/invitations/accept', ['token' => $tok, 'name' => $email, 'password' => 'KataLaluan12345'])->assertOk();

        return User::where('email', $email)->first();
    }

    private function semakFail(string $fmt, string $body): void
    {
        match ($fmt) {
            'pdf' => $this->assertStringStartsWith('%PDF-', $body),
            'docx' => $this->assertZipAda($body, 'word/document.xml'),
            'xlsx' => $this->assertZipAda($body, 'xl/workbook.xml'),
            'odt', 'ods' => $this->assertZipAda($body, 'content.xml'),
            'csv' => $this->assertStringStartsWith("\xEF\xBB\xBF", $body),
            'html' => $this->assertStringContainsString('<!doctype html>', $body),
            'json' => $this->assertIsArray(json_decode($body, true)),
        };
    }

    private function assertZipAda(string $body, string $entry): void
    {
        $f = tempnam(sys_get_temp_dir(), 'zt');
        file_put_contents($f, $body);
        $z = new ZipArchive;
        $this->assertTrue($z->open($f) === true, 'bukan zip sah');
        $this->assertNotFalse($z->locateName($entry), "tiada $entry");
        $z->close();
        @unlink($f);
    }

    public function test_semua_templat_semua_format_dijana_dengan_struktur_sah(): void
    {
        [$u, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($u, $t)->putJson('/api/v1/tenant/settings', ['settings' => ['company.legal_name' => 'AI Bijan Demo Sdn. Bhd.', 'company.reg_no' => '202501000001 (1600001-X)', 'company.sst_no' => 'W10-1808-32000001', 'company.address' => "No. 1, Jalan Bunga\n43000 Kajang, Selangor", 'company.phone' => '03-8700 0000']])->assertOk();
        $out = storage_path('app/contoh-dokumen');
        @mkdir($out, 0775, true);
        $bil = 0;
        foreach (Registry::all() as $key => $tpl) {
            foreach (['ms', 'en'] as $lang) {
                $r = $this->postJson('/api/v1/documents', ['type' => $key, 'data' => ContohDokumen::untuk($key), 'locale' => $lang, 'theme' => ['neutral', 'korporat', 'moden', 'ringkas'][$bil % 4]]);
                $r->assertCreated();
                $ulid = $r->json('ulid');
                foreach ($tpl->formats() as $fmt) {
                    $res = $this->get("/api/v1/documents/$ulid/download/$fmt");
                    $res->assertOk();
                    $body = $res->getContent();
                    $this->semakFail($fmt, $body);
                    $this->assertStringNotContainsString('900101145678', $body, "$key.$fmt: No. KP penuh terdedah");
                    $this->assertStringNotContainsString('900101145678', $res->headers->get('Content-Disposition'));
                    if ($lang === 'ms') {
                        preg_match('/filename="([^"]+)"/', $res->headers->get('Content-Disposition'), $mm);
                        file_put_contents("$out/".$mm[1], $body);
                    }
                    $bil++;
                }
            }
        }
        $this->assertGreaterThan(200, $bil);
    }

    public function test_format_tidak_sesuai_ditolak_dan_nombor_siri_berturutan(): void
    {
        [$u, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $a = $this->sebagai($u, $t)->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->json();
        $b = $this->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->json();
        $this->assertSame('INV-'.date('Y').'-0001', $a['number']);
        $this->assertSame('INV-'.date('Y').'-0002', $b['number']);
        $s = $this->postJson('/api/v1/documents', ['type' => 'slip_gaji', 'data' => ContohDokumen::untuk('slip_gaji')])->json();
        $this->get('/api/v1/documents/'.$s['ulid'].'/download/xlsx')->assertStatus(422); // slip gaji: PDF/DOCX sahaja (V3 §11.5)
        $this->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ['party' => ['name' => 'x']]])->assertStatus(422);
    }

    public function test_slip_gaji_hanya_pekerja_itu_dan_hr(): void
    {
        [$owner, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $nurul = $this->jemput($owner, $t, 'nurul@kedai-a.my', ['staff']);
        $ali = $this->jemput($owner, $t, 'ali@kedai-a.my', ['staff']);
        $admin = $this->jemput($owner, $t, 'admin@kedai-a.my', ['tenant_admin']);
        $hr = $this->jemput($owner, $t, 'hr@kedai-a.my', ['hr_manager']);
        $slip = $this->sebagai($hr, $t)->postJson('/api/v1/documents', ['type' => 'slip_gaji', 'data' => ContohDokumen::untuk('slip_gaji'), 'subject_user_id' => $nurul->id])->assertCreated()->json('ulid');
        $this->sebagai($nurul, $t)->get("/api/v1/documents/$slip/download/pdf")->assertOk();
        $this->assertCount(1, $this->getJson('/api/v1/documents')->json('data'));
        $this->sebagai($ali, $t)->get("/api/v1/documents/$slip/download/pdf")->assertForbidden();
        $this->assertCount(0, $this->getJson('/api/v1/documents')->json('data'));
        $this->sebagai($admin, $t)->get("/api/v1/documents/$slip/download/pdf")->assertForbidden(); // pentadbir umum bukan HR
        $this->postJson('/api/v1/documents', ['type' => 'slip_gaji', 'data' => ContohDokumen::untuk('slip_gaji')])->assertForbidden();
        $this->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->assertCreated(); // tapi boleh invois
        $this->sebagai($nurul, $t)->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->assertForbidden();
        $this->assertTrue(AuditLog::withoutGlobalScope('tenant')->where('action', 'documents.downloaded')->exists());
    }

    public function test_dokumen_tenant_lain_tidak_boleh_diakses(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $inv = $this->sebagai($ub, $tb)->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->json('ulid');
        $this->sebagai($ua, $ta)->get("/api/v1/documents/$inv/download/pdf")->assertNotFound();
        $this->getJson("/api/v1/documents/$inv")->assertNotFound();
        $this->assertCount(0, $this->getJson('/api/v1/documents')->json('data'));
        // Nombor siri berasingan setiap tenant
        $this->assertSame('INV-'.date('Y').'-0001', $this->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->json('number'));
    }

    public function test_batal_dokumen_dan_kandungan_tidak_boleh_diubah(): void
    {
        [$u, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $inv = $this->sebagai($u, $t)->postJson('/api/v1/documents', ['type' => 'invois', 'data' => ContohDokumen::untuk('invois')])->json('ulid');
        $this->postJson("/api/v1/documents/$inv/void", ['reason' => 'Salah alamat pelanggan'])->assertOk();
        $this->postJson("/api/v1/documents/$inv/void", ['reason' => 'lagi sekali'])->assertStatus(409);
        $res = $this->get("/api/v1/documents/$inv/download/html");
        $this->assertStringContainsString('DIBATALKAN', $res->getContent());
        $this->assertStringContainsString('_DIBATALKAN.html', $res->headers->get('Content-Disposition'));
        app(TenantContext::class)->set($t);
        $d = Document::first();
        $d->data = ['input' => [], 'issuer' => []];
        $this->expectException(\LogicException::class);
        $d->save();
    }

    public function test_pratonton_tidak_simpan_dan_html_dilindungi_csp(): void
    {
        [$u, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $data = ContohDokumen::untuk('invois');
        $data['party']['name'] = '<script>alert(1)</script>';
        $r = $this->sebagai($u, $t)->post('/api/v1/documents/preview', ['type' => 'invois', 'data' => $data]);
        $r->assertOk();
        $this->assertStringNotContainsString('<script>', $r->getContent());
        $this->assertStringContainsString("default-src 'none'", $r->headers->get('Content-Security-Policy'));
        $this->assertSame(0, $this->getJson('/api/v1/documents')->json('total'));
    }

    public function test_amaun_dalam_perkataan_bm_dan_en(): void
    {
        $this->assertSame('Ringgit Malaysia Empat ribu dua ratus sembilan puluh satu dan empat puluh sen sahaja', Format::words(4291.40));
        $this->assertSame('Ringgit Malaysia Seribu seratus sebelas sahaja', Format::words(1111));
        $this->assertSame('Ringgit Malaysia Dua juta tiga ratus ribu sahaja', Format::words(2300000));
        $this->assertSame('Ringgit Malaysia Sebelas dan lima sen sahaja', Format::words(11.05));
        $this->assertSame('Ringgit Malaysia Four thousand two hundred and ninety-one and forty sen only', Format::words(4291.40, 'en'));
        $this->assertSame('••••••••5678', Format::mask('900101145678'));
        $this->assertSame('-RM1,234.50', Format::rm(-1234.5));
        $this->assertSame('15 Ogos 2026', Format::date('2026-08-15'));
    }
}
