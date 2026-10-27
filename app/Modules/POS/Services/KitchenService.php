<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSSale;
use Illuminate\Validation\ValidationException;

/**
 * Paparan Dapur / Kitchen Display (BAB 88 / T-76).
 *
 * Dipindah daripada B-POS `KitchenService`. Status dapur hidup pada BARIS
 * JUALAN (bukan jadual berasingan), supaya:
 *   • dapur dan resit tidak boleh "terpisah" (drift),
 *   • laporan boleh kira masa siap dari rekod jualan.
 *
 * Aliran: new → preparing → ready → served.
 * `pos_sales.kitchen_status = null` bermaksud outlet tidak guna paparan dapur.
 */
class KitchenService
{
    public const STATUS_NEW = 'new';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_SERVED = 'served';

    public const ALIRAN = [
        self::STATUS_NEW,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_SERVED,
    ];

    private const CAP_MASA = [
        self::STATUS_NEW => 'kitchen_queued_at',
        self::STATUS_PREPARING => 'kitchen_started_at',
        self::STATUS_READY => 'kitchen_ready_at',
        self::STATUS_SERVED => 'kitchen_served_at',
    ];

    public function diaktifkan(): bool
    {
        return (bool) config('pos.kitchen_display.enabled', false);
    }

    /** Masukkan jualan baharu ke paparan dapur (jika outlet menggunakannya). */
    public function enqueue(POSSale $sale): void
    {
        if (! $this->diaktifkan() || $sale->kitchen_status !== null) {
            return;
        }

        $sale->forceFill([
            'kitchen_status' => self::STATUS_NEW,
            'kitchen_queued_at' => now(),
        ])->save();
    }

    /**
     * Alih tiket ke status baharu. Bergerak MUNDUR akan mengosongkan cap masa
     * yang lebih lewat (cth ready → preparing membuang `kitchen_ready_at`).
     */
    public function moveTo(POSSale $sale, string $status): POSSale
    {
        if (! in_array($status, self::ALIRAN, true) || $sale->kitchen_status === null) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan ini tidak berada dalam paparan dapur.',
            ]);
        }

        $sasaran = array_search($status, self::ALIRAN, true);
        $kemasKini = ['kitchen_status' => $status];

        foreach (self::ALIRAN as $i => $peringkat) {
            $kolum = self::CAP_MASA[$peringkat];

            if ($i > $sasaran) {
                $kemasKini[$kolum] = null;
            } elseif ($i === $sasaran && $peringkat !== self::STATUS_NEW) {
                $kemasKini[$kolum] = now();
            }
        }

        $sale->forceFill($kemasKini)->save();

        return $sale;
    }

    /** Status seterusnya dalam aliran (null jika sudah `served`). */
    public function next(string $status): ?string
    {
        $i = array_search($status, self::ALIRAN, true);

        return $i === false ? null : (self::ALIRAN[$i + 1] ?? null);
    }

    /**
     * Senarai tiket aktif untuk paparan dapur, disusun paling lama dahulu.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, POSSale>
     */
    public function tiketAktif(int $tenantId)
    {
        return POSSale::forTenant($tenantId)
            ->whereNotNull('kitchen_status')
            ->whereIn('kitchen_status', [self::STATUS_NEW, self::STATUS_PREPARING, self::STATUS_READY])
            ->with(['items', 'dineTable'])
            ->orderBy('kitchen_queued_at')
            ->get();
    }
}
