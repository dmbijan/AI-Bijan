<?php

namespace Tests\Feature;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Expense\Models\Expense;
use App\Modules\Expense\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Panel Filament (T-71) — ujian asas UI.
 *
 * Fokus: panel boleh dimuatkan, resource perbelanjaan berfungsi,
 * tenant context ditetapkan, dan pengasingan tenant dikekalkan.
 */
class FilamentPanelTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private User $u;
    private Tenant $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@filament.my', 'Filament Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    // ===============================================================
    // AUTH
    // ===============================================================

    public function test_login_page_boleh_dimuatkan(): void
    {
        // Filament redirect pengguna yang sudah log masuk; tetamu dapat 200
        $respon = $this->get('/admin/login');

        $this->assertContains($respon->getStatusCode(), [200, 302]);
    }

    public function test_tetamu_ditolak_ke_login(): void
    {
        $respon = $this->get('/admin');

        // Authenticate middleware → redirect ke login ATAU 403 (panel lock)
        $this->assertTrue(
            $respon->isRedirect() || $respon->getStatusCode() === 403,
            'Tetamu sepatutnya dialihkan atau ditolak, bukan diberikan akses.'
        );
    }

    public function test_pengguna_log_masuk_boleh_akses_dashboard(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin')->assertOk();
    }

    // ===============================================================
    // RESOURCE PERBELANJAAN
    // ===============================================================

    public function test_senarai_perbelanjaan_boleh_dimuatkan(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/perbelanjaan')->assertOk();
    }

    public function test_borang_cipta_perbelanjaan_boleh_dimuatkan(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/perbelanjaan/create')->assertOk();
    }

    public function test_perbelanjaan_dipaparkan_dalam_senarai(): void
    {
        $this->actingAs($this->u);

        $kategori = ExpenseCategory::create([
            'tenant_id' => $this->t->id,
            'code' => 'TRAVEL',
            'name' => 'Perjalanan',
        ]);

        $expense = Expense::create([
            'tenant_id' => $this->t->id,
            'expense_no' => 'EXP-2026-0001',
            'expense_category_id' => $kategori->id,
            'vendor_name' => 'Petronas Kota Damansara',
            'expense_date' => now()->toDateString(),
            'status' => Expense::STATUS_DRAFT,
            'payment_status' => Expense::PAYMENT_UNPAID,
            'currency' => 'MYR',
            'total_amount' => 150,
            'amount_myr' => 150,
        ]);

        $this->get('/admin/perbelanjaan')
            ->assertOk()
            ->assertSee('EXP-2026-0001')
            ->assertSee('Petronas Kota Damansara');
    }

    public function test_halaman_lihat_perbelanjaan(): void
    {
        $this->actingAs($this->u);

        $expense = Expense::create([
            'tenant_id' => $this->t->id,
            'expense_no' => 'EXP-2026-0002',
            'vendor_name' => 'Vendor Ujian',
            'expense_date' => now()->toDateString(),
            'status' => Expense::STATUS_DRAFT,
            'payment_status' => Expense::PAYMENT_UNPAID,
            'currency' => 'MYR',
            'total_amount' => 99.50,
            'amount_myr' => 99.50,
        ]);

        $this->get("/admin/perbelanjaan/{$expense->id}")->assertOk();
        $this->get("/admin/perbelanjaan/{$expense->id}/edit")->assertOk();
    }

    // ===============================================================
    // PENGASINGAN TENANT
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_perbelanjaan(): void
    {
        // Tenant A — perbelanjaan
        Expense::create([
            'tenant_id' => $this->t->id,
            'expense_no' => 'EXP-RAHSIA-A',
            'vendor_name' => 'Vendor Tenant A',
            'expense_date' => now()->toDateString(),
            'status' => Expense::STATUS_DRAFT,
            'payment_status' => Expense::PAYMENT_UNPAID,
            'currency' => 'MYR',
            'total_amount' => 500,
            'amount_myr' => 500,
        ]);

        // Tenant B — pengguna lain
        [$u2, $t2] = $this->daftar('pemilik@tenant-b.my', 'Tenant B Sdn Bhd');

        $this->actingAs($u2);
        session(['tenant_ulid' => $t2->ulid]);

        $this->get('/admin/perbelanjaan')
            ->assertOk()
            ->assertDontSee('EXP-RAHSIA-A')
            ->assertDontSee('Vendor Tenant A');
    }

    // ===============================================================
    // WIDGET
    // ===============================================================

    public function test_dashboard_boleh_dimuatkan_dengan_widget(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin')->assertOk();
    }

    public function test_widget_kpi_pulang_data_sebenar(): void
    {
        $this->actingAs($this->u);
        $this->dalamTenant($this->t);

        // Cipta perbelanjaan menunggu kelulusan
        Expense::create([
            'tenant_id' => $this->t->id,
            'expense_no' => 'EXP-KPI-0001',
            'vendor_name' => 'Vendor KPI',
            'expense_date' => now()->toDateString(),
            'status' => Expense::STATUS_SUBMITTED,
            'payment_status' => Expense::PAYMENT_UNPAID,
            'currency' => 'MYR',
            'total_amount' => 250,
            'amount_myr' => 250,
        ]);

        \Livewire\Livewire::test(\App\Filament\Widgets\CommandCentreWidget::class)
            ->assertSee('Hasil Bulan Ini')
            ->assertSee('Belum Dibayar')
            ->assertSee('Perbelanjaan Bulan Ini')
            ->assertSee('Menunggu Kelulusan')
            ->assertSee('1'); // 1 perbelanjaan menunggu
    }

    // ===============================================================
    // 5 SKRIN TERAS
    // ===============================================================

    public function test_senarai_pesanan(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/pesanan')->assertOk();
    }

    public function test_senarai_pelanggan_dan_borang_cipta(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/pelanggan')->assertOk();
        $this->get('/admin/pelanggan/create')->assertOk();
    }

    public function test_senarai_invois(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/invois')->assertOk();
    }

    public function test_senarai_produk_dan_borang_cipta(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/produk')->assertOk();
        $this->get('/admin/produk/create')->assertOk();
    }

    public function test_pelanggan_dipaparkan_dalam_senarai(): void
    {
        $this->actingAs($this->u);
        $this->dalamTenant($this->t);

        \App\Modules\CRM\Models\Customer::create([
            'tenant_id' => $this->t->id,
            'customer_code' => 'CUST-0001',
            'name' => 'Syarikat Ujian Sdn Bhd',
            'customer_type' => 'business',
            'status' => 'active',
        ]);

        $this->get('/admin/pelanggan')
            ->assertOk()
            ->assertSee('Syarikat Ujian Sdn Bhd');
    }

    // ===============================================================
    // KEBENARAN RESOURCE (HasModulePermission)
    // ===============================================================

    public function test_pemilik_nampak_semua_resource(): void
    {
        // Pemilik perlu keahlian supaya permission '*' dapat dibaca
        $membership = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $this->u->id)
            ->where('tenant_id', $this->t->id)
            ->first();

        $this->assertNotNull($membership, 'Pemilik perlu keahlian aktif.');

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $membership);

        // Pemilik ada semua kebenaran (peranan tenant_owner = '*')
        $this->assertTrue(ExpenseResource::canViewAny());
        $this->assertTrue(ExpenseResource::canCreate());
        $this->assertTrue(OrderResource::canViewAny());
        $this->assertTrue(CustomerResource::canViewAny());
        $this->assertTrue(InvoiceResource::canViewAny());
        $this->assertTrue(ItemResource::canViewAny());
    }

    public function test_tanpa_konteks_tenant_ditolak(): void
    {
        // Konteks tenant dikosongkan → deny-by-default
        app(\App\Support\Tenancy\TenantContext::class)->clear();

        $this->assertFalse(ExpenseResource::canViewAny());
        $this->assertFalse(ExpenseResource::canCreate());
        $this->assertFalse(OrderResource::canViewAny());
    }

    public function test_staf_tanpa_kebenaran_expense_tidak_nampak_menu(): void
    {
        // Peranan `staff` tiada `expense.*` mengikut config/aibijan.php
        $staf = $this->jemput($this->u, $this->t, 'staf@filament.my', ['staff']);

        $this->actingAs($staf);
        session(['tenant_ulid' => $this->t->ulid]);

        // Tetapkan konteks seperti middleware panel
        $membership = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $staf->id)
            ->where('tenant_id', $this->t->id)
            ->first();

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $membership);

        // Staf tiada kebenaran expense → menu tersembunyi, akses ditolak
        $this->assertFalse(ExpenseResource::canViewAny());
        $this->assertFalse(ExpenseResource::canCreate());
        $this->assertFalse(ExpenseResource::shouldRegisterNavigation());
    }

    public function test_staf_dengan_kebenaran_inventory_boleh_lihat_produk(): void
    {
        // Peranan `inventory_manager` ada `inventory.view` + `inventory.manage`
        $pengurus = $this->jemput($this->u, $this->t, 'stok@filament.my', ['inventory_manager']);

        $this->actingAs($pengurus);

        $membership = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $pengurus->id)
            ->where('tenant_id', $this->t->id)
            ->first();

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $membership);

        $this->assertTrue(ItemResource::canViewAny());
        $this->assertTrue(ItemResource::canCreate());
        $this->assertTrue(ItemResource::shouldRegisterNavigation());

        // Tidak nampak perbelanjaan (tiada expense.*)
        $this->assertFalse(ExpenseResource::canViewAny());
    }

    // ===============================================================
    // SKRIN SERVICEPROJECT (6)
    // ===============================================================

    public function test_senarai_projek_perkhidmatan(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/projek-perkhidmatan')->assertOk();
        $this->get('/admin/projek-perkhidmatan/create')->assertOk();
    }

    // ===============================================================
    // SKRIN KANDUNGAN (7) & IDEA BISNES (8)
    // ===============================================================

    public function test_senarai_kandungan_dan_borang_cipta(): void
    {
        $this->actingAs($this->u);

        $this->get('/admin/kandungan')->assertOk();
        $this->get('/admin/kandungan/create')->assertOk();
    }

    public function test_senarai_idea_bisnes_dan_borang_cipta(): void
    {
        $this->actingAs($this->u);

        // Modul `business_intel` tidak ada dalam plan `starter` — grant dahulu.
        app(\App\Modules\Entitlement\Services\EntitlementService::class)
            ->grantModul($this->t->id, 'business_intel');

        // ⚠️ `dalamTenant()` menetapkan konteks TANPA keahlian → permissions()
        // kosong. Untuk ujian kebenaran, set keahlian sebenar.
        $keahlian = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $this->u->id)->where('tenant_id', $this->t->id)->first();

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $keahlian);

        $this->get('/admin/idea-bisnes')->assertOk();
        $this->get('/admin/idea-bisnes/create')->assertOk();
    }

    public function test_kandungan_dipaparkan_dalam_senarai(): void
    {
        $this->actingAs($this->u);
        $this->dalamTenant($this->t);

        \App\Modules\ContentStudio\Models\ContentPiece::create([
            'tenant_id' => $this->t->id,
            'title' => 'Post Ujian Promosi Raya',
            'body' => 'Kandungan ujian',
            'hook' => 'Hook 3 saat',
            'status' => \App\Modules\ContentStudio\Models\ContentPiece::STATUS_DRAFT,
            'content_type' => 'post',
        ]);

        $this->get('/admin/kandungan')
            ->assertOk()
            ->assertSee('Post Ujian Promosi Raya');
    }

    // ===============================================================
    // REGRESI CSP + LIVEWIRE (T-71)
    // ===============================================================

    /**
     * ⚠️ Nonce dan 'unsafe-inline' SALING EKSKLUSIF dalam CSP. Jika kedua-dua
     * hadir, penyemak imbas mengabaikan 'unsafe-inline' dan menyekat skrip
     * inline Livewire → butang login tidak berfungsi.
     */
    public function test_csp_tidak_campur_nonce_dengan_unsafe_inline(): void
    {
        $csp = (string) $this->get('/admin/login')->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $csp, 'Header CSP mesti hadir.');
        $this->assertStringContainsString("script-src", $csp);
        $this->assertStringContainsString("'unsafe-eval'", $csp, 'Alpine.js perlukan unsafe-eval.');

        $this->assertFalse(
            str_contains($csp, 'nonce-') && str_contains($csp, "'unsafe-inline'"),
            'CSP tidak boleh mengandungi nonce DAN unsafe-inline serentak.'
        );
    }

    /** Fon Filament (Bunny) mesti dibenarkan, jika tidak UI jatuh ke fon sistem. */
    public function test_csp_membenarkan_fon_bunny(): void
    {
        $csp = (string) $this->get('/admin/login')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('fonts.bunny.net', $csp);
    }

    /**
     * ⚠️ Regresi: Livewire memanggil `/livewire/update` dan laluan itu TIDAK
     * melalui `authMiddleware` panel. Jika `ResolveFilamentTenant` tidak
     * didaftarkan dalam kumpulan `web`, widget berjalan tanpa TenantContext
     * dan KPI memaparkan "Tiada Syarikat Aktif".
     */
    public function test_laluan_livewire_mempunyai_middleware_tenant(): void
    {
        $route = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($r) => str_contains($r->uri(), 'livewire'));

        $this->assertNotNull($route, 'Laluan Livewire mesti wujud.');

        $middleware = app('router')->gatherRouteMiddleware($route);

        $this->assertContains(
            \App\Http\Middleware\ResolveFilamentTenant::class,
            $middleware,
            'ResolveFilamentTenant mesti berjalan untuk permintaan Livewire.'
        );
    }

    /**
     * Setiap resource Filament mesti menggunakan namespace permission yang
     * BENAR-BENAR wujud dalam DB. Namespace rekaan → menu sentiasa tersembunyi.
     */
    public function test_semua_resource_guna_namespace_permission_yang_wujud(): void
    {
        $wujud = \App\Models\Permission::withoutGlobalScopes()->pluck('key')
            ->map(fn ($k) => explode('.', $k)[0])
            ->unique()
            ->all();

        $resource = [
            \App\Filament\Resources\Expenses\ExpenseResource::class,
            \App\Filament\Resources\Orders\OrderResource::class,
            \App\Filament\Resources\Customers\CustomerResource::class,
            \App\Filament\Resources\Invoices\InvoiceResource::class,
            \App\Filament\Resources\Items\ItemResource::class,
            \App\Filament\Resources\ServiceProjects\ServiceProjectResource::class,
            \App\Filament\Resources\ContentPieces\ContentPieceResource::class,
            \App\Filament\Resources\BusinessIdeas\BusinessIdeaResource::class,
        ];

        foreach ($resource as $kelas) {
            $ns = (new \ReflectionMethod($kelas, 'permissionNamespace'))->invoke(null);

            $this->assertContains(
                $ns,
                $wujud,
                "Resource {$kelas} guna namespace '{$ns}' yang tiada dalam jadual permissions."
            );
        }
    }

    /** Pemilik mesti nampak KESEMUA 8 resource dalam navigasi. */
    public function test_pemilik_nampak_lapan_resource(): void
    {
        $this->actingAs($this->u);
        $this->dalamTenant($this->t);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Perbelanjaan')
            ->assertSee('Pesanan')
            ->assertSee('Pelanggan')
            ->assertSee('Invois')
            ->assertSee('Produk')
            ->assertSee('Projek Perkhidmatan')
            ->assertSee('Kandungan')
            ->assertSee('Idea Bisnes');
    }

    /** KPI dashboard mesti tunjuk angka sebenar, bukan "Tiada Syarikat Aktif". */
    public function test_kpi_tidak_tunjuk_tiada_syarikat_aktif(): void
    {
        $this->actingAs($this->u);
        $this->dalamTenant($this->t);

        // Widget ialah komponen Livewire — render terus supaya KPI ada dalam HTML.
        \Livewire\Livewire::test(\App\Filament\Widgets\CommandCentreWidget::class)
            ->assertDontSee('Tiada Syarikat Aktif')
            ->assertSee('Hasil Bulan Ini');
    }

    // ===============================================================
    // WIDGET CARTA & JADUAL (T-71 fasa 2)
    // ===============================================================

    /**
     * ⚠️ Regresi: `ChartWidget`/`TableWidget` lalai `$isLazy = true`, jadi ia
     * hanya render melalui `POST /livewire/update` dan kekal sebagai
     * `fi-loading-section` kosong jika permintaan itu gagal. Semua widget
     * carta/jadual MESTI `$isLazy = false`.
     */
    public function test_semua_widget_carta_dan_jadual_tidak_lazy(): void
    {
        $widget = [
            \App\Filament\Widgets\HasilTrendChart::class,
            \App\Filament\Widgets\SaluranPesananChart::class,
            \App\Filament\Widgets\PesananTerkiniWidget::class,
            \App\Filament\Widgets\InvoisTertunggakWidget::class,
        ];

        foreach ($widget as $kelas) {
            $sifat = (new \ReflectionClass($kelas))->getStaticPropertyValue('isLazy');

            $this->assertFalse(
                $sifat,
                "{$kelas} mesti \$isLazy = false supaya render dalam permintaan pertama."
            );
        }
    }

    /** Semua widget mesti didaftarkan pada panel (bukan hanya fail wujud). */
    public function test_semua_widget_didaftar_pada_panel(): void
    {
        $didaftar = array_map(
            fn ($w) => is_string($w) ? $w : get_class($w),
            \Filament\Facades\Filament::getPanel('admin')->getWidgets()
        );

        foreach ([
            \App\Filament\Widgets\CommandCentreWidget::class,
            \App\Filament\Widgets\PesananRingkasanWidget::class,
            \App\Filament\Widgets\HasilTrendChart::class,
            \App\Filament\Widgets\SaluranPesananChart::class,
            \App\Filament\Widgets\PesananTerkiniWidget::class,
            \App\Filament\Widgets\InvoisTertunggakWidget::class,
        ] as $kelas) {
            $this->assertContains($kelas, $didaftar, "{$kelas} tidak didaftarkan pada panel.");
        }
    }

    /** Carta hasil mesti membawa data sebenar daripada invois. */
    public function test_carta_hasil_mengandungi_data_invois(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        \App\Modules\Invoicing\Models\Invoice::create([
            'invoice_number' => 'INV-CHART-001',
            'customer_name' => 'Pelanggan Carta',
            'issue_date' => now()->subDays(3)->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 1500,
            'grand_total' => 1500,
            'amount_paid' => 0,
            'amount_outstanding' => 1500,
            'status' => 'issued',
            'currency' => 'MYR',
        ]);

        $widget = new \App\Filament\Widgets\HasilTrendChart;
        $data = (new \ReflectionMethod($widget, 'getData'))->invoke($widget);

        $this->assertCount(30, $data['datasets'][0]['data']);
        $this->assertCount(30, $data['labels']);
        $this->assertEqualsWithDelta(1500, array_sum($data['datasets'][0]['data']), 0.01);
    }

    /** Carta saluran mesti mengira pesanan mengikut `source`. */
    public function test_carta_saluran_mengira_pesanan(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        foreach (['pos', 'pos', 'marketplace'] as $i => $src) {
            \App\Modules\Orders\Models\UnifiedOrder::create([
                'order_no' => 'ORD-CHART-'.$i,
                'source' => $src,
                'status' => 'paid',
                'payment_status' => 'paid',
                'customer_name' => 'Pelanggan '.$i,
                'subtotal' => 100,
                'total_amount' => 100,
                'currency' => 'MYR',
            ]);
        }

        $widget = new \App\Filament\Widgets\SaluranPesananChart;
        $data = (new \ReflectionMethod($widget, 'getData'))->invoke($widget);

        $this->assertContains('POS', $data['labels']);
        $this->assertContains('Marketplace', $data['labels']);
        $this->assertEquals(3, array_sum($data['datasets'][0]['data']));
    }

    /** Widget jadual mesti tunjuk tajuk dan senarai kosong yang betul. */
    public function test_widget_jadual_dashboard_dipaparkan(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Pesanan Terkini')
            ->assertSee('Invois Tertunggak');
    }

    /**
     * Tetapkan konteks tenant DENGAN keahlian sebenar supaya `permissions()`
     * tidak kosong. `dalamTenant()` sengaja tidak menetapkan keahlian.
     */
    private function konteksPenuh(): void
    {
        $keahlian = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $this->u->id)
            ->where('tenant_id', $this->t->id)
            ->first();

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $keahlian);
    }

    // ===============================================================
    // TETAPAN SYARIKAT
    // ===============================================================

    public function test_halaman_tetapan_boleh_dimuatkan(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin/tetapan')
            ->assertOk()
            ->assertSee('Tetapan Syarikat')
            ->assertSee('Maklumat Syarikat')
            ->assertSee('Langganan');
    }

    public function test_tetapan_papar_plan_dan_had(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin/tetapan')
            ->assertOk()
            ->assertSee('Had Pengguna')
            ->assertSee('Kredit AI')
            ->assertSee('Modul Aktif');
    }

    /**
     * Pengguna TANPA permission `tenant.settings.view` ditolak.
     *
     * ⚠️ Tidak boleh diuji melalui HTTP: `ResolveFilamentTenant` menetapkan
     * semula konteks daripada keahlian sebenar pada setiap permintaan, jadi
     * "konteks tanpa kebenaran" buatan ujian akan ditimpa. Uji `canAccess()`
     * secara langsung dengan keahlian yang tiada peranan.
     */
    public function test_tetapan_ditolak_tanpa_kebenaran(): void
    {
        $this->actingAs($this->u);

        // Keahlian tanpa sebarang peranan → permissions() kosong → ditolak.
        $keahlian = \App\Models\TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $this->u->id)
            ->where('tenant_id', $this->t->id)
            ->firstOrFail();

        $keahlian->roles()->sync([]);

        app(\App\Support\Tenancy\TenantContext::class)->set($this->t, $keahlian->fresh());

        $this->assertFalse(
            \App\Filament\Pages\TetapanSyarikat::canAccess(),
            'Pengguna tanpa tenant.settings.view tidak boleh akses Tetapan.'
        );
    }

    /** Simpan tetapan mengemas kini rekod tenant. */
    public function test_simpan_tetapan_mengemas_kini_tenant(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        \Livewire\Livewire::test(\App\Filament\Pages\TetapanSyarikat::class)
            ->fillForm([
                'name' => 'Syarikat Baharu Sdn Bhd',
                'timezone' => 'Asia/Kuala_Lumpur',
                'currency' => 'MYR',
                'locale' => 'ms',
            ])
            ->call('simpan')
            ->assertHasNoFormErrors();

        $this->assertSame('Syarikat Baharu Sdn Bhd', $this->t->fresh()->name);
    }

    // ===============================================================
    // POS UI (T-76 · baca sahaja untuk transaksi)
    // ===============================================================

    public function test_senarai_jualan_pos_boleh_dimuatkan(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin/pos-jualan')->assertOk();
    }

    public function test_senarai_kaunter_pos_dan_borang_cipta(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin/pos-kaunter')->assertOk();
        $this->get('/admin/pos-kaunter/create')->assertOk();
    }

    public function test_senarai_syif_tunai_boleh_dimuatkan(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin/pos-syif')->assertOk();
    }

    /**
     * ⚠️ Jualan & syif POS TIDAK boleh dicipta melalui Filament — mesti
     * melalui `SaleService`/`CashShiftService` supaya stok, tender dan
     * kiraan tunai tidak dipintas.
     */
    public function test_jualan_pos_dan_syif_tidak_boleh_dicipta(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->assertFalse(
            \App\Filament\Resources\PosSales\PosSaleResource::canCreate(),
            'Jualan POS tidak boleh dicipta melalui UI.'
        );

        $this->assertFalse(
            \App\Filament\Resources\CashShifts\CashShiftResource::canCreate(),
            'Syif tunai tidak boleh dicipta melalui UI.'
        );
    }

    /** Kaunter POS BOLEH dicipta (konfigurasi, bukan transaksi). */
    public function test_kaunter_pos_boleh_dicipta(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->assertTrue(
            \App\Filament\Resources\PosRegisters\PosRegisterResource::canCreate(),
            'Kaunter POS ialah konfigurasi — boleh dicipta melalui UI.'
        );

        \Livewire\Livewire::test(\App\Filament\Resources\PosRegisters\Pages\CreatePosRegister::class)
            ->fillForm([
                'name' => 'Kaunter Depan',
                'location' => 'Tingkat 1',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pos_registers', ['name' => 'Kaunter Depan']);
    }

    // ===============================================================
    // LIPUTAN MODUL (T-71 fasa 3) — 59 resource
    // ===============================================================

    /**
     * ⚠️ Setiap resource Filament menggunakan namespace permission yang
     * MESTI wujud dalam jadual `permissions`. Namespace rekaan → menu
     * muncul tetapi halaman 403 (UX buruk, dan petanda config tidak lengkap).
     */
    public function test_setiap_resource_guna_namespace_yang_wujud_dalam_db(): void
    {
        $ada = \App\Models\Permission::withoutGlobalScopes()
            ->pluck('key')
            ->map(fn ($k) => explode('.', $k)[0])
            ->unique()
            ->all();

        $resource = collect(glob(app_path('Filament/Resources/*/*Resource.php')))
            ->map(fn ($f) => 'App\\Filament\\Resources\\'.basename(dirname($f)).'\\'.basename($f, '.php'));

        $this->assertGreaterThanOrEqual(50, $resource->count(), 'Sekurang-kurangnya 50 resource dijangka.');

        $gagal = [];

        foreach ($resource as $kelas) {
            if (! method_exists($kelas, 'permissionNamespace')) {
                continue;
            }

            $ns = (new \ReflectionMethod($kelas, 'permissionNamespace'))->invoke(null);

            if (! in_array($ns, $ada, true)) {
                $gagal[] = $kelas.' → '.$ns;
            }
        }

        $this->assertSame(
            [],
            $gagal,
            "Resource menggunakan namespace permission yang TIADA dalam DB:\n".implode("\n", $gagal)
        );
    }

    /** Semua model yang digunakan resource mesti mempunyai jadual sebenar. */
    public function test_setiap_model_resource_mempunyai_jadual(): void
    {
        $resource = collect(glob(app_path('Filament/Resources/*/*Resource.php')))
            ->map(fn ($f) => 'App\\Filament\\Resources\\'.basename(dirname($f)).'\\'.basename($f, '.php'));

        $gagal = [];

        foreach ($resource as $kelas) {
            if (! method_exists($kelas, 'getModel')) {
                continue;
            }

            try {
                $jadual = (new ($kelas::getModel()))->getTable();

                if (! \Illuminate\Support\Facades\Schema::hasTable($jadual)) {
                    $gagal[] = $kelas.' → jadual `'.$jadual.'` TIADA';
                }
            } catch (\Throwable $e) {
                $gagal[] = $kelas.' → ralat: '.substr($e->getMessage(), 0, 90);
            }
        }

        $this->assertSame(
            [],
            $gagal,
            "Model resource merujuk jadual yang tidak wujud:\n".implode("\n", $gagal)
        );
    }

    /** Dashboard mesti render tanpa ralat selepas widget ditambah. */
    public function test_dashboard_render_tanpa_ralat(): void
    {
        $this->actingAs($this->u);
        $this->konteksPenuh();

        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('Internal Server Error')
            ->assertDontSee('SQLSTATE');
    }
}
