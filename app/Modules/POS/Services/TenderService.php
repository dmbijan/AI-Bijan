<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSSaleTender;
use Illuminate\Validation\ValidationException;

/**
 * Tender pembayaran berbilang (BAB 88 / T-79).
 *
 * Dipindah daripada B-POS `TransactionTenderService` — corak yang lebih ketat
 * daripada B-POS asal, kerana ini melibatkan wang pelanggan:
 *
 *   • jumlah tender MESTI sama tepat dengan jumlah jualan (tiada lebih/kurang),
 *   • tunai: `cash_received` ≥ `amount`; baki = `change_amount`,
 *   • maksimum 2 tender (sama seperti B-POS),
 *   • maksimum 1 tender bukan tunai (kad/e-wallet tidak boleh dipecah dua kali).
 *
 * Semua amaun dalam unit mata wang yang SAMA seperti `pos_sales.total_amount`.
 */
class TenderService
{
    public const MAX_TENDER = 2;

    public const KAEDAH_SAH = [
        POSSaleTender::METHOD_CASH,
        POSSaleTender::METHOD_CARD,
        POSSaleTender::METHOD_EWALLET,
        POSSaleTender::METHOD_TNG,
        POSSaleTender::METHOD_CREDIT,
        POSSaleTender::METHOD_VOUCHER,
        POSSaleTender::METHOD_DUITNOW,
        POSSaleTender::METHOD_ONLINE,
    ];

    /** Status lalai: tunai/kad/e-wallet/voucher = paid; kredit & TnG = pending. */
    private const STATUS_LALAI = [
        POSSaleTender::METHOD_CASH => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_CARD => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_EWALLET => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_CREDIT => POSSaleTender::STATUS_PENDING,
        POSSaleTender::METHOD_VOUCHER => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_DUITNOW => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_ONLINE => POSSaleTender::STATUS_PAID,
        POSSaleTender::METHOD_TNG => POSSaleTender::STATUS_PENDING,
    ];

    /**
     * Sahkan dan normalkan senarai tender.
     *
     * @param  array<int, array<string, mixed>>  $input
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $input, float $jumlahJualan, bool $benarkanKosong = false): array
    {
        if ($input === []) {
            if ($benarkanKosong) {
                return [];
            }

            throw ValidationException::withMessages(['tenders' => 'Kaedah bayaran diperlukan.']);
        }

        if (count($input) > self::MAX_TENDER) {
            throw ValidationException::withMessages([
                'tenders' => 'Maksimum ' . self::MAX_TENDER . ' kaedah bayaran bagi satu jualan.',
            ]);
        }

        $normal = [];
        foreach (array_values($input) as $i => $tender) {
            $normal[] = $this->normalSatu($tender, $i);
        }

        $bukanTunai = count(array_filter(
            $normal,
            fn (array $tender) => $tender['method'] !== POSSaleTender::METHOD_CASH
        ));

        if ($bukanTunai > 1) {
            throw ValidationException::withMessages([
                'tenders' => 'Hanya satu bayaran bukan tunai dibenarkan (selain tunai).',
            ]);
        }

        $jumlahTender = round(array_sum(array_column($normal, 'amount')), 2);
        $jumlahJualan = round($jumlahJualan, 2);

        if (abs($jumlahTender - $jumlahJualan) > 0.005) {
            throw ValidationException::withMessages([
                'tenders' => sprintf(
                    'Jumlah tender (%s) mesti sama dengan jumlah jualan (%s).',
                    number_format($jumlahTender, 2, '.', ''),
                    number_format($jumlahJualan, 2, '.', '')
                ),
            ]);
        }

        return $normal;
    }

    /**
     * Normalkan satu tender tanpa mengubah keadaan pengesahan senarai.
     *
     * @param  array<string, mixed>  $tender
     * @return array<string, mixed>
     */
    private function normalSatu(array $tender, int $i): array
    {
        $kaedah = strtolower(trim((string) ($tender['method'] ?? '')));

        if (! in_array($kaedah, self::KAEDAH_SAH, true)) {
            throw ValidationException::withMessages([
                "tenders.{$i}.method" => 'Kaedah bayaran tidak sah.',
            ]);
        }

        $amaun = round((float) ($tender['amount'] ?? 0), 2);

        if ($amaun <= 0) {
            throw ValidationException::withMessages([
                "tenders.{$i}.amount" => 'Amaun mesti melebihi sifar.',
            ]);
        }

        $status = self::STATUS_LALAI[$kaedah];
        $diterima = null;
        $baki = 0.0;

        if ($kaedah === POSSaleTender::METHOD_CASH) {
            $diterima = round((float) ($tender['cash_received'] ?? $amaun), 2);

            if ($diterima + 0.0001 < $amaun) {
                throw ValidationException::withMessages([
                    "tenders.{$i}.cash_received" => 'Wang diterima kurang daripada amaun tender.',
                ]);
            }

            $baki = round($diterima - $amaun, 2);
        } else {
            if (! empty($tender['cash_received'])) {
                throw ValidationException::withMessages([
                    "tenders.{$i}.cash_received" => 'Wang diterima hanya untuk bayaran tunai.',
                ]);
            }
        }

        return [
            'method' => $kaedah,
            'amount' => $amaun,
            'cash_received' => $diterima,
            'change_amount' => $baki,
            'payment_status' => $status,
            'reference' => $tender['reference'] ?? null,
            'paid_at' => $status === POSSaleTender::STATUS_PAID ? now() : null,
        ];
    }

    /**
     * Status bayaran jualan daripada tender: failed > pending > paid > unpaid.
     *
     * @param  iterable<POSSaleTender|array<string, mixed>>  $tenders
     */
    public function statusGabungan(iterable $tenders): string
    {
        $result = 'unpaid';

        foreach ($tenders as $t) {
            $status = is_array($t) ? ($t['payment_status'] ?? null) : $t->payment_status;

            if ($status === POSSaleTender::STATUS_FAILED) {
                $result = 'failed';
            } elseif ($status === POSSaleTender::STATUS_PENDING && $result !== 'failed') {
                $result = 'pending';
            } elseif ($result === 'unpaid') {
                $result = 'paid';
            }
        }

        return $result;
    }

    /** Label kaedah untuk resit (dari config/pos.php). */
    public function label(string $kaedah): string
    {
        return (string) config('pos.payment_methods.' . $kaedah, $kaedah);
    }
}
