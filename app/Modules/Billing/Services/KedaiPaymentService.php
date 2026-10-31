<?php

namespace App\Modules\Billing\Services;

use App\Models\Tenant;
use App\Modules\Billing\Models\PaymentLink;
use App\Modules\Billing\Models\TenantPaymentGateway;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Services\SalePaymentService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Bayaran pelanggan kedai (Fasa 1.4) melalui akaun CHIP milik kedai.
 * Status "dibayar" hanya diterima daripada callback CHIP yang tandatangannya sah — bukan redirect pelayar
 * (Master Inventory §7.1: "redirect browser bukan bukti muktamad").
 */
class KedaiPaymentService
{
    /** Simpan kunci CHIP kedai selepas disahkan dengan CHIP (ambil kunci awam untuk callback). */
    public function simpanTetapan(string $brandId, string $apiKey): TenantPaymentGateway
    {
        $kunciAwam = ChipService::denganKunci($apiKey, $brandId)->kunciAwam();

        return TenantPaymentGateway::updateOrCreate(['provider' => 'chip'], [
            'brand_id' => $brandId, 'api_key' => $apiKey, 'public_key' => $kunciAwam, 'is_active' => true, 'verified_at' => now(),
        ]);
    }

    public function gateway(): ?TenantPaymentGateway
    {
        return TenantPaymentGateway::where('provider', 'chip')->where('is_active', true)->first();
    }

    /** Pautan bayar CHIP untuk jualan POS draf (pesanan meja, pesanan WhatsApp, dll.). */
    public function pautanJualan(POSSale $sale, ?string $email, ?string $emailPengguna): PaymentLink
    {
        if ($sale->status !== 'draft') {
            throw new RuntimeException('Hanya jualan draf boleh dibuat pautan bayar.');
        }
        $g = $this->gateway();
        if (! $g) {
            throw new RuntimeException('Akaun CHIP kedai belum disambung. Tetapkan di POS → Kaunter → Bayaran Online.');
        }
        $sale->load('items');
        if ($sale->items->isEmpty() || (float) $sale->total_amount <= 0) {
            throw new RuntimeException('Jualan kosong tidak boleh dibayar.');
        }

        $chip = ChipService::denganKunci($g->api_key, $g->brand_id, $g->public_key);
        $tenant = app(TenantContext::class)->tenant();
        $produk = $sale->items->map(fn ($i) => [
            'name' => mb_substr(rtrim(rtrim(number_format((float) $i->quantity, 3, '.', ''), '0'), '.').' × '.$i->product_name, 0, 256),
            'quantity' => 1,
            'price' => $chip->kepadaSen($i->line_total),
        ])->values()->all();
        // Selaras jumlah: caj perkhidmatan/diskaun peringkat jualan dimasukkan sebagai satu baris pelarasan.
        $beza = $chip->kepadaSen($sale->total_amount) - array_sum(array_column($produk, 'price'));
        if ($beza > 0) {
            $produk[] = ['name' => 'Caj perkhidmatan / cukai', 'quantity' => 1, 'price' => $beza];
        } elseif ($beza < 0) {
            $produk[0]['discount'] = -$beza; // CHIP: diskaun dalam sen pada produk
        }

        $hasil = $chip->ciptaInvois([
            'email' => $email ?: $emailPengguna,
            'amount' => (float) $sale->total_amount,
            'products' => $produk,
            'reference' => $sale->sale_no,
            'product_name' => $tenant->name,
            'success_url' => url('/app/pos?bayar=berjaya'),
            'failure_url' => url('/app/pos?bayar=gagal'),
            'success_callback' => url('/api/v1/webhooks/chip-kedai/'.$tenant->ulid),
            'metadata' => ['sale_id' => $sale->id, 'tenant' => $tenant->ulid],
        ]);

        return PaymentLink::create([
            'provider' => 'chip', 'payable_type' => 'pos_sale', 'payable_id' => $sale->id,
            'provider_ref' => $hasil['id'], 'url' => $hasil['checkout_url'], 'amount' => $sale->total_amount, 'status' => 'created',
        ]);
    }

    /** Callback CHIP untuk satu kedai. Pulangkan [ok, mesej]. Sentiasa idempoten. */
    public function callback(string $ulid, string $rawBody, ?string $signature): array
    {
        $tenant = Tenant::where('ulid', $ulid)->first();
        if (! $tenant) {
            return [false, 'Kedai tidak dijumpai'];
        }
        $ctx = app(TenantContext::class);
        $ctx->set($tenant);
        try {
            $g = $this->gateway();
            if (! $g || ! ChipService::denganKunci($g->api_key, $g->brand_id, $g->public_key)->sahkanTandatangan($rawBody, $signature)) {
                return [false, 'Tandatangan tidak sah'];
            }
            $d = json_decode($rawBody, true) ?: [];
            $link = PaymentLink::where('provider_ref', (string) ($d['id'] ?? ''))->first();
            if (! $link) {
                return [true, 'Pembelian tidak dikenali (diabaikan)'];
            }
            if ($link->status === 'paid') {
                return [true, 'Sudah diproses'];
            }
            if (($d['status'] ?? '') !== 'paid') {
                return [true, 'Status '.($d['status'] ?? '?').' diabaikan'];
            }
            $sale = POSSale::find($link->payable_id);
            if ($sale && $sale->status === 'draft') {
                app(SalePaymentService::class)->bayar($sale, [['method' => 'online', 'amount' => (float) $sale->total_amount, 'reference' => $link->provider_ref]], null);
            }
            $link->update(['status' => 'paid', 'paid_at' => now()]);

            return [true, 'Dibayar'];
        } catch (\Throwable $e) {
            Log::error('Callback CHIP kedai gagal', ['tenant' => $ulid, 'error' => mb_substr($e->getMessage(), 0, 200)]);

            return [false, 'Ralat dalaman'];
        } finally {
            $ctx->clear();
        }
    }
}
