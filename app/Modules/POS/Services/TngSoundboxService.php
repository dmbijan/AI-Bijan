<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSTngNotification;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleTender;
use Illuminate\Support\Facades\DB;

/**
 * TNG merchant soundbox (BAB 88 / T-82).
 *
 * Dipindah daripada B-POS `TngSoundboxService`. Aliran:
 *   1. Notifikasi bayaran TnG masuk (webhook / e-mel parser / manual).
 *   2. Padan dengan jualan TnG yang BELUM BAYAR dan jumlahnya SAMA,
 *      first-come-first-served, dalam tetingkap masa.
 *   3. Jika tiada padanan → simpan sebagai `unclaimed` untuk padanan manual.
 *
 * ⚠️ Idempotency: `unique(tenant_id, reference)` pada `pos_tng_notifications`
 * menghalang notifikasi sama daripada melunaskan dua jualan (isu S-5 B-POS).
 */
class TngSoundboxService
{
    public function diaktifkan(): bool
    {
        return (bool) config('pos.tng_soundbox.enabled', false);
    }

    private function tetingkapJam(): int
    {
        return max(1, (int) config('pos.tng_soundbox.match_window_hours', 12));
    }

    /**
     * Rekod notifikasi masuk; cuba padankan.
     *
     * @return array{matched: bool, sale_id: int|null, notification_id: int, amount: float, duplicate: bool}
     */
    public function rekod(int $tenantId, float $amaun, ?string $rujukan, ?string $mesejMentah): array
    {
        return DB::transaction(function () use ($tenantId, $amaun, $rujukan, $mesejMentah) {
            // Idempotency — rujukan sama tidak direkod dua kali.
            if ($rujukan !== null && $rujukan !== '') {
                $sedia = POSTngNotification::where('tenant_id', $tenantId)
                    ->where('reference', $rujukan)
                    ->first();

                if ($sedia) {
                    return [
                        'matched' => $sedia->sudahPadan(),
                        'sale_id' => $sedia->sale_id,
                        'notification_id' => $sedia->id,
                        'amount' => (float) $sedia->amount,
                        'duplicate' => true,
                    ];
                }
            }

            $jualan = POSSale::forTenant($tenantId)
                ->where('payment_status', 'pending')
                ->where('total_amount', round($amaun, 2))
                ->where('created_at', '>=', now()->subHours($this->tetingkapJam()))
                ->whereHas('tenders', fn ($q) => $q->where('method', POSSaleTender::METHOD_TNG))
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            $notifikasi = POSTngNotification::create([
                'tenant_id' => $tenantId,
                'amount' => round($amaun, 2),
                'reference' => $rujukan ?: null,
                'raw_message' => $mesejMentah,
                'status' => $jualan ? POSTngNotification::STATUS_MATCHED : POSTngNotification::STATUS_UNCLAIMED,
                'sale_id' => $jualan?->id,
                'received_at' => now(),
                'matched_at' => $jualan ? now() : null,
            ]);

            if ($jualan) {
                $this->lunasJualan($jualan, $rujukan);
            }

            return [
                'matched' => (bool) $jualan,
                'sale_id' => $jualan?->id,
                'notification_id' => $notifikasi->id,
                'amount' => round($amaun, 2),
                'duplicate' => false,
            ];
        });
    }

    /** Padanan manual: lekatkan notifikasi terkumpul pada jualan terpilih. */
    public function matchManual(POSTngNotification $notifikasi, POSSale $jualan, int $penggunaId, int $tenantId): void
    {
        if ($notifikasi->tenant_id !== $tenantId || $jualan->tenant_id !== $tenantId) {
            throw new \RuntimeException('Padanan merentas tenant tidak dibenarkan.');
        }

        DB::transaction(function () use ($notifikasi, $jualan, $penggunaId) {
            $this->lunasJualan($jualan, $notifikasi->reference);

            $notifikasi->update([
                'status' => POSTngNotification::STATUS_MATCHED,
                'sale_id' => $jualan->id,
                'matched_by' => $penggunaId,
                'matched_at' => now(),
            ]);
        });
    }

    /** Tandakan notifikasi terkumpul sebagai lunas bagi jualan tertentu. */
    private function lunasJualan(POSSale $jualan, ?string $rujukan): void
    {
        $jualan->update(['payment_status' => 'paid']);

        $jualan->tenders()
            ->where('method', POSSaleTender::METHOD_TNG)
            ->update([
                'payment_status' => POSSaleTender::STATUS_PAID,
                'reference' => $rujukan,
                'paid_at' => now(),
            ]);
    }

    /** Label bunyi/paparan soundbox. */
    public function labelAmaun(float $amaun): string
    {
        return 'Ringgit Malaysia ' . number_format($amaun, 2, '.', '') . ' diterima';
    }
}
