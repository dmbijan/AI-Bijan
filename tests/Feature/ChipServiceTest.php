<?php

namespace Tests\Feature;

use App\Modules\Billing\Services\ChipService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** CHIP ikut dokumentasi rasmi: harga dalam sen; X-Signature = RSA-SHA256 atas badan mentah. */
class ChipServiceTest extends TestCase
{
    private function kunci(): array
    {
        $k = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($k, $priv);

        return [$priv, openssl_pkey_get_details($k)['key']];
    }

    #[Test]
    public function harga_dihantar_dalam_sen(): void
    {
        Http::fake(['*/purchases/' => Http::response(['id' => 'p1', 'checkout_url' => 'https://gate.chip-in.asia/p/p1/', 'status' => 'created'], 201)]);

        ChipService::denganKunci('kunci-ujian', 'brand-ujian')->ciptaInvois([
            'email' => 'a@b.my', 'amount' => 388, 'reference' => 'R1', 'success_url' => 'https://x/ok', 'failure_url' => 'https://x/gagal',
        ]);

        Http::assertSent(fn ($req) => $req['purchase']['products'][0]['price'] === 38800
            && ! isset($req['purchase']['total'])
            && $req['brand_id'] === 'brand-ujian'
            && $req->hasHeader('Authorization', 'Bearer kunci-ujian'));
    }

    #[Test]
    public function tandatangan_sah_atas_badan_sahaja(): void
    {
        [$priv, $pub] = $this->kunci();
        $badan = '{"id":"p1","event_type":"purchase.paid","status":"paid"}';
        openssl_sign($badan, $sig, $priv, OPENSSL_ALGO_SHA256);
        $chip = ChipService::denganKunci('k', 'b', $pub);

        $this->assertTrue($chip->sahkanTandatangan($badan, base64_encode($sig)));
        $this->assertFalse($chip->sahkanTandatangan($badan.' ', base64_encode($sig)), 'Badan diubah mesti ditolak.');

        openssl_sign('1700000000.'.$badan, $sigLama, $priv, OPENSSL_ALGO_SHA256);
        $this->assertFalse($chip->sahkanTandatangan($badan, base64_encode($sigLama)), 'Format lama (timestamp.body) bukan format CHIP.');
        $this->assertFalse(ChipService::denganKunci('k', 'b', null)->sahkanTandatangan($badan, base64_encode($sig)), 'Tiada kunci awam → tolak.');
    }
}
