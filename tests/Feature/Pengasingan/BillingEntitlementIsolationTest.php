<?php

namespace Tests\Feature\Pengasingan;

use App\Models\Plan;
use App\Models\Subscription;
use App\Modules\Entitlement\Models\EntitlementGrant;
use App\Modules\Entitlement\Models\UsageCounter;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Billing (langganan) + Entitlement (had modul).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • Billing: `Subscription` guna trait → diuji dengan 4 ujian teras.
 * • Entitlement: `EntitlementGrant` dan `UsageCounter` SENGAJA tidak guna
 *   trait (lihat `config/tenant-global-models.php`): ia ditulis semasa
 *   provisioning ketika `TenantContext` kosong, jadi skop global akan
 *   menapis `tenant_id = 0` dan mencipta rekod dengan tenant salah.
 *
 *   Kesan sampingan yang WAJIB diketahui: TIADA pengasingan automatik.
 *   Setiap query mesti menulis `where('tenant_id', …)` secara eksplisit.
 *   Ujian di bawah mengukur andaian itu secara langsung — bukan andaian.
 */
class BillingEntitlementIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Billing';

    /** Hanya `Subscription` guna trait — diuji oleh ujian teras generik. */
    protected array $model = [
        Subscription::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan bil dikawal oleh peranan platform/super-admin';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        // ⚠️ `TenantProvisioner` mencipta langganan PERCUBAAN untuk setiap tenant
        // baharu. Jadi ujian "tenant dua tidak nampak senarai" MESTI tidak
        // bergantung pada `count() == 0`. `plan_id` dikongsi oleh kedua-dua
        // baris, jadi `plan_id` sahaja tidak membezakan — guna `ends_at`
        // yang unik supaya baris yang dicipta ujian boleh dikenal pasti.
        return [
            'plan_id' => $this->plan()->id,
            'status' => 'trial',
            'starts_at' => '2030-01-01 00:00:00',
            'ends_at' => '2030-12-31 23:59:59',
        ];
    }

    private function plan(): Plan
    {
        return Plan::firstOrCreate(
            ['code' => 'percubaan'],
            [
                'name' => 'Percubaan',
                'max_users' => 5,
                'max_locations' => 1,
                'storage_mb' => 1024,
                'ai_credits_monthly' => 0,
                'price_monthly' => 0,
                'is_active' => true,
                'modules' => ['*'],
            ]
        );
    }

    // ===============================================================
    // LANGANAN — semakan tepat (bukan count == 0)
    // ===============================================================

    /**
     * Setiap tenant dapat langganan percubaan automatik daripada
     * `TenantProvisioner`, jadi `count() == 0` tidak sah sebagai ukuran.
     * Yang diuji: baris MILIK tenant satu (dikenal pasti melalui `ends_at`
     * 2030) tidak kelihatan dalam konteks tenant dua.
     */
    public function test_tenant_lain_tidak_nampak_senarai(): void
    {
        $this->dalamTenant($this->t);
        $this->rekod(Subscription::class);

        $this->konteksDua();

        $this->assertSame(
            0,
            Subscription::whereDate('ends_at', '2030-12-31')->count(),
            'Subscription: langganan tenant satu kelihatan dalam konteks tenant dua (bocor).'
        );

        // Bukti ia wujud untuk tenant satu
        $this->assertSame(
            1,
            Subscription::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->t->id)
                ->whereDate('ends_at', '2030-12-31')
                ->count(),
            'Langganan ujian tidak dicipta untuk tenant satu — ujian tidak bermakna.'
        );
    }

    // ===============================================================
    // ENTITLEMENT — bukan trait, jadi diuji secara eksplisit
    // ===============================================================

    /**
     * Grant tenant dua TIDAK boleh dilihat oleh query tenant satu.
     *
     * Jika ini gagal, bermakna kod memanggil `EntitlementGrant::query()`
     * tanpa `where('tenant_id', …)` — iaitu kebocoran sebenar.
     */
    public function test_grant_tenant_lain_tidak_nampak_tanpa_skop(): void
    {
        $this->dalamTenant($this->t);
        EntitlementGrant::create([
            'tenant_id' => $this->t->id,
            'module' => 'modul_tenant_satu',
            'source' => 'addon',
            'enabled' => true,
        ]);

        $t2 = $this->konteksDua();
        $this->dalamTenant($t2);
        EntitlementGrant::create([
            'tenant_id' => $t2->id,
            'module' => 'modul_tenant_dua',
            'source' => 'addon',
            'enabled' => true,
        ]);

        // Corak yang DIDOKUMENTASIKAN (dan digunakan oleh EntitlementService)
        $this->assertSame(
            1,
            EntitlementGrant::where('tenant_id', $this->t->id)->count(),
            'Query eksplisit where(tenant_id) TIDAK menapis — kebocoran entitlement.'
        );

        // Skema: setiap grant mesti ada pemilik yang sah
        $this->assertSame(
            0,
            EntitlementGrant::whereNull('tenant_id')->count(),
            'Grant tanpa tenant_id ditemui — provisioning menulis rekod yatim.'
        );
    }

    /**
     * Kaunter penggunaan pula SENGAJA boleh dibaca tanpa tenant (job/provisioning).
     */
    public function test_kaunter_penggunaan_perlu_penapis_tenant(): void
    {
        $this->dalamTenant($this->t);
        UsageCounter::create([
            'tenant_id' => $this->t->id,
            'limit_key' => 'ai_tokens',
            'period_key' => '2026-10',
            'used_value' => 11,
            'reserved_value' => 0,
        ]);

        $t2 = $this->konteksDua();
        $this->dalamTenant($t2);
        UsageCounter::create([
            'tenant_id' => $t2->id,
            'limit_key' => 'ai_tokens',
            'period_key' => '2026-10',
            'used_value' => 22,
            'reserved_value' => 0,
        ]);

        $this->assertSame(
            11,
            (int) UsageCounter::where('tenant_id', $this->t->id)->value('used_value'),
            'Penggunaan tenant satu terbaca sebagai milik tenant lain.'
        );
    }
}
