<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rujukan — SATU tenant hanya boleh dimiliki SATU reseller.
 *
 * `tenant_id` di sini ialah RUJUKAN (bukan data tenant itu sendiri). Model ini
 * tidak guna `BelongsToTenant` kerana ia perlu dibaca oleh pekerjaan platform
 * (cron kematangan komisen) yang berjalan TANPA TenantContext.
 *
 * ⚠️ Semua query MESTI menapis eksplisit:
 *   ResellerReferral::where('tenant_id', $id)   ← bukan sekadar find()
 */
class ResellerReferral extends Model
{
    protected $table = 'reseller_referrals';

    protected $fillable = [
        'reseller_id',
        'tenant_id',
        'source',
        'reseller_click_id',
        'attributed_at',
    ];

    protected $casts = [
        'attributed_at' => 'datetime',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }
}
