<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\BillingCharge;
use App\Modules\Billing\Models\BillingSubscription;
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

    /** Harga langganan — dibaca dari config, bukan hardcode tersebar. */
    public function hargaBulanan(string $planCode): float
    {
        $harga = config("billing.plans.{$planCode}.price");

        if ($harga === null) {
            throw new RuntimeException("Pelan '{$planCode}' tiada dalam config billing.plans.");
        }

        return (float) $harga;
    }

    /**
     * Cipta invois langganan untuk satu tenant.
     *
     * ⚠️ Jika invois untuk period ini sudah wujud dan belum dibayar, ia
     * DIGUNAKAN SEMULA (idempotent). Jika sudah dibayar, tolak — jangan
     * caj dua kali.
     */
    public function ciptaInvoisLangganan(
        int $tenantId,
        string $planCode,
        array $pelanggan,
        array $url = []
    ): BillingCharge {
        $period = now()->format('Y-m');
        $harga = $this->hargaBulanan($planCode);

        return DB::transaction(function () use ($tenantId, $planCode, $pelanggan, $url, $period, $harga) {
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
                    'amount' => $harga,
                    'currency' => 'MYR',
                    'billing_cycle' => 'monthly',
                ]
            );

            $cas = $sedia ?? new BillingCharge([
                'tenant_id' => $tenantId,
                'billing_subscription_id' => $langganan->id,
                'kind' => BillingCharge::KIND_SUBSCRIPTION,
                'period_key' => $period,
                'amount' => $harga,
                'currency' => 'MYR',
                'status' => BillingCharge::STATUS_CREATED,
            ]);

            $invois = $this->chip->ciptaInvois([
                'email' => $pelanggan['email'],
                'full_name' => $pelanggan['name'] ?? null,
                'phone' => $pelanggan['phone'] ?? null,
                'amount' => $harga,
                'currency' => 'MYR',
                'reference' => sprintf('SUB-%d-%s', $tenantId, $period),
                'product_name' => 'Langganan AI BIJAN (' . ucfirst($planCode) . ')',
                'success_url' => $url['success'] ?? config('billing.urls.success'),
                'failure_url' => $url['failure'] ?? config('billing.urls.failure'),
                'cancel_url' => $url['cancel'] ?? config('billing.urls.failure'),
                'metadata' => ['tenant_id' => $tenantId, 'plan' => $planCode, 'period' => $period],
            ]);

            $cas->fill([
                'chip_purchase_id' => $invois['id'] ?: null,
                'chip_checkout_url' => $invois['checkout_url'] ?: null,
                'status' => BillingCharge::STATUS_PENDING,
                // ⚠️ JANGAN simpan payload penuh — mungkin ada data peribadi
                'chip_payload' => [
                    'id' => $invois['id'],
                    'status' => $invois['status'],
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
