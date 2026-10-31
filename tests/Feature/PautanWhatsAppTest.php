<?php

namespace Tests\Feature;

use App\Support\WhatsApp\PautanWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Fasa 1.5a — pautan wa.me untuk resit & laporan (tiada mesej dihantar oleh sistem). */
class PautanWhatsAppTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    #[Test]
    public function nombor_malaysia_dinormalkan(): void
    {
        $this->assertSame('60123456789', PautanWhatsApp::nombor('012-345 6789'));
        $this->assertSame('60123456789', PautanWhatsApp::nombor('+60 12-345 6789'));
        $this->assertSame('60123456789', PautanWhatsApp::nombor('0060123456789'));
        $this->assertSame('60123456789', PautanWhatsApp::nombor('123456789'));
        $this->assertSame('6591234567', PautanWhatsApp::nombor('+65 9123 4567'));
        $this->assertNull(PautanWhatsApp::nombor('abc'));
        $this->assertNull(PautanWhatsApp::nombor('123'));
    }

    #[Test]
    public function resit_jualan_ke_whatsapp_pelanggan(): void
    {
        $this->setUpAkses();
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1'])->assertCreated();
        $id = $http->postJson('/api/v1/pos/sales/checkout', [
            'register_id' => $reg->json('id') ?? $reg->json('data.id'),
            'items' => [['product_name' => 'Jambangan Ros', 'quantity' => 2, 'unit_price' => 25]],
            'tenders' => [['method' => 'duitnow', 'amount' => 50, 'reference' => 'DN1']],
        ])->assertCreated()->json('data.id');

        $r = $http->getJson("/api/v1/pos/sales/{$id}/resit-whatsapp?telefon=012-3456789")->assertOk();

        $this->assertStringStartsWith('https://wa.me/60123456789?text=', $r->json('url'));
        $this->assertStringContainsString('*Kedai Bunga Sari*', $r->json('teks'));
        $this->assertStringContainsString('2 × Jambangan Ros — RM 50.00', $r->json('teks'));
        $this->assertStringContainsString('DuitNow QR', $r->json('teks'));

        $http->getJson("/api/v1/pos/sales/{$id}/resit-whatsapp?telefon=123")->assertStatus(422);
        $this->assertStringStartsWith('https://wa.me/?text=', $http->getJson("/api/v1/pos/sales/{$id}/resit-whatsapp")->json('url'));
    }

    #[Test]
    public function laporan_ejen_ke_whatsapp(): void
    {
        $this->setUpAkses();
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');

        $r = $this->sebagai($u, $t)->postJson('/api/v1/ai-agents/whatsapp-pautan', ['teks' => "*Tutup kedai*\nJualan: RM 10.00"])->assertOk();

        $this->assertSame('https://wa.me/?text='.rawurlencode("*Tutup kedai*\nJualan: RM 10.00"), $r->json('url'));
    }
}
