<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pakej perkhidmatan — templat harga + durasi + deposit.
 */
class ServicePackage extends Model
{
    use BelongsToTenant;

    protected $table = 'service_packages';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'description',
        'base_price',
        'currency',
        'duration_minutes',
        'buffer_minutes',
        'inclusions',
        'exclusions',
        'requires_deposit',
        'deposit_percent',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'deposit_percent' => 'decimal:2',
        'duration_minutes' => 'integer',
        'buffer_minutes' => 'integer',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'requires_deposit' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(ServiceProject::class, 'package_id');
    }

    /** Jumlah deposit yang perlu dibayar untuk pakej ini. */
    public function kiraDeposit(): float
    {
        if (! $this->requires_deposit) {
            return 0.0;
        }

        return round((float) $this->base_price * ((float) $this->deposit_percent / 100), 2);
    }

    /** Durasi penuh termasuk buffer setup/tear-down. */
    public function jumlahDurasi(): int
    {
        return (int) ($this->duration_minutes ?? 0) + (int) ($this->buffer_minutes ?? 0);
    }
}
