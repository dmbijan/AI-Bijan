<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\BillingCharge;
use App\Modules\Billing\Models\BillingSubscription;
use App\Modules\Billing\Models\Reseller;
use App\Modules\Billing\Models\ResellerReferral;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Aliran langganan: cipta invois CHIP → tunggu webhook → aktifkan entitlemen
 * → rekod komisen reseller.
 *
 * ⚠️ PRINSIP: langganan HANYA diaktifkan oleh webhook yang disahkan. Respons
 * redirect dari pelayar (`success_redirect`) TIDAK PERNAH dipercayai —
 * ia boleh dipalsukan dengan menaip URL.
 */
class BillingService
{
    public function __construct(private ChipService $chip) {}

    /**
     * Harga langganan bulanan — dibaca dari jadual `plans` (diurus Super Admin).
     * Sandaran: `billing.plans` config untuk pemasangan yang belum ada plan dalam DB.
     */
    public function hargaBulanan(string $planCode): float
    {
        $plan = \App\Models\Plan::query()->where('code', $planCode)->where('is_active', true)->first();
        if ($plan) {
            return (float) $plan->price_monthly;
        }

        $harga = config("billing.plans.{$planCode}.price");
        if ($harga === null) {
            throw new RuntimeException("Pelan '{$planCode}' tiada atau tidak aktif.");
        }

        return (float) $harga;
    }

    /**
     * Pakej token AI yang tersedia — dari jadual `token_packages` (diurus Super Admin).
     *
     * ⚠️ Token WAJIB dipilih semasa langganan — ia bukan pilihan. Tanpa token,
     * ciri AI tidak berfungsi, jadi menjual langganan sahaja bermakna pelanggan
     * membayar untuk sistem yang sebahagian besarnya mati.
     *
     * @return array<string, array{name:string, tokens:int, price:float, kos_pembekal:float, validity_days:int}>
     */
    public function pakejToken(): array
    {
        $db = \App\Models\TokenPackage::query()->where('is_active', true)->orderBy('sort_order')->orderBy('price')->get();
        if ($db->isNotEmpty()) {
            return $db->mapWithKeys(fn ($p) => [$p->code => [
                'name' => $p->name,
                'tokens' => (int) $p->tokens,
                'price' => (float) $p->price,
                'kos_pembekal' => (float) ($p->supplier_cost ?? 0),
                'harga_beli_tokens' => $p->usage_limit,
                'validity_days' => (int) $p->validity_days,
            ]])->all();
        }

        return (array) config('billing.token_packages', []);
    }

    /**
     * Harga pakej token.
     */
    public function hargaToken(string $pakejCode): float
    {
        $pakej = $this->pakejToken()[$pakejCode] ?? null;

        if ($pakej === null) {
            throw new RuntimeException("Pakej token '{$pakejCode}' tiada.");
        }

        return (float) $pakej['price'];
    }

    /**
     * Analisis margin setiap pakej token.
     *
     * ⚠️ Angka ini datang dari config (`kos_pembekal`), yang mesti disahkan
     * terhadap kontrak pembekal. Jika pembekal naikkan harga, kemas kini
     * config — margin dikira semula secara automatik.
     *
     * @return array<string, array{
     *   nama:string, tokens:int, jual:float, beli:float,
     *   untung:float, margin_pct:float, kos_per_juta:float, untung_per_juta:float
     * }>
     */
    public function analisisMarginToken(): array
    {
        $hasil = [];

        foreach ($this->pakejToken() as $kod => $p) {
            $tokens = (int) $p['tokens'];
            $jual = (float) $p['price'];
            $beli = (float) ($p['kos_pembekal'] ?? 0);
            $untung = round($jual - $beli, 2);
            $juta = $tokens / 1_000_000;

            $hasil[$kod] = [
                'nama' => $p['name'],
                'tokens' => $tokens,
                'jual' => $jual,
                'beli' => $beli,
                'untung' => $untung,
                'margin_pct' => $jual > 0 ? round(($untung / $jual) * 100, 1) : 0.0,
                'kos_per_juta' => $juta > 0 ? round($beli / $juta, 4) : 0.0,
                'untung_per_juta' => $juta > 0 ? round($untung / $juta, 4) : 0.0,
            ];
        }

        return $hasil;
    }

    /**
     * Semak pakej token dijual di ATAS kos pembekal.
     *
     * ⚠️ Jika ini pulang pakej dalam senarai, JANGAN lancar — anda akan rugi
     * setiap bulan pada pelanggan yang menggunakan token penuh.
     *
     * @return array<string, string> kod pakej => mesej ralat
     */
    public function pakejRugi(): array
    {
        $rugi = [];

        foreach ($this->analisisMarginToken() as $kod => $a) {
            if ($a['untung'] <= 0) {
                $rugi[$kod] = sprintf(
                    '%s dijual RM%.2f tetapi kos pembekal RM%.2f — RUGI RM%.2f',
                    $a['nama'],
                    $a['jual'],
                    $a['beli'],
                    abs($a['untung'])
                );
            }

            // Amaran tambahan: margin di bawah 50% bermakna naik kos sedikit
            // sahaja akan memusnahkan keuntungan.
            if ($a['margin_pct'] < 50 && $a['untung'] > 0) {
                $rugi[$kod . '_nipis'] = sprintf(
                    '%s margin hanya %.1f%% — terlalu nipis',
                    $a['nama'],
                    $a['margin_pct']
                );
            }
        }

        return $rugi;
    }

    /**
     * Untung bersih platform untuk satu pelanggan dalam satu bulan.
     *
     * @param  string  $planCode     starter|growth|enterprise
     * @param  string  $pakejToken   kod pakej token
     * @param  bool    $adaReseller  true jika pelanggan dirujuk reseller
     */
    public function untungBersih(string $planCode, string $pakejToken, bool $adaReseller = false): array
    {
        $langganan = $this->hargaBulanan($planCode);
        $token = $this->hargaToken($pakejToken);
        $pakej = $this->pakejToken()[$pakejToken] ?? [];
        $kosToken = (float) ($pakej['kos_pembekal'] ?? 0);
        $komisen = $adaReseller ? (float) $this->komisenUntuk($planCode) : 0.0;

        $jumlah = round($langganan + $token, 2);
        $kos = round($kosToken, 2);
        $net = round($jumlah - $komisen - $kos, 2);

        return [
            'langganan' => $langganan,
            'token' => $token,
            'jumlah_dibilkan' => $jumlah,
            'komisen_reseller' => $komisen,
            'kos_token' => $kos,
            'untung_bersih' => $net,
            'margin_pct' => $jumlah > 0 ? round(($net / $jumlah) * 100, 1) : 0.0,
        ];
    }

    /**
     * Jumlah yang pelanggan bayar setiap bulan = langganan + token.
     */
    public function jumlahBulanan(string $planCode, string $pakejToken): float
    {
        return round($this->hargaBulanan($planCode) + $this->hargaToken($pakejToken), 2);
    }

    /**
     * Kira komisen reseller.
     *
     * ⚠️ Peraturan: komisen dikira dari LANGGANAN SAHAJA, bukan dari token.
     * Jika dikira dari jumlah penuh (langganan + token), reseller boleh dapat
     * RM100 dari RM438 — dan apabila pelanggan membeli pakej token besar,
     * komisen itu akan melebihi had 50% dan merosakkan margin platform.
     *
     * @return string nilai komisen (2 titik perpuluhan)
     */
    public function komisenUntuk(string $planCode, ?Reseller $reseller = null): string
    {
        $asas = $this->hargaBulanan($planCode);
        $nilai = (float) ($reseller?->commission_value ?? config('billing.reseller.commission_value', 100));
        $hadPct = (float) ($reseller?->commission_cap_pct ?? config('billing.reseller.commission_cap_pct', 50));

        $komisen = min($nilai, $asas * ($hadPct / 100));

        return number_format(max(0.0, $komisen), 2, '.', '');
    }

    /**
     * Cipta invois langganan + pakej token AI (WAJIB).
     *
     * ⚠️ Token bukan pilihan. `$pakejToken` mesti sah; jika tidak, transaksi
     * ditolak. Jangan sesekali mencipta langganan tanpa token — pelanggan akan
     * membayar untuk sistem yang ciri AI-nya mati.
     *
     * ⚠️ Jumlah invois = langganan + token. Tetapi komisen reseller dikira
     * dari LANGGANAN SAHAJA (lihat `ResellerCommissionService::rekodKomisen`).
     */
    public function ciptaInvoisLangganan(
        int $tenantId,
        string $planCode,
        string $pakejToken,
        array $pelanggan,
        array $url = []
    ): BillingCharge {
        // Sahkan pakej token dahulu — lebih awal gagal, lebih baik
        if (! array_key_exists($pakejToken, $this->pakejToken())) {
            throw new RuntimeException(
                "Pakej token '{$pakejToken}' tidak sah. Pilih salah satu: "
                . implode(', ', array_keys($this->pakejToken()))
            );
        }

        $period = now()->format('Y-m');
        $hargaLangganan = $this->hargaBulanan($planCode);
        $hargaToken = $this->hargaToken($pakejToken);
        $jumlah = round($hargaLangganan + $hargaToken, 2);

        return DB::transaction(function () use ($tenantId, $planCode, $pakejToken, $pelanggan, $url, $period, $hargaLangganan, $hargaToken, $jumlah) {
            $sedia = BillingCharge::where('tenant_id', $tenantId)
                ->where('kind', BillingCharge::KIND_SUBSCRIPTION)
                ->where('period_key', $period)
                ->lockForUpdate()
                ->first();

            if ($sedia && $sedia->sudahDibayar()) {
                throw new RuntimeException(
                    "Langganan untuk {$period} sudah dibayar. Tidak dicaj semula."
                );
            }

            if ($sedia && $sedia->chip_checkout_url && $sedia->status !== BillingCharge::STATUS_EXPIRED) {
                return $sedia;   // guna semula invois sedia ada
            }

            $langganan = BillingSubscription::firstOrCreate(
                ['tenant_id' => $tenantId, 'status' => BillingSubscription::STATUS_PENDING],
                [
                    'plan_code' => $planCode,
                    'amount' => $hargaLangganan,
                    'currency' => 'MYR',
                    'billing_cycle' => 'monthly',
                ]
            );

            $cas = $sedia ?? new BillingCharge([
                'tenant_id' => $tenantId,
                'billing_subscription_id' => $langganan->id,
                'kind' => BillingCharge::KIND_SUBSCRIPTION,
                'period_key' => $period,
                'currency' => 'MYR',
                'status' => BillingCharge::STATUS_CREATED,
            ]);

            // ⚠️ Jumlah = langganan + token. `base_amount` komisen TIDAK
            // menggunakan nilai ini — lihat ResellerCommissionService.
            $cas->amount = $jumlah;

            $invois = $this->chip->ciptaInvois([
                'email' => $pelanggan['email'],
                'full_name' => $pelanggan['name'] ?? null,
                'phone' => $pelanggan['phone'] ?? null,
                'amount' => $jumlah,
                'currency' => 'MYR',
                'reference' => sprintf('SUB-%d-%s', $tenantId, $period),
                'product_name' => sprintf(
                    'AI BIJAN %s + %s',
                    ucfirst($planCode),
                    $this->pakejToken()[$pakejToken]['name']
                ),
                'success_url' => $url['success'] ?? config('billing.urls.success'),
                'failure_url' => $url['failure'] ?? config('billing.urls.failure'),
                'cancel_url' => $url['cancel'] ?? config('billing.urls.failure'),
                'metadata' => [
                    'tenant_id' => $tenantId,
                    'plan' => $planCode,
                    'token_package' => $pakejToken,
                    'period' => $period,
                ],
            ]);

            $cas->fill([
                'chip_purchase_id' => $invois['id'] ?: null,
                'chip_checkout_url' => $invois['checkout_url'] ?: null,
                'status' => BillingCharge::STATUS_PENDING,
                'chip_payload' => [
                    'id' => $invois['id'],
                    'status' => $invois['status'],
                    // Simpan pecahan supaya boleh diaudit kemudian
                    'pecahan' => [
                        'langganan' => $hargaLangganan,
                        'token' => $hargaToken,
                        'pakej_token' => $pakejToken,
                    ],
                ],
            ])->save();

            return $cas->fresh();
        });
    }

    /**
     * Aktifkan langganan selepas bayaran disahkan.
     *
     * WAJIB idempoten — webhook boleh tiba lebih daripada sekali.
     *
     * @return bool true jika baru diaktifkan, false jika sudah aktif
     */
    public function aktifkanSelepasBayaran(BillingCharge $cas, ?string $paidAt = null): bool
    {
        return DB::transaction(function () use ($cas, $paidAt) {
            $cas = BillingCharge::withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->find($cas->id);

            if ($cas === null) {
                return false;
            }

            if ($cas->sudahDibayar()) {
                return false;   // sudah diproses — idempoten
            }

            $masa = $paidAt ? \Carbon\Carbon::parse($paidAt) : now();

            $cas->update([
                'status' => BillingCharge::STATUS_PAID,
                'paid_at' => $masa,
                'failed_at' => null,
                'failure_reason' => null,
            ]);

            $langganan = BillingSubscription::withoutGlobalScope('tenant')
                ->find($cas->billing_subscription_id);

            if ($langganan === null) {
                return true;   // cas dibayar, tetapi tiada langganan untuk diaktifkan
            }

            $tamat = $this->tambahBulan($masa);

            $langganan->update([
                'status' => BillingSubscription::STATUS_ACTIVE,
                'current_period_start' => $masa,
                'current_period_end' => $tamat,
            ]);

            // Rekod komisen reseller (jika tenant dirujuk)
            app(ResellerCommissionService::class)
                ->rekodKomisen($cas, $langganan);

            return true;
        });
    }

    /**
     * Tandakan bayaran gagal.
     */
    public function tandakanGagal(BillingCharge $cas, string $sebab): void
    {
        DB::transaction(function () use ($cas, $sebab) {
            $cas = BillingCharge::withoutGlobalScope('tenant')->lockForUpdate()->find($cas->id);

            if ($cas === null || $cas->sudahDibayar()) {
                return;   // jangan rosakkan rekod yang sudah dibayar
            }

            $cas->update([
                'status' => BillingCharge::STATUS_FAILED,
                'failed_at' => now(),
                'failure_reason' => mb_substr($sebab, 0, 500),
            ]);

            if ($cas->billing_subscription_id) {
                BillingSubscription::withoutGlobalScope('tenant')
                    ->where('id', $cas->billing_subscription_id)
                    ->where('status', BillingSubscription::STATUS_ACTIVE)
                    ->update(['status' => BillingSubscription::STATUS_PAST_DUE]);
            }
        });
    }

    /**
     * Refund — balikkan bayaran dan batalkan komisen yang belum matang.
     */
    public function tandakanRefund(BillingCharge $cas): void
    {
        DB::transaction(function () use ($cas) {
            $cas = BillingCharge::withoutGlobalScope('tenant')->lockForUpdate()->find($cas->id);

            if ($cas === null) {
                return;
            }

            $cas->update(['status' => BillingCharge::STATUS_REFUNDED]);

            app(ResellerCommissionService::class)->balikKomisen($cas, 'refund');
        });
    }

    private function tambahBulan(\Carbon\Carbon $dari): \Carbon\Carbon
    {
        return $dari->copy()->addMonthNoOverflow();
    }
}
