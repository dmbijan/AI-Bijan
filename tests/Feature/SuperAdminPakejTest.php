<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\TokenPackage;
use App\Models\User;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Services\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Harga & pakej langganan diurus Super Admin (keputusan owner 31/10/2026); sistem cuma semai pakej contoh. */
class SuperAdminPakejTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    /** Log masuk sebagai Super Admin dengan 2FA (wajib untuk /admin). */
    private function superAdmin(): void
    {
        $adm = User::create(['name' => 'Admin Platform', 'email' => 'root@aibijan.my', 'password' => 'AdminPlatform123'])->refresh();
        $adm->forceFill(['is_platform_admin' => true])->save();
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();
        $this->flushSession();
        $this->postJson('/api/v1/auth/login', ['email' => 'root@aibijan.my', 'password' => 'AdminPlatform123'])->assertOk();
        $s = $this->postJson('/api/v1/auth/2fa/setup')->assertOk()->json('secret');
        $this->postJson('/api/v1/auth/2fa/confirm', ['code' => Totp::code($s)])->assertOk();
    }

    #[Test]
    public function pakej_contoh_disemai(): void
    {
        $this->assertTrue(Plan::where('code', 'starter')->exists());
        $this->assertTrue(Plan::where('code', 'growth')->exists());
        $this->assertTrue(Plan::where('code', 'enterprise')->exists());
        $this->assertGreaterThan(0, TokenPackage::count());
        $this->assertStringContainsString('contoh', Plan::where('code', 'growth')->value('description'));
    }

    #[Test]
    public function super_admin_ubah_harga_dan_billing_ikut_harga_baharu(): void
    {
        $this->superAdmin();
        $growth = Plan::where('code', 'growth')->first();

        $this->putJson("/api/v1/admin/plans/{$growth->id}", [
            'code' => 'growth', 'name' => 'Growth Plus', 'price_monthly' => 299, 'price_yearly' => 2990,
            'features' => ['POS', 'CRM'], 'limits' => [['limit_key' => 'seats', 'name' => 'Pengguna', 'limit_value' => 50, 'period' => 'total']],
        ])->assertOk()->assertJsonPath('name', 'Growth Plus');

        $this->assertSame(299.0, app(BillingService::class)->hargaBulanan('growth'));
        $this->assertSame(50, (int) $growth->limits()->where('limit_key', 'seats')->value('limit_value'));
    }

    #[Test]
    public function harga_admin_tidak_ditulis_ganti_bila_syarikat_baharu_daftar(): void
    {
        $this->superAdmin();
        $growth = Plan::where('code', 'growth')->first();
        $this->putJson("/api/v1/admin/plans/{$growth->id}", ['code' => 'growth', 'name' => 'Growth', 'price_monthly' => 199])->assertOk();

        // Dulu: setiap pendaftaran syarikat memanggil janaPlanLalai() yang menetapkan semula harga.
        $this->flushSession();
        auth()->forgetGuards();
        $this->daftar('baru@kedai.my', 'Kedai Baru');
        app(EntitlementService::class)->janaPlanLalai();

        $this->assertSame(199.0, (float) $growth->fresh()->price_monthly);
    }

    #[Test]
    public function super_admin_cipta_pakej_baharu_dan_pakej_token(): void
    {
        $this->superAdmin();

        $this->postJson('/api/v1/admin/plans', ['code' => 'kedai-bunga', 'name' => 'Kedai Bunga', 'price_monthly' => 150, 'modules' => ['pos', 'inventory']])->assertCreated();
        $this->assertSame(150.0, app(BillingService::class)->hargaBulanan('kedai-bunga'));

        $this->postJson('/api/v1/admin/token-packages', ['code' => 't10m', 'name' => '10 Juta token', 'tokens' => 10_000_000, 'price' => 12, 'supplier_cost' => 2])->assertCreated();
        $senarai = $this->getJson('/api/v1/admin/token-packages')->assertOk()->json();
        $baru = collect($senarai)->firstWhere('code', 't10m');
        $this->assertEquals(83.3, $baru['margin_pct']);
        $this->assertArrayHasKey('t10m', app(BillingService::class)->pakejToken());
        $this->assertSame(12.0, app(BillingService::class)->hargaToken('t10m'));

        $this->postJson('/api/v1/admin/plans', ['code' => 'kedai-bunga', 'name' => 'Pendua', 'price_monthly' => 1])->assertStatus(422);
    }

    #[Test]
    public function pakej_dimatikan_tidak_boleh_dilanggan(): void
    {
        $this->superAdmin();
        $id = TokenPackage::where('code', 't60m')->value('id');
        $this->putJson("/api/v1/admin/token-packages/{$id}", ['code' => 't60m', 'name' => '60 Juta token', 'tokens' => 60_000_000, 'price' => 55, 'is_active' => false])->assertOk();

        $this->assertArrayNotHasKey('t60m', app(BillingService::class)->pakejToken());
    }

    #[Test]
    public function kos_pembekal_tidak_didedahkan_kepada_pelanggan(): void
    {
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga');
        $r = $this->sebagai($u, $t)->getJson('/api/v1/billing/packages')->assertOk();

        $this->assertStringNotContainsString('kos_pembekal', $r->getContent());
        $this->assertStringNotContainsString('supplier_cost', $r->getContent());
    }

    #[Test]
    public function bukan_super_admin_ditolak(): void
    {
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga');

        $this->sebagai($u, $t)->getJson('/api/v1/admin/plans')->assertForbidden();
        $this->sebagai($u, $t)->postJson('/api/v1/admin/token-packages', ['code' => 'x', 'name' => 'x', 'tokens' => 1, 'price' => 1])->assertForbidden();
    }
}
