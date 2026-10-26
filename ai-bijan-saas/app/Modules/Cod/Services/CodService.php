<?php

namespace App\Modules\Cod\Services;

use App\Modules\Cod\Models\CodOrder;
use App\Modules\Cod\Models\CodSetting;
use Illuminate\Support\Facades\Log;

/**
 * CodService — enjin Cash on Delivery.
 *
 * ✅ IDEA #6 (BAB 84): COD ialah kaedah pembayaran dominan di Malaysia.
 * Tiada pesaing MY yang menyenangkannya — ini jurangunting Herald.
 *
 * Aliran status:
 * pending → confirmed → dispatched → delivered → collected
 *                    ↘ failed / cancelled
 */
class CodService
{
    /**
     * Kunci status yang sah — digunakan untuk validate perpindahan.
     */
    public const ALIRAN = [
        CodOrder::STATUS_PENDING => [CodOrder::STATUS_CONFIRMED, CodOrder::STATUS_CANCELLED],
        CodOrder::STATUS_CONFIRMED => [CodOrder::STATUS_DISPATCHED, CodOrder::STATUS_CANCELLED, CodOrder::STATUS_FAILED],
        CodOrder::STATUS_DISPATCHED => [CodOrder::STATUS_DELIVERED, CodOrder::STATUS_FAILED, CodOrder::STATUS_CANCELLED],
        CodOrder::STATUS_DELIVERED => [CodOrder::STATUS_COLLECTED, CodOrder::STATUS_FAILED, CodOrder::STATUS_REFUNDED],
        CodOrder::STATUS_COLLECTED => [CodOrder::STATUS_REFUNDED],
        CodOrder::STATUS_FAILED => [],
        CodOrder::STATUS_CANCELLED => [],
        CodOrder::STATUS_REFUNDED => [],
    ];

    /**
     * Semak sama ada COD boleh digunakan untuk pesanan tertentu.
     *
     * @return array{allowed: bool, reasons: array, surcharge: float, needs_confirmation: bool, needs_call: bool}
     */
    public function semakKelayakan(CodSetting $setting, float $orderAmount, ?string $state = null, ?string $courier = null): array
    {
        $reasons = [];

        if (! $setting->enabled) {
            $reasons[] = 'COD tidak diaktifkan untuk tenant ini';
        }

        if ($orderAmount < (float) $setting->min_order_amount) {
            $reasons[] = sprintf(
                'Minimum pesanan COD ialah RM%s, pesanan ini RM%s',
                number_format((float) $setting->min_order_amount, 2),
                number_format($orderAmount, 2)
            );
        }

        if ($orderAmount > (float) $setting->max_order_amount) {
            $reasons[] = sprintf(
                'Maksimum COD ialah RM%s, pesanan ini RM%s',
                number_format((float) $setting->max_order_amount, 2),
                number_format($orderAmount, 2)
            );
        }

        // exempted regions
        $excluded = $setting->excluded_regions ?? [];
        if ($state && in_array(strtolower($state), array_map('strtolower', $excluded), true)) {
            $reasons[] = "Kawasan {$state} tidak disokong untuk COD";
        }

        // courier whitelist
        $allowed = $setting->allowed_couriers ?? [];
        if ($courier && ! empty($allowed) && ! in_array($courier, $allowed, true)) {
            $reasons[] = "Kurier {$courier} tidak menyokong COD";
        }

        return [
            'allowed' => empty($reasons),
            'reasons' => $reasons,
            'surcharge' => (float) $setting->surcharge,
            'needs_confirmation' => (bool) $setting->require_confirmation,
            'needs_call' => (bool) $setting->require_call_confirmation,
        ];
    }

    /**
     * Semak alamat & liputan pengemparan.
     *
     * @return array{verified: bool, issue: ?string, serviceable: bool}
     */
    public function semakAlamat(string $address, ?string $postcode = null, ?string $state = null, ?string $phone = null): array
    {
        $address = trim($address);
        $issues = [];

        if (mb_strlen($address) < 15) {
            $issues[] = 'Alamat terlalu pendek';
        }

        // Alamat Malaysia biasa ada nombor jalan + nama jalan
        if (! preg_match('/\d+/', $address)) {
            $issues[] = 'Alamat tiada nombor jalan';
        }

        // Postcode MY = 5 digit
        if ($postcode !== null && $postcode !== '') {
            if (! preg_match('/^\d{5}$/', $postcode)) {
                $issues[] = 'Poskod Malaysia mesti 5 digit';
            }
        }

        // No telefon MY
        if ($phone !== null && $phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            if (! preg_match('/^(60)?1\d{8,9}$/', $digits)) {
                $issues[] = 'Nombor telefon bukan format MY yang sah';
            }
        }

        $verified = empty($issues);

        return [
            'verified' => $verified,
            'issue' => $verified ? null : implode('; ', $issues),
            // Serviceable — untuk sekarang sama dengan verified (boleh diintegrasikan
            // dengan API liputan kurier selepas itu).
            'serviceable' => $verified,
        ];
    }

    /**
     * Jana nombor COD unik.
     */
    public function janaCodNo(int $tenantId): string
    {
        $tahun = now()->format('Y');
        $count = CodOrder::where('tenant_id', $tenantId)->count() + 1;

        // Sentiasa probe ke atas sehingga nombor benar-benar belum digunakan
        // (count() hanya memberi jumlah rekod, bukan seq nombor terpakai).
        do {
            $no = sprintf('COD-%s-%05d', $tahun, $count);
            $ada = CodOrder::where('tenant_id', $tenantId)->where('cod_no', $no)->exists();
            $count++;
        } while ($ada);

        return $no;
    }

    /**
     * Pindah status dengan validasi aliran + jejak audit.
     */
    public function tukarStatus(CodOrder $order, string $status, ?string $note = null, ?int $userId = null): CodOrder
    {
        $dari = $order->status;

        if ($dari === $status) {
            return $order;
        }

        $diberbolehkan = self::ALIRAN[$dari] ?? [];

        if (! in_array($status, $diberbolehkan, true)) {
            Log::warning('COD: perpindahan status tidak sah', [
                'cod_no' => $order->cod_no,
                'from' => $dari,
                'to' => $status,
            ]);

            return $order;
        }

        $order->status = $status;

        // Set timestamp mengikut status
        match ($status) {
            CodOrder::STATUS_DISPATCHED => $order->dispatched_at = now(),
            CodOrder::STATUS_DELIVERED => $order->delivered_at = now(),
            CodOrder::STATUS_COLLECTED => $order->collected_at = now(),
            CodOrder::STATUS_FAILED => $order->failed_at = now(),
            default => null,
        };

        $order->save();

        // Jejak audit
        $order->histories()->create([
            'tenant_id' => $order->tenant_id,
            'from_status' => $dari,
            'to_status' => $status,
            'note' => $note,
            'changed_by' => $userId ?? auth()->id(),
            'changed_at' => now(),
        ]);

        return $order;
    }

    /**
     * Rekod kutipan wang.
     */
    public function rekodKutipan(CodOrder $order, float $amount, ?int $userId = null): CodOrder
    {
        $amount = max(0, round($amount, 2));

        $order->amount_collected = round((float) $order->amount_collected + $amount, 2);

        // Jika sudah quoting penuh, tukar ke collected
        if ($order->amount_collected >= (float) $order->amount_to_collect) {
            $this->tukarStatus(
                $order,
                CodOrder::STATUS_COLLECTED,
                'Kutipan penuh',
                $userId
            );
        } else {
            $order->save();
        }

        return $order;
    }

    /**
     * Analisis kegagalan COD — punca utama kehilangan revenue.
     *
     * @return array{total: int, failed: int, failure_rate: float, by_reason: array, by_courier: array}
     */
    public function analisisKegagalan(int $tenantId, int $hari = 30): array
    {
        $sejak = now()->subDays($hari);

        $failed = CodOrder::query()
            ->where('tenant_id', $tenantId)
            ->where('status', CodOrder::STATUS_FAILED)
            ->where('created_at', '>=', $sejak)
            ->get(['failure_reason', 'courier']);

        $total = CodOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [CodOrder::STATUS_FAILED, CodOrder::STATUS_COLLECTED, CodOrder::STATUS_REFUNDED])
            ->where('created_at', '>=', $sejak)
            ->count();

        $byReason = $failed
            ->filter(fn ($o) => ! empty($o->failure_reason))
            ->groupBy('failure_reason')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->toArray();

        $byCourier = $failed
            ->filter(fn ($o) => ! empty($o->courier))
            ->groupBy('courier')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->toArray();

        return [
            'total' => $total,
            'failed' => $failed->count(),
            'failure_rate' => $total > 0 ? round(($failed->count() / $total) * 100, 2) : 0.0,
            'by_reason' => $byReason,
            'by_courier' => $byCourier,
            'periode_hari' => $hari,
        ];
    }

    /**
     * Reconciliation harian — banding dijangka vs dikutip.
     */
    public function reconcilesi(int $tenantId, string $tarikh, ?string $courier = null): array
    {
        $query = CodOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', $tarikh);

        if ($courier) {
            $query->where('courier', $courier);
        }

        $orders = $query->get();

        $dijangka = (float) $orders->sum('amount_to_collect');
        $dikutipWang = (float) $orders->sum('amount_collected');
        $gagal = $orders->where('status', CodOrder::STATUS_FAILED)->count();
        $dikutipBilangan = $orders->where('status', CodOrder::STATUS_COLLECTED)->count();
        $refund = (float) $orders->sum('amount_refunded');

        return [
            'tarikh' => $tarikh,
            'courier' => $courier,
            'jumlah_orders' => $orders->count(),
            'dijangka' => round($dijangka, 2),
            'dikutip' => round($dikutipWang, 2),
            'jurang' => round($dijangka - $dikutipWang, 2),
            'kadar_kutipan' => $dijangka > 0 ? round(($dikutipWang / $dijangka) * 100, 2) : 0.0,
            'orders_dikutip' => $dikutipBilangan,
            'orders_gagal' => $gagal,
            'jumlah_refund' => round($refund, 2),
        ];
    }
}
