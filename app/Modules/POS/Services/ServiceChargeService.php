<?php

namespace App\Modules\POS\Services;

/**
 * Caj perkhidmatan (BAB 88 / T-78) — dipindah daripada B-POS
 * `ServiceChargeService`.
 *
 * Amalan Malaysia: restoran mengenakan 10% caj perkhidmatan pada pesanan
 * MAKAN DI TEMPAT sahaja, dan ia mesti dipaparkan BERASINGAN daripada cukai
 * dalam resit (bukan digabung ke dalam `tax_amount`).
 *
 * Kadar & skop datang daripada `config/pos.php` (boleh ditimpa per tenant
 * melalui tetapan tenant).
 */
class ServiceChargeService
{
    public const SKOP_DINE_IN = 'dine_in';

    public const SKOP_SEMUA = 'all';

    /** Kadar caj perkhidmatan untuk tenant (0 = tiada caj). */
    public function kadar(): float
    {
        $kadar = (float) config('pos.service_charge.rate', 0);

        return max(0, min(100, $kadar));
    }

    /** Skop: `dine_in` (lalai) atau `all`. */
    public function skop(): string
    {
        $skop = (string) config('pos.service_charge.scope', self::SKOP_DINE_IN);

        return in_array($skop, [self::SKOP_DINE_IN, self::SKOP_SEMUA], true)
            ? $skop
            : self::SKOP_DINE_IN;
    }

    /** Adakah caj dikenakan untuk jenis pesanan ini? */
    public function terpakai(?string $jenisPesanan): bool
    {
        if ($this->kadar() <= 0) {
            return false;
        }

        return $this->skop() === self::SKOP_SEMUA || $jenisPesanan === 'dine_in';
    }

    /**
     * Kira caj perkhidmatan atas asas SELEPAS diskaun.
     *
     * @return array{amount: float, rate: float|null}
     */
    public function kira(float $asas, ?string $jenisPesanan): array
    {
        if ($asas <= 0 || ! $this->terpakai($jenisPesanan)) {
            return ['amount' => 0.0, 'rate' => null];
        }

        $kadar = $this->kadar();

        return [
            'amount' => round($asas * $kadar / 100, 2),
            'rate' => $kadar,
        ];
    }
}
