<?php

namespace App\Modules\Entitlement\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kaunter penggunaan bagi satu had (tenant scoped).
 *
 * `used_value` — usage yang sudah diset.
 * `reserved_value` — usage yang dikuatkuasakan tetapi belum selesai.
 *
 * Untuk AI tokens: reserve dahulu (atomic), kemudian settle. Ini
 * langkah pencegahan credit overspend.
 *
 * ⚠️ MODEL INI SENGAJA TIDAK GUNA `BelongsToTenant` (disahkan 27 Okt 2026).
 *
 * Sebab: `EntitlementService` membaca/menulis kaunter semasa **provisioning**,
 * CLI dan seed — ketika `TenantContext` KOSONG. Global scope akan menapis
 * `tenant_id = 0` dan mencipta rekod dengan tenant_id salah (bug yang pernah
 * berlaku dalam `EntitlementGrant`, lihat memori projek).
 *
 * **Ganti jaminan skop:** setiap query MESTI menulis `where('tenant_id', $id)`
 * secara eksplisit. Semua pemanggil sedia ada sudah berbuat demikian:
 *   - `EntitlementService::kaunter()`  — firstOrCreate dengan tenant_id
 *   - `EntitlementService::reserve()`  — where('tenant_id', …)->lockForUpdate()
 *
 * Jika menambah query baharu pada model ini, **salin** corak itu.
 * Jika mahu trait, pindahkan logik provisioning ke `withoutGlobalScope` dahulu.
 */
class UsageCounter extends Model
{
    protected $table = 'usage_counters';

    protected $fillable = [
        'tenant_id',
        'limit_key',
        'period_key',
        'used_value',
        'reserved_value',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'used_value' => 'integer',
        'reserved_value' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    /**
     * Jumlah terpakai termasuk reservation.
     */
    public function jumlahDigunakan(): int
    {
        return (int) $this->used_value + (int) $this->reserved_value;
    }
}
