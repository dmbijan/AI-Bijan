<?php

namespace Tests\Feature;

use App\Modules\Invoicing\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Syarikat kedua mesti boleh buka kaunter, jual dan keluarkan invois (nombor dokumen unik per syarikat). */
class NomborDokumenPerTenantTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    private function bukaKaunterDanJual($u, $t): array
    {
        $http = $this->sebagai($u, $t);
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1'])->assertCreated();
        $id = $reg->json('id') ?? $reg->json('data.id');
        $jual = $http->postJson('/api/v1/pos/sales/checkout', [
            'register_id' => $id, 'items' => [['product_name' => 'Kopi', 'quantity' => 1, 'unit_price' => 3]],
            'tenders' => [['method' => 'cash', 'amount' => 3]],
        ])->assertCreated();

        return [$reg->json('code') ?? $reg->json('data.code'), $jual->json('data.sale_no')];
    }

    #[Test]
    public function dua_syarikat_boleh_berniaga_serentak(): void
    {
        [$ua, $ta] = $this->daftar('a@kedai.my', 'Kedai A');
        [$ub, $tb] = $this->daftar('b@kedai.my', 'Kedai B');

        [$kodA, $noA] = $this->bukaKaunterDanJual($ua, $ta);
        [$kodB, $noB] = $this->bukaKaunterDanJual($ub, $tb);

        // Setiap syarikat bermula dengan nombor sendiri
        $this->assertSame($kodA, $kodB);
        $this->assertSame($noA, $noB);

        foreach ([$ta, $tb] as $t) {
            $this->dalamTenant($t);
            $i = Invoice::create(['tenant_id' => $t->id, 'customer_name' => 'Pelanggan', 'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(), 'subtotal' => 10, 'tax_total' => 0, 'grand_total' => 10,
                'amount_paid' => 0, 'status' => 'draft', 'currency' => 'MYR']);
            $this->assertNotEmpty($i->invoice_number);
        }
    }
}
