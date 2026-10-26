<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\Totp;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Fasa 1: platform teras. Fokus: pengasingan tenant, RBAC, audit, 2FA, akses sokongan. */
class PlatformCoreTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // SPA pihak pertama (Sanctum stateful): permintaan dari asal aplikasi sendiri.
        $this->withHeader('Referer', config('app.url'));
    }

    private function daftar(string $email, string $syarikat): array
    {
        $this->pw[$email] = 'RahsiaKuat123';
        $r = $this->postJson('/api/v1/auth/register', ['name' => 'Pemilik '.$syarikat, 'email' => $email, 'password' => 'RahsiaKuat123', 'company' => $syarikat]);
        $r->assertCreated();
        $u = User::where('email', $email)->first();
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();

        return [$u, Tenant::where('name', $syarikat)->first()];
    }

    /** Kata laluan pengguna ujian (untuk log masuk sebenar melalui API). */
    private array $pw = [];

    /** Log masuk sebenar (sesi SPA) sebagai pengguna ini, kemudian pilih tenant melalui X-Tenant. */
    private function sebagai(User $u, ?Tenant $t = null)
    {
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();
        $this->flushSession();
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => $this->pw[$u->email]])->assertOk();
        $this->app->forgetScopedInstances();

        return $this->withHeaders(['X-Tenant' => $t?->ulid ?? '']);
    }

    public function test_daftar_syarikat_cipta_tenant_peranan_dan_audit(): void
    {
        [$u, $t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->assertSame('trial', $t->status);
        $r = $this->sebagai($u, $t)->getJson('/api/v1/tenant')->assertOk();
        $this->assertContains('roles.manage', $r->json('permissions'));
        $this->assertSame(['tenant_owner'], $r->json('roles'));
        $this->assertSame(1, AuditLog::withoutGlobalScope('tenant')->where('tenant_id', $t->id)->where('action', 'tenant.created')->count());
    }

    public function test_pengguna_tenant_a_tidak_boleh_masuk_tenant_b(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $rr = $this->sebagai($ua, $tb)->getJson('/api/v1/tenant');
        $rr->assertForbidden();
        $this->sebagai($ua, $tb)->getJson('/api/v1/members')->assertForbidden();
        $this->sebagai($ua)->postJson('/api/v1/auth/tenant', ['tenant' => $tb->ulid])->assertForbidden();
        // Tanpa X-Tenant: jatuh ke tenant sendiri sahaja
        $this->assertSame($ta->ulid, $this->sebagai($ua)->getJson('/api/v1/tenant')->json('tenant.ulid'));
    }

    public function test_senarai_ahli_audit_dan_tetapan_hanya_tenant_sendiri(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $this->sebagai($ub, $tb)->putJson('/api/v1/tenant/settings', ['settings' => ['resit.nota' => 'RAHSIA-B']])->assertOk();
        $ahli = $this->sebagai($ua, $ta)->getJson('/api/v1/members')->assertOk()->json();
        $this->assertSame(['a@kedai-a.my'], array_column($ahli, 'email'));
        $this->assertStringNotContainsString('RAHSIA-B', $this->sebagai($ua, $ta)->getJson('/api/v1/tenant/settings')->getContent());
        $audit = $this->sebagai($ua, $ta)->getJson('/api/v1/audit')->assertOk()->getContent();
        $this->assertStringNotContainsString('RAHSIA-B', $audit);
        $this->assertStringNotContainsString('b@kedai-b.my', $audit);
    }

    public function test_tukar_id_dalam_url_tidak_beri_akses_rekod_tenant_lain(): void
    {
        Storage::fake('local');
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $fail = $this->sebagai($ub, $tb)->postJson('/api/v1/files', ['file' => UploadedFile::fake()->create('gaji-b.pdf', 20, 'application/pdf')])->assertCreated()->json('ulid');
        $this->sebagai($ua, $ta)->get('/api/v1/files/'.$fail)->assertNotFound();
        $this->sebagai($ub, $tb)->get('/api/v1/files/'.$fail)->assertOk();
        $ahliB = TenantUser::withoutGlobalScope('tenant')->where('tenant_id', $tb->id)->value('id');
        $this->sebagai($ua, $ta)->putJson("/api/v1/members/$ahliB", ['status' => 'disabled'])->assertNotFound();
        $this->assertSame('active', TenantUser::withoutGlobalScope('tenant')->find($ahliB)->status);
    }

    public function test_skop_model_tanpa_tenant_tiada_rekod_dan_tidak_boleh_cipta(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->app->forgetScopedInstances();
        $this->assertSame(0, TenantUser::count());
        $this->expectException(\RuntimeException::class);
        TenantUser::create(['user_id' => $ua->id, 'status' => 'active']);
    }

    public function test_tenant_id_tidak_boleh_ditukar(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        app(TenantContext::class)->set($ta);
        $m = TenantUser::first();
        $m->tenant_id = $tb->id;
        $this->expectException(\RuntimeException::class);
        $m->save();
    }

    public function test_rbac_jemput_staf_dan_staf_disekat(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $r = $this->sebagai($ua, $ta)->postJson('/api/v1/members/invite', ['email' => 'staf@kedai-a.my', 'roles' => ['staff']])->assertCreated();
        $token = basename($r->json('invite_url'));
        $this->assertSame(64, strlen($token));
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();
        $this->pw['staf@kedai-a.my'] = 'StafKuat12345';
        $this->postJson('/api/v1/invitations/accept', ['token' => $token, 'name' => 'Staf Satu', 'password' => 'StafKuat12345'])->assertOk();
        $this->flushSession();
        $this->postJson('/api/v1/invitations/accept', ['token' => $token, 'name' => 'x', 'password' => 'StafKuat12345'])->assertNotFound();
        // Jemputan tanpa kata laluan tidak boleh log masuk sebelum diterima
        $this->sebagai($ua, $ta)->postJson('/api/v1/members/invite', ['email' => 'baru@kedai-a.my', 'roles' => ['staff']])->assertCreated();
        $this->flushSession();
        foreach (['', 'password', '!'] as $cuba) {
            $this->postJson('/api/v1/auth/login', ['email' => 'baru@kedai-a.my', 'password' => $cuba])->assertStatus(422);
        }
        $staf = User::where('email', 'staf@kedai-a.my')->first();
        $this->sebagai($staf, $ta)->getJson('/api/v1/tenant')->assertOk();
        $this->sebagai($staf, $ta)->getJson('/api/v1/members')->assertForbidden();
        $this->sebagai($staf, $ta)->postJson('/api/v1/members/invite', ['email' => 'x@y.my', 'roles' => ['tenant_owner']])->assertForbidden();
        $this->sebagai($staf, $ta)->getJson('/api/v1/audit')->assertForbidden();
        $this->sebagai($staf, $ta)->putJson('/api/v1/tenant/settings', ['settings' => ['a' => 1]])->assertForbidden();
    }

    public function test_pentadbir_tak_boleh_lantik_pemilik_dan_pemilik_terakhir_dilindungi(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $r = $this->sebagai($ua, $ta)->postJson('/api/v1/members/invite', ['email' => 'admin@kedai-a.my', 'roles' => ['tenant_admin']])->assertCreated();
        $this->pw['admin@kedai-a.my'] = 'AdminKuat12345';
        $this->flushSession();
        $this->postJson('/api/v1/invitations/accept', ['token' => basename($r->json('invite_url')), 'name' => 'Admin', 'password' => 'AdminKuat12345'])->assertOk();
        $admin = User::where('email', 'admin@kedai-a.my')->first();
        $this->sebagai($admin, $ta)->postJson('/api/v1/members/invite', ['email' => 'p2@kedai-a.my', 'roles' => ['tenant_owner']])->assertForbidden();
        $pemilikId = TenantUser::withoutGlobalScope('tenant')->where('user_id', $ua->id)->value('id');
        $this->sebagai($admin, $ta)->putJson("/api/v1/members/$pemilikId", ['roles' => ['staff']])->assertForbidden();
        $this->sebagai($ua, $ta)->putJson("/api/v1/members/$pemilikId", ['roles' => ['staff']])->assertStatus(422);
    }

    public function test_audit_tidak_boleh_diubah_atau_dipadam(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        app(TenantContext::class)->set($ta);
        $log = AuditLog::first();
        $this->assertNotNull($log);
        try {
            $log->update(['action' => 'palsu']);
            $this->fail('Audit boleh diubah');
        } catch (\LogicException) {
        }
        $this->expectException(\LogicException::class);
        $log->delete();
    }

    public function test_audit_tidak_simpan_rahsia(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($ua, $ta)->postJson('/api/v1/api-keys', ['name' => 'App', 'scopes' => ['products.read']])->assertCreated();
        $kunci = $this->sebagai($ua, $ta)->postJson('/api/v1/api-keys', ['name' => 'App2', 'scopes' => ['stock.read']])->json('key');
        $this->assertStringStartsWith('aib_', $kunci);
        $semua = AuditLog::withoutGlobalScope('tenant')->get()->toJson();
        $this->assertStringNotContainsString($kunci, $semua);
        $this->assertStringNotContainsString('RahsiaKuat123', $semua);
        $this->assertStringNotContainsString($kunci, $this->sebagai($ua, $ta)->getJson('/api/v1/api-keys')->getContent());
    }

    public function test_log_masuk_salah_dihadkan(): void
    {
        $this->daftar('a@kedai-a.my', 'Kedai A');
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'a@kedai-a.my', 'password' => 'salah'])->assertStatus(422);
        }
        $this->postJson('/api/v1/auth/login', ['email' => 'a@kedai-a.my', 'password' => 'RahsiaKuat123'])->assertStatus(429);
    }

    public function test_super_admin_wajib_2fa_dan_tiada_data_tenant_tanpa_geran(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($ua, $ta)->putJson('/api/v1/tenant/settings', ['settings' => ['rahsia' => 'DATA-A']]);
        $adm = User::create(['name' => 'Admin Platform', 'email' => 'root@aibijan.my', 'password' => 'AdminPlatform123'])->refresh();
        $adm->forceFill(['is_platform_admin' => true])->save();
        $this->pw['root@aibijan.my'] = 'AdminPlatform123';
        // Tanpa 2FA: ditolak
        $this->sebagai($adm)->getJson('/api/v1/admin/tenants')->assertForbidden();
        // Aktifkan 2FA (sesi log masuk sedia ada)
        $s = $this->postJson('/api/v1/auth/2fa/setup')->assertOk()->json('secret');
        $this->postJson('/api/v1/auth/2fa/confirm', ['code' => '000000'])->assertStatus(422);
        $this->postJson('/api/v1/auth/2fa/confirm', ['code' => Totp::code($s)])->assertOk();
        // Log masuk semula perlukan kod
        auth()->forgetGuards();
        $this->flushSession();
        $this->postJson('/api/v1/auth/login', ['email' => 'root@aibijan.my', 'password' => 'AdminPlatform123'])->assertStatus(202)->assertJson(['two_factor' => true]);
        $this->postJson('/api/v1/auth/login', ['email' => 'root@aibijan.my', 'password' => 'AdminPlatform123', 'code' => Totp::code($s)])->assertOk();
        $senarai = $this->getJson('/api/v1/admin/tenants')->assertOk();
        $this->assertStringNotContainsString('DATA-A', $senarai->getContent());
        // Tiada geran: tak boleh baca tetapan tenant
        $this->withHeaders(['X-Tenant' => $ta->ulid])->getJson('/api/v1/tenant/settings')->assertForbidden();
        // Geran bersebab → baca sahaja, dilog
        $g = $this->postJson("/api/v1/admin/tenants/{$ta->id}/support-access", ['reason' => 'Tiket #12: semak tetapan resit', 'minutes' => 30])->assertCreated()->json('id');
        $this->app->forgetScopedInstances();
        $this->withHeaders(['X-Tenant' => $ta->ulid])->getJson('/api/v1/tenant/settings')->assertOk()->assertJson(['rahsia' => 'DATA-A']);
        $this->app->forgetScopedInstances();
        $this->withHeaders(['X-Tenant' => $ta->ulid])->putJson('/api/v1/tenant/settings', ['settings' => ['rahsia' => 'UBAH']])->assertForbidden();
        $this->assertTrue(AuditLog::withoutGlobalScope('tenant')->where('tenant_id', $ta->id)->where('action', 'support.access')->exists());
        $this->assertTrue(AuditLog::withoutGlobalScope('tenant')->where('tenant_id', $ta->id)->where('action', 'support.granted')->exists());
        // Batal geran → ditolak semula
        $this->app->forgetScopedInstances();
        $this->deleteJson("/api/v1/admin/support-access/$g")->assertOk();
        $this->app->forgetScopedInstances();
        $this->withHeaders(['X-Tenant' => $ta->ulid])->getJson('/api/v1/tenant/settings')->assertForbidden();
    }

    public function test_tenant_digantung_tidak_boleh_digunakan(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $ta->update(['status' => 'suspended']);
        $this->sebagai($ua, $ta)->getJson('/api/v1/tenant')->assertForbidden();
    }

    public function test_muat_naik_jenis_berbahaya_ditolak(): void
    {
        Storage::fake('local');
        [$ua, $ta] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($ua, $ta)->postJson('/api/v1/files', ['file' => UploadedFile::fake()->create('jahat.php', 1, 'application/x-php')])->assertStatus(422);
        $this->sebagai($ua, $ta)->postJson('/api/v1/files', ['file' => UploadedFile::fake()->create('besar.pdf', 20000, 'application/pdf')])->assertStatus(422);
    }

    public function test_totp_vektor_rfc6238(): void
    {
        // Vektor ujian RFC 6238 (SHA1): rahsia "12345678901234567890", T=59 → 94287082 (6 digit terakhir: 287082)
        $b32 = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $this->assertSame('287082', Totp::code($b32, 59));
        $this->assertSame('081804', Totp::code($b32, 1111111109));
    }
}
