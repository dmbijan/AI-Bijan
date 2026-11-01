<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** OAM: struktur organisasi, seat, warisan & tolak, cegah naik pangkat, gantung/tarik balik, skop & had. */
class OrganizationAccessTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('owner@abc.my', 'ABC Holdings');
        $this->sebagai($this->u, $this->t);
    }

    private function ahli(string $email): TenantUser
    {
        $this->dalamTenant($this->t);

        return TenantUser::where('user_id', User::where('email', $email)->value('id'))->first();
    }

    #[Test]
    public function struktur_kumpulan_syarikat_cawangan_jabatan_dengan_peraturan_induk(): void
    {
        $g = $this->postJson('/api/v1/org/units', ['type' => 'group', 'name' => 'ABC Holdings'])->assertCreated()->json('data.id');
        $c = $this->postJson('/api/v1/org/units', ['type' => 'company', 'name' => 'ABC Restaurant Sdn Bhd', 'parent_id' => $g, 'registration_number' => '202401000001'])->assertCreated()->json('data.id');
        $b = $this->postJson('/api/v1/org/units', ['type' => 'branch', 'name' => 'Shah Alam', 'parent_id' => $c, 'code' => 'SA'])->assertCreated()->json('data.id');
        $d = $this->postJson('/api/v1/org/units', ['type' => 'department', 'name' => 'Operasi', 'parent_id' => $b])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/org/units', ['type' => 'team', 'name' => 'Dapur', 'parent_id' => $d])->assertCreated();

        $this->postJson('/api/v1/org/units', ['type' => 'branch', 'name' => 'Salah', 'parent_id' => $g])->assertStatus(422);
        $this->postJson('/api/v1/org/units', ['type' => 'branch', 'name' => 'Kod sama', 'parent_id' => $c, 'code' => 'SA'])->assertStatus(422);
        // Pakej percubaan: 1 lokasi sahaja.
        $this->postJson('/api/v1/org/units', ['type' => 'branch', 'name' => 'Klang', 'parent_id' => $c])->assertStatus(409);
        // Kitaran: jabatan tak boleh jadi induk kepada dirinya.
        $d2 = $this->postJson('/api/v1/org/units', ['type' => 'department', 'name' => 'Sub', 'parent_id' => $d])->assertCreated()->json('data.id');
        $this->putJson("/api/v1/org/units/{$d}", ['parent_id' => $d2])->assertStatus(422);

        $this->postJson("/api/v1/org/units/{$b}/archive")->assertStatus(422); // masih ada unit aktif di bawah
        $o = $this->getJson('/api/v1/org/overview')->assertOk()->json('data');
        $this->assertSame(1, $o['units']['company']);
        $this->assertSame(1, $o['units']['branch']);
    }

    #[Test]
    public function seat_penuh_jemputan_tidak_boleh_diterima(): void
    {
        $s = $this->getJson('/api/v1/org/seats')->assertOk()->json('data');
        $this->assertSame(5, $s['included']);
        $this->assertSame(1, $s['used']);
        foreach (range(1, 4) as $i) {
            $this->jemput($this->u, $this->t, "s{$i}@abc.my", ['staff']);
        }
        $tok = basename($this->sebagai($this->u, $this->t)->postJson('/api/v1/members/invite', ['email' => 'lebih@abc.my', 'roles' => ['staff']])->assertCreated()->json('invite_url'));
        $this->assertSame(1, $this->getJson('/api/v1/org/seats')->json('data.pending_invites'));
        $this->assertSame(0, $this->getJson('/api/v1/org/seats')->json('data.available'));
        $this->flushSession();
        $this->postJson('/api/v1/invitations/accept', ['token' => $tok, 'name' => 'Lebih', 'password' => 'KataLaluan12345'])->assertStatus(409);

        // Gantung seorang → seat terbuka.
        $this->sebagai($this->u, $this->t)->postJson('/api/v1/org/members/'.$this->ahli('s1@abc.my')->id.'/suspend', ['reason' => 'Cuti panjang'])->assertOk();
        $this->flushSession();
        $this->postJson('/api/v1/invitations/accept', ['token' => $tok, 'name' => 'Lebih', 'password' => 'KataLaluan12345'])->assertOk();
        // Aktifkan semula ditolak kerana penuh.
        $this->sebagai($this->u, $this->t)->postJson('/api/v1/org/members/'.$this->ahli('s1@abc.my')->id.'/reactivate')->assertStatus(409);
    }

    #[Test]
    public function warisan_peranan_dan_kebenaran_ditolak_menang(): void
    {
        $this->dalamTenant($this->t);
        $kasyer = Role::where('key', 'cashier')->first();
        $id = $this->postJson('/api/v1/roles', ['name' => 'Juruwang Kanan', 'parent_id' => $kasyer->id, 'permissions' => ['inventory.view'], 'denies' => ['pos.manage'],
            'limits' => ['pos.discount_amount' => 50]])->assertCreated()->json('id');
        $r = collect($this->getJson('/api/v1/roles')->json('roles'))->firstWhere('id', $id);
        $this->assertContains('inventory.view', $r['effective']);
        $this->assertContains('pos.view', $r['effective']); // diwarisi
        $this->assertNotContains('pos.manage', $r['effective']);
        // Kitaran ditolak.
        $this->putJson("/api/v1/roles/{$kasyer->id}", ['name' => $kasyer->name, 'parent_id' => $id])->assertStatus(422);

        $staf = $this->jemput($this->u, $this->t, 'kanan@abc.my', ['cashier']);
        $m = $this->ahli('kanan@abc.my');
        $this->sebagai($this->u, $this->t)->putJson("/api/v1/members/{$m->id}", ['roles' => ['juruwang_kanan']])->assertOk();
        $c = $this->postJson('/api/v1/org/access/check', ['member_id' => $m->id, 'permission' => 'pos.view', 'amount' => 80, 'limit_key' => 'pos.discount_amount'])->json('data');
        $this->assertFalse($c['allowed']);
        $this->assertSame('limit', $c['step']);
        $this->assertTrue($this->postJson('/api/v1/org/access/check', ['member_id' => $m->id, 'permission' => 'pos.view', 'amount' => 40, 'limit_key' => 'pos.discount_amount'])->json('data.allowed'));
        $this->assertSame('permission', $this->postJson('/api/v1/org/access/check', ['member_id' => $m->id, 'permission' => 'pos.manage'])->json('data.step'));
        $this->sebagai($staf, $this->t)->getJson('/api/v1/org/members')->assertForbidden();
    }

    #[Test]
    public function pentadbir_tidak_boleh_beri_kebenaran_yang_dia_tiada(): void
    {
        $admin = $this->jemput($this->u, $this->t, 'admin@abc.my', ['tenant_admin']);
        $this->jemput($this->u, $this->t, 'biasa@abc.my', ['staff']);
        $m = $this->ahli('biasa@abc.my');
        $http = $this->sebagai($admin, $this->t);
        $http->putJson("/api/v1/members/{$m->id}", ['roles' => ['finance_manager']])->assertForbidden();
        $http->postJson('/api/v1/members/invite', ['email' => 'kewangan@abc.my', 'roles' => ['finance_manager']])->assertForbidden();
        $http->putJson("/api/v1/members/{$m->id}", ['roles' => ['cashier']])->assertOk();
        // Tak boleh ubah peranan diri sendiri.
        $http->putJson('/api/v1/members/'.$this->ahli('admin@abc.my')->id, ['roles' => ['tenant_admin', 'finance_manager']])->assertForbidden();
    }

    #[Test]
    public function gantung_tarik_balik_dan_tamat_tempoh_menyekat_akses(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'ali@abc.my', ['staff']);
        $m = $this->ahli('ali@abc.my');
        $this->sebagai($this->u, $this->t)->postJson("/api/v1/org/members/{$m->id}/suspend", ['reason' => 'Siasatan'])->assertOk()->assertJsonPath('data.state', 'suspended');
        $this->sebagai($staf, $this->t)->getJson('/api/v1/org/sessions')->assertForbidden();

        $this->sebagai($this->u, $this->t)->postJson("/api/v1/org/members/{$m->id}/reactivate")->assertOk();
        $this->sebagai($staf, $this->t)->getJson('/api/v1/org/sessions')->assertOk();

        $this->sebagai($this->u, $this->t)->putJson("/api/v1/org/members/{$m->id}/scope", ['data_scope' => 'all', 'expires_at' => now()->addDays(3)->toDateString()])->assertOk();
        DB::table('tenant_users')->where('id', $m->id)->update(['expires_at' => now()->subMinute()]);
        $this->sebagai($staf, $this->t)->getJson('/api/v1/org/sessions')->assertForbidden();

        $this->sebagai($this->u, $this->t)->postJson("/api/v1/org/members/{$m->id}/revoke")->assertOk()->assertJsonPath('data.state', 'revoked');
        $this->postJson("/api/v1/org/members/{$m->id}/reactivate")->assertStatus(422);

        // Pemilik tak boleh gantung diri sendiri.
        $this->postJson('/api/v1/org/members/'.$this->ahli('owner@abc.my')->id.'/suspend')->assertStatus(422);
        $this->assertTrue(collect($this->getJson('/api/v1/org/members')->json('data'))->contains('state', 'revoked'));
    }

    #[Test]
    public function skop_data_unit_ditolak_di_luar_cawangan(): void
    {
        $c = $this->postJson('/api/v1/org/units', ['type' => 'company', 'name' => 'ABC Retail'])->json('data.id');
        $b = $this->postJson('/api/v1/org/units', ['type' => 'branch', 'name' => 'Johor', 'parent_id' => $c])->json('data.id');
        $d = $this->postJson('/api/v1/org/units', ['type' => 'department', 'name' => 'Jualan', 'parent_id' => $b])->json('data.id');
        $c2 = $this->postJson('/api/v1/org/units', ['type' => 'company', 'name' => 'ABC Services'])->json('data.id');
        $this->jemput($this->u, $this->t, 'pengurus@abc.my', ['inventory_manager']);
        $m = $this->ahli('pengurus@abc.my');
        $this->sebagai($this->u, $this->t)->putJson("/api/v1/org/members/{$m->id}/scope", ['org_unit_id' => $b, 'data_scope' => 'unit_tree'])->assertOk();
        $e = $this->getJson("/api/v1/org/members/{$m->id}/effective")->json('data');
        $this->assertEqualsCanonicalizing([$b, $d], $e['visible_unit_ids']);
        $this->assertTrue($this->postJson('/api/v1/org/access/check', ['member_id' => $m->id, 'permission' => 'inventory.view', 'org_unit_id' => $d])->json('data.allowed'));
        $this->assertSame('data_scope', $this->postJson('/api/v1/org/access/check', ['member_id' => $m->id, 'permission' => 'inventory.view', 'org_unit_id' => $c2])->json('data.step'));
    }

    #[Test]
    public function unit_terasing_antara_tenant_dan_sesi_sendiri_sahaja(): void
    {
        $this->postJson('/api/v1/org/units', ['type' => 'company', 'name' => 'Rahsia'])->assertCreated();
        $this->getJson('/api/v1/org/sessions')->assertOk();
        [$u2, $t2] = $this->daftar('lain@xyz.my', 'XYZ');
        $this->sebagai($u2, $t2);
        $this->assertCount(0, $this->getJson('/api/v1/org/units')->json('data'));
        $this->putJson('/api/v1/org/units/1', ['name' => 'Curi'])->assertNotFound();
    }

    #[Test]
    public function juruwang_boleh_checkout_tapi_tidak_urus_kaunter(): void
    {
        $r = $this->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1', 'code' => 'K1'])->assertCreated();
        $reg = $r->json('id') ?? $r->json('data.id');
        $kasyer = $this->jemput($this->u, $this->t, 'kasyer@abc.my', ['cashier']);
        $http = $this->sebagai($kasyer, $this->t);
        $sale = $http->postJson('/api/v1/pos/sales/checkout', ['register_id' => $reg, 'items' => [['product_name' => 'Ros', 'quantity' => 1, 'unit_price' => 5]], 'tenders' => [['method' => 'cash', 'amount' => 5]]])
            ->assertCreated()->json('data.id');
        $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 2', 'code' => 'K2'])->assertForbidden();
        $http->postJson("/api/v1/pos/sales/{$sale}/refund", ['reason' => 'x'])->assertForbidden();
    }
}
