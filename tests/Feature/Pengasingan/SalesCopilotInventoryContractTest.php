<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\SalesCopilot\Http\Controllers\SalesCopilotController;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SalesCopilot + Inventory (kontrak modul).
 *
 * Ditambah 27 Okt 2026 (P1-1d) sebagai ujian **khusus** untuk dua modul yang
 * bergantung pada model modul lain:
 *
 * • `SalesCopilot` tiada model sendiri — servisnya membaca `Prospect`
 *   (Prospecting) dan `BlastMessage` (Blast). Ujian ini memastikan ia hanya
 *   membaca data tenant semasa.
 * • `Inventory` tiada folder `Models/` — modelnya di `app/Models`
 *   (`Item`, `StockBalance`, `Warehouse`). Lihat
 *   `AdeInventoryCopilotIsolationTest` untuk ujian data penuh.
 *
 * Kedua-dua kategori ini bermakna audit liputan TIDAK boleh meneka melalui
 * nama folder sahaja — ujian ini mendokumentasikan sebabnya.
 */
class SalesCopilotInventoryContractTest extends UjianPengasingan
{
    protected string $modul = 'SalesCopilot';

    protected array $model = [];

    protected ?string $laluanApi = '/api/v1/sales-copilot/requires-attention';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    public function test_controller_wujud_dan_memerlukan_kebenaran(): void
    {
        $this->assertTrue(
            class_exists(SalesCopilotController::class),
            'SalesCopilotController tidak dijumpai.'
        );

        // Laluan mesti berdaftar dan dilindungi kebenaran — bukan terbuka.
        $laluan = collect(app('router')->getRoutes())
            ->first(fn ($r) => $r->uri() === 'api/v1/sales-copilot/requires-attention');

        $this->assertNotNull($laluan, 'Laluan sales-copilot/requires-attention tiada.');

        $this->assertContains(
            'perm:sales_copilot.view',
            $laluan->gatherMiddleware(),
            'Laluan SalesCopilot tiada kebenaran — boleh dibaca sesiapa sahaja.'
        );
    }

    public function test_api_tidak_memulangkan_data_tenant_lain(): void
    {
        $this->dalamTenant($this->t);

        // Data tenant satu dalam modul yang dibaca SalesCopilot
        \App\Modules\Prospecting\Models\Prospect::create([
            'first_name' => 'Rahsia',
            'last_name' => 'Tenant Satu',
            'email' => 'rahsia@copilot-tenant-satu.test',
            'company' => 'Syarikat Rahsia',
        ]);

        [$u2, $t2] = $this->daftar('t2@copilot.my', 'Copilot Tenant Dua Sdn Bhd');
        $this->sebagai($u2, $t2);

        $respons = $this->getJson($this->laluanApi);

        $this->assertStringNotContainsString(
            'Rahsia Tenant Satu',
            $respons->getContent(),
            'SalesCopilot memulangkan prospek tenant lain (IDOR).'
        );
    }
}
