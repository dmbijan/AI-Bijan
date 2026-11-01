<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TenantUser;
use App\Modules\HRMS\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** OAM skop data dikuatkuasa di lapisan query: Inventori, POS, HRMS. */
class DataScopeTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    private int $uA;

    private int $uB;

    private int $wA;

    private int $wB;

    private int $rA;

    private int $rB;

    private $mgr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('owner@abc.my', 'ABC Retail');
        $http = $this->sebagai($this->u, $this->t);
        $c = $http->postJson('/api/v1/org/units', ['type' => 'company', 'name' => 'ABC Retail Sdn Bhd'])->json('data.id');
        $this->uA = $http->postJson('/api/v1/org/units', ['type' => 'department', 'name' => 'Kedai Kajang', 'parent_id' => $c])->json('data.id');
        $this->uB = $http->postJson('/api/v1/org/units', ['type' => 'department', 'name' => 'Kedai Johor', 'parent_id' => $c])->json('data.id');
        $this->wA = $http->postJson('/api/v1/inventory/warehouses', ['code' => 'KJG', 'name' => 'Kajang', 'type' => 'outlet'])->json('id');
        $this->wB = $http->postJson('/api/v1/inventory/warehouses', ['code' => 'JHR', 'name' => 'Johor', 'type' => 'outlet'])->json('id');
        $http->putJson('/api/v1/org/links', ['type' => 'warehouse', 'ids' => [$this->wA], 'org_unit_id' => $this->uA])->assertOk();
        $http->putJson('/api/v1/org/links', ['type' => 'warehouse', 'ids' => [$this->wB], 'org_unit_id' => $this->uB])->assertOk();
        $rA = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter Kajang', 'code' => 'KA'])->assertCreated();
        $rB = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter Johor', 'code' => 'KB'])->assertCreated();
        $this->rA = $rA->json('id') ?? $rA->json('data.id');
        $this->rB = $rB->json('id') ?? $rB->json('data.id');
        $http->putJson('/api/v1/org/links', ['type' => 'register', 'ids' => [$this->rA], 'org_unit_id' => $this->uA])->assertOk();
        $http->putJson('/api/v1/org/links', ['type' => 'register', 'ids' => [$this->rB], 'org_unit_id' => $this->uB])->assertOk();

        $this->mgr = $this->jemput($this->u, $this->t, 'pengurus@abc.my', ['tenant_admin', 'hr_manager']);
        $this->dalamTenant($this->t);
        $m = TenantUser::where('user_id', $this->mgr->id)->first();
        $this->sebagai($this->u, $this->t)->putJson("/api/v1/org/members/{$m->id}/scope", ['org_unit_id' => $this->uA, 'data_scope' => 'unit_tree'])->assertOk();
    }

    private function jual(int $reg, string $nama): void
    {
        $r = $this->postJson('/api/v1/pos/sales/checkout', ['register_id' => $reg, 'items' => [['product_name' => $nama, 'quantity' => 1, 'unit_price' => 10]], 'tenders' => [['method' => 'cash', 'amount' => 10]]]);
        $this->assertSame(201, $r->status(), (string) $r->getContent());
    }

    #[Test]
    public function inventori_gudang_dan_pergerakan_ikut_cawangan(): void
    {
        $item = $this->postJson('/api/v1/inventory/items', ['sku' => 'ROS', 'name' => 'Ros', 'uom' => 'unit'])->json('id');
        foreach ([$this->wA, $this->wB] as $w) {
            $m = $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $w, 'lines' => [['item_id' => $item, 'qty' => 10, 'unit_cost' => 1]]])->json('id');
            $this->postJson("/api/v1/inventory/movements/{$m}/post")->assertOk();
        }
        $http = $this->sebagai($this->mgr, $this->t);
        $this->assertSame([$this->wA], collect($http->getJson('/api/v1/inventory/warehouses')->json())->pluck('id')->all());
        $this->assertCount(2, $http->getJson('/api/v1/inventory/warehouses?all=1')->json());
        $this->assertSame([$this->wA], collect($http->getJson('/api/v1/inventory/balances')->json())->pluck('warehouse_id')->unique()->values()->all());
        $this->assertCount(1, $http->getJson('/api/v1/inventory/movements')->json('data'));
        $this->assertEquals(10, $http->getJson('/api/v1/inventory/reports/valuation')->json('total'));

        $http->postJson('/api/v1/inventory/movements', ['type' => 'issue', 'from_warehouse_id' => $this->wB, 'lines' => [['item_id' => $item, 'qty' => 1]]])->assertForbidden();
        $tr = $http->postJson('/api/v1/inventory/movements', ['type' => 'transfer', 'from_warehouse_id' => $this->wA, 'to_warehouse_id' => $this->wB, 'lines' => [['item_id' => $item, 'qty' => 2]]])->assertCreated()->json('id');
        $http->postJson("/api/v1/inventory/movements/{$tr}/post")->assertOk();
        $http->postJson("/api/v1/inventory/movements/{$tr}/receive")->assertForbidden(); // hanya Johor boleh terima
        $this->sebagai($this->u, $this->t)->postJson("/api/v1/inventory/movements/{$tr}/receive")->assertOk();
    }

    #[Test]
    public function pos_kaunter_dan_jualan_ikut_cawangan(): void
    {
        $this->jual($this->rB, 'Johor');
        $http = $this->sebagai($this->mgr, $this->t);
        $this->jual($this->rA, 'Kajang');
        $http->postJson('/api/v1/pos/sales/checkout', ['register_id' => $this->rB, 'items' => [['product_name' => 'X', 'quantity' => 1, 'unit_price' => 1]], 'tenders' => [['method' => 'cash', 'amount' => 1]]])->assertForbidden();
        $regs = $http->getJson('/api/v1/pos/registers')->json();
        $this->assertSame([$this->rA], collect($regs['data'] ?? $regs)->pluck('id')->all());
        $sales = $http->getJson('/api/v1/pos/sales')->json();
        $this->assertSame([$this->rA], collect($sales['data']['data'] ?? $sales['data'] ?? $sales)->pluck('register_id')->unique()->values()->all());

        $own = $this->sebagai($this->u, $this->t)->getJson('/api/v1/pos/sales')->json();
        $this->assertCount(2, collect($own['data']['data'] ?? $own['data'] ?? $own));
    }

    #[Test]
    public function hrms_pekerja_ikut_jabatan_dan_rekod_sendiri(): void
    {
        $this->dalamTenant($this->t);
        $a = Employee::create(['employee_no' => Employee::generateEmployeeNo(), 'first_name' => 'Aina', 'email' => 'aina@abc.my', 'status' => 'active', 'org_unit_id' => $this->uA]);
        $b = Employee::create(['employee_no' => Employee::generateEmployeeNo(), 'first_name' => 'Badrul', 'email' => 'badrul@abc.my', 'status' => 'active', 'org_unit_id' => $this->uB]);
        $self = Employee::create(['employee_no' => Employee::generateEmployeeNo(), 'first_name' => 'Pengurus', 'email' => 'pengurus.hr@abc.my', 'status' => 'active', 'user_id' => User::where('email', 'pengurus@abc.my')->value('id')]);
        $http = $this->sebagai($this->mgr, $this->t);
        $ids = collect($http->getJson('/api/v1/hrms/employees?per_page=50')->json('data.data'))->pluck('id')->sort()->values()->all();
        $this->assertSame(collect([$a->id, $self->id])->sort()->values()->all(), $ids);
        $http->getJson("/api/v1/hrms/employees/{$b->id}")->assertNotFound();
        $http->postJson('/api/v1/hrms/performance/goals', ['employee_id' => $b->id, 'period' => '2026', 'title' => 'x'])->assertStatus(422);
        $http->postJson('/api/v1/hrms/performance/goals', ['employee_id' => $a->id, 'period' => '2026', 'title' => 'x'])->assertCreated();
        $this->assertSame(2, $http->getJson('/api/v1/hrms/talent/brief')->json('data.headcount'));

        // Pekerja baharu oleh pengurus berskop masuk ke unitnya.
        $new = $http->postJson('/api/v1/hrms/employees', ['first_name' => 'Baru', 'email' => 'baru@abc.my', 'employment_type' => 'full_time'])->assertSuccessful()->json('data.id');
        $this->dalamTenant($this->t);
        $this->assertSame($this->uA, Employee::withoutGlobalScopes()->find($new)->org_unit_id);
        $this->assertCount(4, collect($this->sebagai($this->u, $this->t)->getJson('/api/v1/hrms/employees?per_page=50')->json('data.data')));
    }

    #[Test]
    public function hanya_skop_penuh_boleh_menautkan_unit(): void
    {
        $this->jemput($this->u, $this->t, 'admin@abc.my', ['tenant_admin']);
        $this->dalamTenant($this->t);
        $adm = TenantUser::where('user_id', User::where('email', 'admin@abc.my')->value('id'))->first();
        $this->sebagai($this->u, $this->t)->putJson("/api/v1/org/members/{$adm->id}/scope", ['org_unit_id' => $this->uA, 'data_scope' => 'own_unit'])->assertOk();
        $admin = User::where('email', 'admin@abc.my')->first();
        $this->sebagai($admin, $this->t)->putJson('/api/v1/org/links', ['type' => 'warehouse', 'ids' => [$this->wB], 'org_unit_id' => $this->uA])->assertForbidden();
        $this->assertCount(3, $this->sebagai($this->u, $this->t)->getJson('/api/v1/org/links')->json('data'));
    }
}
