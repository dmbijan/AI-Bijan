<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\BillingCharge;
use App\Modules\Billing\Models\BillingWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pemproses webhook CHIP.
 *
 * ⚠️ PERATURAN KESELAMATAN (jangan langgar)
 * ─────────────────────────────────────────
 *  1. Tandatangan WAJIB disahkan SEBELUM apa-apa diproses
 *  2. Timestamp mesti dalam toleransi (elak replay)
 *  3. Idempoten — webhook sama boleh tiba berkali-kali
 *  4. JANGAN percaya `success_redirect` dari pelayar
 *  5. Jumlah mesti sepadan dengan `billing_charges.amount` — jika tidak, tolak
 *  6. Semua ralat dilog, TIDAK dilempar ke CHIP (elak retry storm)
 */
class ChipWebhookService
{
    public function __construct(
        private ChipService $chip,
        private BillingService $billing,
    ) {}

    /**
     * Proses webhook mentah.
     *
     * @return array{ok: bool, mesej: string}
     */
    public function proses(string $rawBody, ?string $signature, ?string $timestamp): array
    {
        // ── 1. Pengesahan tandatangan ──────────────────────────────
        if (! $this->chip->sahkanTandatangan($rawBody, $signature, $timestamp)) {
            return ['ok' => false, 'mesej' => 'Tandatangan tidak sah'];
        }

        $data = json_decode($rawBody, true);

        if (! is_array($data)) {
            return ['ok' => false, 'mesej' => 'Badan JSON tidak sah'];
        }

        $eventType = (string) ($data['event_type'] ?? $data['type'] ?? 'unknown');
        $purchaseId = (string) ($data['id'] ?? $data['purchase_id'] ?? '');
        $subscriptionId = $data['subscription_id'] ?? null;

        // ── 2. Idempoten ──────────────────────────────────────────
        $sedia = BillingWebhookEvent::where('provider', 'chip')
            ->where('event_type', $eventType)
            ->where('chip_purchase_id', $purchaseId ?: null)
            ->whereIn('status', ['processed', 'ignored'])
            ->exists();

        if ($sedia) {
            return ['ok' => true, 'mesej' => 'Sudah diproses (idempoten)'];
        }

        $rekod = BillingWebhookEvent::create([
            'provider' => 'chip',
            'event_type' => $eventType,
            'chip_purchase_id' => $purchaseId ?: null,
            'chip_subscription_id' => $subscriptionId,
            'status' => 'received',
            'signature' => $signature ? mb_substr($signature, 0, 255) : null,
            // ⚠️ Simpan tanpa data sensitif
            'payload' => $this->sunting($data),
        ]);

        try {
            $hasil = DB::transaction(fn () => $this->kendali($eventType, $purchaseId, $data));

            $rekod->update([
                'status' => $hasil['status'],
                'processed_at' => now(),
            ]);

            return ['ok' => true, 'mesej' => $hasil['mesej']];
        } catch (\Throwable $e) {
            // ⚠️ JANGAN lempar — CHIP akan retry berulang kali
            Log::error('Webhook CHIP gagal diproses', [
                'event' => $eventType,
                'purchase' => $purchaseId,
                'ralat' => $e->getMessage(),
            ]);

            $rekod->update([
                'status' => 'failed',
                'processing_error' => mb_substr($e->getMessage(), 0, 500),
            ]);

            return ['ok' => false, 'mesej' => 'Ralat diproses'];
        }
    }

    /**
     * @return array{status: string, mesej: string}
     */
    private function kendali(string $eventType, string $purchaseId, array $data): array
    {
        $cas = BillingCharge::withoutGlobalScope('tenant')
            ->where('chip_purchase_id', $purchaseId)
            ->first();

        if ($cas === null) {
            // Bukan cas kita — mungkin dari integrasi lain. Abaikan dengan sopan.
            return ['status' => 'ignored', 'mesej' => 'Purchase tidak dikenali'];
        }

        return match (true) {
            str_contains($eventType, 'purchase.paid') => $this->kendaliDibayar($cas, $data),
            str_contains($eventType, 'purchase.failed') => $this->kendaliGagal($cas, $data, 'gagal'),
            str_contains($eventType, 'purchase.expired') => $this->kendaliGagal($cas, $data, 'tamat tempoh'),
            str_contains($eventType, 'purchase.cancelled') => $this->kendaliGagal($cas, $data, 'dibatalkan'),
            str_contains($eventType, 'purchase.refunded'),
            str_contains($eventType, 'purchase.refund') => $this->kendaliRefund($cas),
            default => ['status' => 'ignored', 'mesej' => 'Event tidak dikendalikan: ' . $eventType],
        };
    }

    private function kendaliDibayar(BillingCharge $cas, array $data): array
    {
        // ⚠️ PERATURAN 5: sahkan jumlah sepadan
        $dibayar = $this->jumlahDari($data);

        if ($dibayar !== null && abs($dibayar - (float) $cas->amount) > 0.01) {
            Log::error('Webhook CHIP: jumlah TIDAK SEPADAN', [
                'purchase' => $cas->chip_purchase_id,
                'dijangka' => (string) $cas->amount,
                'diterima' => $dibayar,
            ]);

            return ['status' => 'failed', 'mesej' => 'Jumlah tidak sepadan'];
        }

        $baru = $this->billing->aktifkanSelepasBayaran($cas, $data['paid_at'] ?? $data['created_at'] ?? null);

        return [
            'status' => 'processed',
            'mesej' => $baru ? 'Langganan diaktifkan' : 'Sudah aktif sebelum ini',
        ];
    }

    private function kendaliGagal(BillingCharge $cas, array $data, string $sebab): array
    {
        $this->billing->tandakanGagal(
            $cas,
            $data['failure_reason'] ?? $data['status'] ?? $sebab
        );

        return ['status' => 'processed', 'mesej' => 'Ditandakan: ' . $sebab];
    }

    private function kendaliRefund(BillingCharge $cas): array
    {
        $this->billing->tandakanRefund($cas);

        return ['status' => 'processed', 'mesej' => 'Refund direkod, komisen belum matang dibalikkan'];
    }

    /**
     * Cuba baca jumlah daripada pelbagai bentuk payload CHIP.
     */
    private function jumlahDari(array $data): ?float
    {
        foreach ([
            'purchase.total',
            'payment.amount',
            'amount',
            'total',
            'purchase.payment.total',
        ] as $laluan) {
            $nilai = data_get($data, $laluan);

            if (is_numeric($nilai)) {
                return round((float) $nilai, 2);
            }
        }

        return null;
    }

    /**
     * Buang data sensitif sebelum simpan payload.
     */
    private function sunting(array $data): array
    {
        $sensitif = [
            'client.email', 'client.phone', 'client.full_name',
            'client.bank_account', 'client.bank_account_number',
            'card', 'card_token', 'payment.card', 'payment.card_token',
            'authorization', 'signature',
        ];

        foreach ($sensitif as $laluan) {
            if (data_get($data, $laluan) !== null) {
                data_set($data, $laluan, '[disunting]');
            }
        }

        return $data;
    }
}
