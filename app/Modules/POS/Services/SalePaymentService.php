<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleTender;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pembayaran jualan POS (BAB 88 / T-78 + T-79 + T-81 + T-83).
 *
 * Menyatukan empat ciri B-POS dalam SATU transaksi atomik:
 *
 *   1. Caj perkhidmatan (T-78) dikira atas subtotal SELEPAS diskaun,
 *   2. Tender berbilang (T-79) disahkan = jumlah jualan,
 *   3. Kelulusan diskaun (T-83) diperiksa sebelum wang diambil,
 *   4. Idempotency luar talian (T-81) — `client_uuid` sama tidak mencipta
 *      jualan kedua.
 */
class SalePaymentService
{
    public function __construct(
        private TenderService $tender,
        private ServiceChargeService $serviceCharge,
        private DiscountApprovalService $discount,
        private KitchenService $kitchen,
        private SaleStockService $stok,
    ) {
    }

    /**
     * Kira semula jumlah jualan (subtotal − diskaun + caj perkhidmatan + cukai).
     */
    public function kiraSemula(POSSale $sale): POSSale
    {
        $subtotal = (float) $sale->subtotal;
        $diskaun = min((float) $sale->discount_amount, $subtotal);
        $asas = max(0, $subtotal - $diskaun);

        $caj = $this->serviceCharge->kira($asas, $sale->order_type);

        $sale->forceFill([
            'service_charge_amount' => $caj['amount'],
            'service_charge_rate' => $caj['rate'],
            'total_amount' => round($asas + $caj['amount'] + (float) $sale->tax_amount, 2),
        ])->save();

        return $sale;
    }

    /**
     * Bayar & selesaikan jualan draf.
     *
     * @param  array<int, array<string, mixed>>  $tenders
     */
    public function bayar(POSSale $sale, array $tenders, ?string $clientUuid = null): POSSale
    {
        if ($sale->status !== 'draft') {
            throw ValidationException::withMessages([
                'sale_id' => 'Hanya jualan draf boleh dibayar.',
            ]);
        }

        // Idempotency luar talian (T-81)
        if ($clientUuid !== null && $clientUuid !== '') {
            $sedia = POSSale::forTenant($sale->tenant_id)
                ->where('client_uuid', $clientUuid)
                ->where('status', 'completed')
                ->first();

            if ($sedia) {
                return $sedia;
            }
        }

        if ($sale->items()->count() === 0) {
            throw ValidationException::withMessages([
                'sale_id' => 'Jualan tanpa item tidak boleh dibayar.',
            ]);
        }

        // T-83 — gerbang kelulusan diskaun
        $this->discount->pastikanDibenarkan($sale);

        // T-78 — kira caj perkhidmatan atas asas selepas diskaun
        $this->kiraSemula($sale);

        // T-79 — sahkan tender = jumlah jualan
        $normal = $this->tender->normalize($tenders, (float) $sale->total_amount);

        return DB::transaction(function () use ($sale, $normal, $clientUuid) {
            $sale = POSSale::whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->status !== 'draft') {
                throw ValidationException::withMessages([
                    'sale_id' => 'Jualan ini sudah diproses.',
                ]);
            }

            foreach ($normal as $t) {
                POSSaleTender::create($t + [
                    'tenant_id' => $sale->tenant_id,
                    'sale_id' => $sale->id,
                ]);
            }

            $status = $this->tender->statusGabungan($normal);
            $kaedah = count($normal) > 1
                ? 'split'
                : $normal[0]['method'];

            $diterima = array_sum(array_column($normal, 'cash_received'));
            $baki = array_sum(array_column($normal, 'change_amount'));

            $sale->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'sale_date' => now()->format('Y-m-d'),
                'payment_method' => $kaedah,
                'payment_status' => $status,
                'amount_paid' => round(array_sum(array_column($normal, 'amount')), 2),
                // Tunai yang diterima (termasuk baki) untuk paparan resit
                'change_amount' => round((float) $baki, 2),
                'client_uuid' => $clientUuid ?: $sale->client_uuid,
                'sync_fingerprint' => $clientUuid ? hash('sha256', $sale->sale_no . '|' . $clientUuid) : null,
            ])->save();

            if ($status === 'paid') {
                $this->kitchen->enqueue($sale);
            }

            // POS → IMS: potong stok (stok tak cukup ditanda untuk semakan, jualan tak disekat)
            $this->stok->potongStok($sale);

            return $sale->fresh(['tenders', 'items']);
        });
    }
}
