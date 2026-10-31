<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\BillingCharge;
use App\Modules\Billing\Models\BillingSubscription;
use App\Modules\Billing\Models\Reseller;
use App\Modules\Billing\Models\ResellerCommission;
use App\Modules\Billing\Models\ResellerPayout;
use App\Modules\Billing\Models\ResellerReferral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Komisen reseller berulang.
 *
 * DUA BELAS PERATURAN (semua diuji dalam `ResellerCommissionTest`)
 * ─────────────────────────────────────────────────────────────────
 *  1. Satu tenant = satu reseller (UNIQUE pada `reseller_referrals.tenant_id`)
 *  2. Satu komisen per bulan per rujukan (UNIQUE pada komisen)
 *  3. Asas komisen = jumlah SEBENAR dibayar, BUKAN harga senarai
 *     → pelanggan dapat diskaun RM288, komisen tetap RM100? TIDAK.
 *       Reseller tidak boleh dapat lebih daripada 50% bayaran sebenar.
 *  4. Komisen ≤ `commission_cap_pct` × bayaran (lalai 50%)
 *  5. Komisen jadi hak 30 hari selepas bayaran (`mature_at`)
 *  6. Churn → `pending` jadi `reversed`
 *  7. `approved` TIDAK dibatalkan oleh churn (sudah jadi hak)
 *  8. Snapshot bank semasa bayar (rekod tidak berubah)
 *  9. Tiada self-referral (semak user_id + e-mel)
 * 10. Cookie atribusi 90 hari
 * 11. IP di-hash (PDPA — lihat `ResellerClick::hashIp`)
 * 12. Tiada komisen jika langganan menunggak/tidak aktif
 *
 * ⚠️ PERATURAN EMAS: komisen TIDAK BOLEH melebihi bayaran sebenar. Jika kos
 * operasi naik dan margin jadi negatif, yang pertama mesti berhenti ialah
 * komisen — bukan perkhidmatan pelanggan.
 */
class ResellerCommissionService
{
    /**
     * Rekod komisen untuk satu cas yang baru dibayar.
     *
     * @return ResellerCommission|null null jika tiada reseller / syarat gagal
     */
    public function rekodKomisen(BillingCharge $cas, BillingSubscription $langganan): ?ResellerCommission
    {
        // Peraturan 12 — hanya untuk langganan aktif
        if ($langganan->status !== BillingSubscription::STATUS_ACTIVE) {
            return null;
        }

        $rujukan = ResellerReferral::where('tenant_id', $cas->tenant_id)->first();

        if ($rujukan === null) {
            return null;   // tenant ini bukan rujukan sesiapa
        }

        $reseller = Reseller::find($rujukan->reseller_id);

        if ($reseller === null || ! $reseller->aktif()) {
            return null;   // reseller digantung/ditamatkan — tiada komisen baru
        }

        // Peraturan 4 & 3 — had dan asas bayaran sebenar
        //
        // ⚠️ PENTING (dibetulkan 27 Okt 2026): `$cas->amount` ialah jumlah
        // LANGGANAN + TOKEN. Komisen reseller MESTI dikira dari LANGGANAN
        // SAHAJA. Jika dikira dari jumlah penuh, pelanggan yang membeli pakej
        // token besar akan menyebabkan komisen melebihi 50% dan memakan
        // margin platform.
        $jumlahInvois = (float) $cas->amount;

        $bayaranLangganan = (float) ($cas->chip_payload['pecahan']['langganan'] ?? 0);

        // Fallback untuk cas lama (sebelum pecahan disimpan): guna harga pelan.
        if ($bayaranLangganan <= 0) {
            $bayaranLangganan = (float) (\App\Models\Plan::query()->where('code', $langganan->plan_code)->value('price_monthly')
                ?? config('billing.plans.' . $langganan->plan_code . '.price', $jumlahInvois));
        }

        // Asas komisen TIDAK boleh melebihi jumlah invois sebenar.
        $bayaran = min($bayaranLangganan, $jumlahInvois);

        $nilai = (float) $reseller->kiraKomisen($bayaran);

        if ($nilai <= 0) {
            return null;
        }

        // Peraturan 9 — tiada self-referral
        if ($this->ialahSelfReferral($reseller, $cas->tenant_id)) {
            Log::warning('Komisen ditolak: self-referral', [
                'reseller_id' => $reseller->id,
                'tenant_id' => $cas->tenant_id,
            ]);

            return null;
        }

        $period = $cas->period_key ?: now()->format('Y-m');
        $masa = $cas->paid_at ?? now();

        // Peraturan 5 — matang 30 hari
        $matang = $masa->copy()->addDays(Reseller::HARI_MATANG);

        try {
            // Peraturan 2 — UNIQUE(reseller_referral_id, period)
            return ResellerCommission::withoutGlobalScope('tenant')->create([
                'reseller_id' => $reseller->id,
                'reseller_referral_id' => $rujukan->id,
                'tenant_id' => $cas->tenant_id,
                'billing_charge_id' => $cas->id,
                'period' => $period,
                'base_amount' => number_format($bayaran, 2, '.', ''),
                'commission_amount' => number_format($nilai, 2, '.', ''),
                'currency' => $cas->currency ?: 'MYR',
                'status' => ResellerCommission::STATUS_PENDING,
                'earned_at' => $masa,
                'mature_at' => $matang,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Sudah ada komisen untuk period ini — idempoten
            Log::info('Komisen sudah wujud untuk period ini', ['period' => $period]);

            return ResellerCommission::withoutGlobalScope('tenant')
                ->where('reseller_referral_id', $rujukan->id)
                ->where('period', $period)
                ->first();
        }
    }

    /**
     * Balikkan komisen (refund/churn).
     *
     * Peraturan 6: hanya `pending` boleh dibalikkan.
     * Peraturan 7: `approved` dan `paid` TIDAK dibatalkan automatik.
     *
     * @return int bilangan komisen yang dibalikkan
     */
    public function balikKomisen(BillingCharge $cas, string $sebab): int
    {
        $komisen = ResellerCommission::withoutGlobalScope('tenant')
            ->where('billing_charge_id', $cas->id)
            ->get();

        $dibalikkan = 0;

        foreach ($komisen as $k) {
            if (! $k->bolehDibatalkan()) {
                // `approved`/`paid` — jangan sentuh, tetapi beri amaran
                Log::warning('Komisen tidak dibalikkan (sudah jadi hak)', [
                    'commission_id' => $k->id,
                    'status' => $k->status,
                    'sebab' => $sebab,
                ]);

                continue;
            }

            $k->update([
                'status' => ResellerCommission::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversal_reason' => mb_substr($sebab, 0, 255),
            ]);

            $dibalikkan++;
        }

        return $dibalikkan;
    }

    /**
     * Sahkan komisen yang sudah matang (cron harian 02:00).
     *
     * @return int bilangan komisen yang di-approve
     */
    public function maturekanKomisen(): int
    {
        $sedia = ResellerCommission::withoutGlobalScope('tenant')
            ->where('status', ResellerCommission::STATUS_PENDING)
            ->whereNotNull('mature_at')
            ->where('mature_at', '<=', now())
            ->get();

        $bilangan = 0;

        foreach ($sedia as $k) {
            // Peraturan 12 — pastikan langganan masih aktif
            $langganan = BillingSubscription::withoutGlobalScope('tenant')
                ->where('tenant_id', $k->tenant_id)
                ->whereIn('status', [
                    BillingSubscription::STATUS_ACTIVE,
                    BillingSubscription::STATUS_PAST_DUE,
                ])
                ->first();

            if ($langganan === null) {
                $k->update([
                    'status' => ResellerCommission::STATUS_REVERSED,
                    'reversed_at' => now(),
                    'reversal_reason' => 'langganan tidak aktif',
                ]);

                continue;
            }

            $k->update([
                'status' => ResellerCommission::STATUS_APPROVED,
                'approved_at' => now(),
            ]);

            $bilangan++;
        }

        return $bilangan;
    }

    /**
     * Kesan churn — langganan tamat/batal → balikkan komisen `pending`.
     *
     * @return int bilangan komisen yang dibalikkan
     */
    public function kesanChurn(): int
    {
        $tenantBatal = BillingSubscription::withoutGlobalScope('tenant')
            ->whereIn('status', [
                BillingSubscription::STATUS_CANCELLED,
                BillingSubscription::STATUS_EXPIRED,
                BillingSubscription::STATUS_SUSPENDED,
            ])
            ->pluck('tenant_id')
            ->unique()
            ->all();

        if ($tenantBatal === []) {
            return 0;
        }

        $sedia = ResellerCommission::withoutGlobalScope('tenant')
            ->where('status', ResellerCommission::STATUS_PENDING)
            ->whereIn('tenant_id', $tenantBatal)
            ->get();

        $bilangan = 0;

        foreach ($sedia as $k) {
            $k->update([
                'status' => ResellerCommission::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversal_reason' => 'churn — langganan tamat',
            ]);

            $bilangan++;
        }

        return $bilangan;
    }

    /**
     * Bina payout automatik (cron 1hb 04:00).
     *
     * Hanya `approved` dikumpul. Baki < `MIN_PAYOUT` dikekalkan untuk bulan
     * berikutnya.
     *
     * @return array<int, ResellerPayout>
     */
    public function binaPayout(): array
    {
        $hasil = [];

        $resellerIds = ResellerCommission::withoutGlobalScope('tenant')
            ->where('status', ResellerCommission::STATUS_APPROVED)
            ->distinct()
            ->pluck('reseller_id')
            ->all();

        foreach ($resellerIds as $resellerId) {
            $reseller = Reseller::find($resellerId);

            if ($reseller === null || ! $reseller->bank_details) {
                Log::warning('Payout dilangkau: bank_details tiada', ['reseller_id' => $resellerId]);

                continue;
            }

            $komisen = ResellerCommission::withoutGlobalScope('tenant')
                ->where('reseller_id', $resellerId)
                ->where('status', ResellerCommission::STATUS_APPROVED)
                ->get();

            $jumlah = (float) $komisen->sum('commission_amount');

            if ($jumlah < Reseller::MIN_PAYOUT) {
                continue;   // baki dikekalkan
            }

            $payout = DB::transaction(function () use ($reseller, $komisen, $jumlah) {
                $p = ResellerPayout::create([
                    'reseller_id' => $reseller->id,
                    'amount' => number_format($jumlah, 2, '.', ''),
                    'currency' => 'MYR',
                    'status' => ResellerPayout::STATUS_PENDING,
                    // Peraturan 8 — snapshot bank
                    'bank_snapshot' => $reseller->bank_details,
                    'commission_count' => $komisen->count(),
                    'period_from' => $komisen->min('period'),
                    'period_to' => $komisen->max('period'),
                ]);

                ResellerCommission::withoutGlobalScope('tenant')
                    ->whereIn('id', $komisen->pluck('id'))
                    ->update([
                        'status' => ResellerCommission::STATUS_PAID,
                        'paid_at' => now(),
                    ]);

                return $p;
            });

            $hasil[] = $payout;
        }

        return $hasil;
    }

    /**
     * Peraturan 9 — semak self-referral.
     *
     * Dua laluan:
     *   1. Reseller menjual kepada syarikatnya sendiri (`tenants.reseller_id`)
     *   2. E-mel reseller sama dengan e-mel PEMILIK tenant (melalui `tenant_users`)
     *
     * ⚠️ `tenants` TIADA kolum `owner_email`. Pemilik dicari melalui
     * `tenant_users` + `role_tenant_user`, atau `created_by` jika ada.
     * Jangan andaikan atribut yang tidak wujud (ia meletup dengan
     * MissingAttributeException).
     */
    private function ialahSelfReferral(Reseller $reseller, int $tenantId): bool
    {
        // Laluan 1 — syarikat sendiri
        $tenant = \App\Models\Tenant::find($tenantId);

        if ($tenant !== null && (int) ($tenant->reseller_id ?? 0) === (int) $reseller->id) {
            return true;
        }

        if (empty($reseller->email)) {
            return false;
        }

        // Laluan 2 — e-mel pemilik/ahli tenant
        $emelAhli = \Illuminate\Support\Facades\DB::table('tenant_users')
            ->join('users', 'users.id', '=', 'tenant_users.user_id')
            ->where('tenant_users.tenant_id', $tenantId)
            ->where('tenant_users.status', 'active')
            ->pluck('users.email')
            ->filter()
            ->all();

        foreach ($emelAhli as $emel) {
            if (strcasecmp($reseller->email, (string) $emel) === 0) {
                return true;
            }
        }

        return false;
    }
}
