<?php

namespace Tests\Feature;

use App\Modules\Billing\Models\PaymentLink;
use App\Modules\POS\Models\POSSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Fasa 1.4 — bayaran pelanggan kedai melalui akaun CHIP kedai sendiri (tiada panggilan CHIP sebenar). */
class KedaiPaymentTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private string $priv = "";

    private string $pub = "";

    private const KUNCI = 'kunci-chip-kedai-ujian-1234567890ABCD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        $k = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($k, $this->priv);
        $this->pub = openssl_pkey_get_details($k)['key'];
    }

    private function chipTiruan(): void
    {
        Http::fake([
            '*/public_key/' => Http::response(json_encode($this->pub), 200),
            '*/purchases/' => Http::response(['id' => 'chip-p-001', 'checkout_url' => 'https://gate.chip-in.asia/p/chip-p-001/', 'status' => 'created'], 201),
        ]);
    }

    private function sedia(): array
    {
        $this->chipTiruan();
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $http->putJson('/api/v1/pos/payment-gateway', ['brand_id' => 'brand-kedai', 'api_key' => self::KUNCI])->assertOk();
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1'])->assertCreated();
        $sale = $http->postJson('/api/v1/pos/sales/draft', ['register_id' => $reg->json('id') ?? $reg->json('data.id')])->assertCreated();
        $id = $sale->json('id') ?? $sale->json('data.id');
        $http->postJson("/api/v1/pos/sales/{$id}/items", ['product_name' => 'Jambangan Ros', 'quantity' => 2, 'unit_price' => 25])->assertSuccessful();

        return [$u, $t, $http, $id];
    }

    private function hantarCallback($t, array $badan, ?string $sig = null)
    {
        $raw = json_encode($badan);
        if ($sig === null) {
            openssl_sign($raw, $s, $this->priv, OPENSSL_ALGO_SHA256);
            $sig = base64_encode($s);
        }
        $this->flushHeaders();
        $this->flushSession();
        auth()->forgetGuards();

        return $this->call('POST', "/api/v1/webhooks/chip-kedai/{$t->ulid}", [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $sig], $raw);
    }

    #[Test]
    public function kunci_disulitkan_dan_bertopeng(): void
    {
        [, $t, $http] = $this->sedia();

        $r = $http->getJson('/api/v1/pos/payment-gateway')->assertOk();
        $this->assertSame('••••ABCD', $r->json('data.kunci'));
        $this->assertStringNotContainsString(self::KUNCI, $r->getContent());
        $this->assertNotSame(self::KUNCI, DB::table('tenant_payment_gateways')->where('tenant_id', $t->id)->value('api_key'), 'Kunci mesti disulitkan dalam DB.');
    }

    #[Test]
    public function kunci_salah_ditolak(): void
    {
        Http::fake(['*/public_key/' => Http::response('Unauthorized', 401)]);
        [$u, $t] = $this->daftar('lain@kedai.my', 'Kedai Lain');

        $this->sebagai($u, $t)->putJson('/api/v1/pos/payment-gateway', ['brand_id' => 'b', 'api_key' => 'kunci-salah-xxxxxxxxxxxxxxxx'])->assertStatus(422);
    }

    #[Test]
    public function pautan_bayar_dalam_sen_dengan_callback_kedai(): void
    {
        [, $t, $http, $id] = $this->sedia();

        $r = $http->postJson("/api/v1/pos/sales/{$id}/pautan-bayar", ['email' => 'pelanggan@contoh.my'])->assertCreated();

        $this->assertSame('https://gate.chip-in.asia/p/chip-p-001/', $r->json('data.url'));
        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/purchases/')
            && $req['purchase']['products'][0]['price'] === 5000
            && $req['brand_id'] === 'brand-kedai'
            && str_contains($req['success_callback'], '/api/v1/webhooks/chip-kedai/'.$t->ulid)
            && $req->hasHeader('Authorization', 'Bearer '.self::KUNCI));
    }

    #[Test]
    public function callback_sah_selesaikan_jualan_sekali_sahaja(): void
    {
        [, $t, $http, $id] = $this->sedia();
        $http->postJson("/api/v1/pos/sales/{$id}/pautan-bayar")->assertCreated();

        $this->hantarCallback($t, ['id' => 'chip-p-001', 'status' => 'paid', 'event_type' => 'purchase.paid'])->assertOk()->assertJson(['ok' => true, 'mesej' => 'Dibayar']);
        $this->hantarCallback($t, ['id' => 'chip-p-001', 'status' => 'paid', 'event_type' => 'purchase.paid'])->assertJson(['mesej' => 'Sudah diproses']);

        $sale = POSSale::withoutGlobalScopes()->find($id);
        $this->assertSame('completed', $sale->status);
        $this->assertSame('online', $sale->payment_method);
        $this->assertSame('paid', PaymentLink::withoutGlobalScopes()->where('provider_ref', 'chip-p-001')->value('status'));
    }

    #[Test]
    public function callback_tandatangan_palsu_ditolak(): void
    {
        [, $t, $http, $id] = $this->sedia();
        $http->postJson("/api/v1/pos/sales/{$id}/pautan-bayar")->assertCreated();

        $this->hantarCallback($t, ['id' => 'chip-p-001', 'status' => 'paid'], base64_encode('palsu'))->assertJson(['ok' => false]);

        $this->assertSame('draft', POSSale::withoutGlobalScopes()->find($id)->status);
    }

    #[Test]
    public function juruwang_tidak_boleh_palsukan_bayaran_online(): void
    {
        [, , $http] = $this->sedia();
        $reg = $http->getJson('/api/v1/pos/registers')->json('0.id');

        $http->postJson('/api/v1/pos/sales/checkout', ['register_id' => $reg, 'items' => [['product_name' => 'X', 'quantity' => 1, 'unit_price' => 5]],
            'tenders' => [['method' => 'online', 'amount' => 5]]])->assertStatus(422);
    }
}
