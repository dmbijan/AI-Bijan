<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Entitlement\Models\EntitlementGrant;
use App\Modules\Entitlement\Models\UsageCounter;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\Quotation;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — Entitlement (geran + pembilang penggunaan) dan
 * Invoicing (quotation + invois).
 *
 * Ditambah 28 Okt 2026 (T-111).
 *
 * • Entitlement: `EntitlementGrant` & `UsageCounter` SENGAJA tiada
 *   `BelongsToTenant` (ditulis semasa provisioning, lihat senarai putih
 *   `config/tenant-global-models.php`) → ujian ini memaksa laluan pengawal
 *   menapis dengan `where('tenant_id', …)` eksplisit. Jika pagar itu hilang,
 *   satu tenant boleh menaikkan had langganan tenant lain.
 * • Invoicing: invois tenant lain ialah kebocoran kewangan + PDPA.
 */
class EntitlementInvoicingIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Entitlement,Invoicing';

    protected array $model = [
        Quotation::class,
        Invoice::class,
    ];

    protected ?string $laluanApi = '/api/v1/invoicing/invoices';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Quotation::class) {
            return [
                'customer_name' => 'Pelanggan Rahsia Tenant Satu ' . $n,
                'issue_date' => now()->toDateString(),
                'status' => 'draft',
            ];
        }

        return [
            'customer_name' => 'Pelanggan Rahsia Tenant Satu ' . $n,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
        ];
    }

    /**
     * Entitlement tiada trait — diuji dengan query eksplisit seperti pengawal.
     */
    public function test_entitlement_hanya_dikembalikan_untuk_tenant_aktif(): void
    {
        $grant = EntitlementGrant::create([
            'tenant_id' => $this->t->id,
            'module' => 'ai_gateway',
            'source' => 'manual',
            'enabled' => true,
            'enabled_at' => now(),
        ]);

        UsageCounter::create([
            'tenant_id' => $this->t->id,
            'limit_key' => 'ai_tokens',
            'period_key' => now()->format('Y-m'),
            'used_value' => 5,
            'reserved_value' => 0,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);

        // Laluan yang betul: pengawal MESTI tapis tenant_id eksplisit.
        $this->assertSame(1, EntitlementGrant::where('tenant_id', $this->t->id)->count());
        $this->assertSame(0, EntitlementGrant::where('tenant_id', $this->t->id + 99999)->count());
        $this->assertSame(1, UsageCounter::where('tenant_id', $this->t->id)->count());
        $this->assertSame(0, UsageCounter::where('tenant_id', $this->t->id + 99999)->count());
        $this->assertSame($this->t->id, (int) $grant->tenant_id);
    }
}
