<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reseller — orang yang menjual langganan AI BIJAN.
 *
 * ⚠️ JADUAL PLATFORM, BUKAN TENANT. Tiada trait `BelongsToTenant` dan tiada
 * `tenant_id`. Jangan gabung dengan modul `Affiliate` (yang tenant-scoped
 * untuk pelanggan menjual produk mereka sendiri).
 *
 * Model komisen (disahkan pengguna 27 Okt 2026):
 *   Harga RM388/bulan · komisen RM100/bulan · selagi pelanggan langgan.
 *   Komisen jadi hak selepas 30 hari (lindungi refund/chargeback).
 */
class Reseller extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_TERMINATED = 'terminated';

    /** Komisen tidak boleh melebihi 50% daripada bayaran sebenar. */
    public const HAD_KOMISEN_PCT = 50.00;

    /** Tempoh matang — lindungi daripada refund/chargeback. */
    public const HARI_MATANG = 30;

    /** Minimum baki untuk payout automatik. */
    public const MIN_PAYOUT = 100.00;

    protected $table = 'resellers';

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'email',
        'phone',
        'status',
        'commission_value',
        'commission_months',
        'commission_cap_pct',
        'bank_details',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'commission_months' => 'integer',
        'commission_cap_pct' => 'decimal:2',
        'bank_details' => 'array',
        'approved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'commission_value' => 100,
        'commission_cap_pct' => 50,
    ];

    public function aktif(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(ResellerReferral::class, 'reseller_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ResellerCommission::class, 'reseller_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(ResellerPayout::class, 'reseller_id');
    }

    /**
     * Kira komisen untuk satu bayaran.
     *
     * Peraturan:
     *   1. Asas = jumlah SEBENAR dibayar (bukan harga senarai — diskaun turunkan)
     *   2. Had = `commission_cap_pct` × bayaran (lalai 50%)
     *   3. Nilai komisen = `commission_value` (lalai RM100)
     *
     * @return string nilai komisen (2 titik perpuluhan)
     */
    public function kiraKomisen(float $bayaranSebenar): string
    {
        $had = $bayaranSebenar * ((float) $this->commission_cap_pct / 100);
        $nilai = min((float) $this->commission_value, $had);

        // Tidak boleh negatif atau melebihi bayaran
        $nilai = max(0.0, min($nilai, $bayaranSebenar));

        return number_format($nilai, 2, '.', '');
    }
}
