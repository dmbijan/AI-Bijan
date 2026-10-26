<?php

namespace App\Modules\ProofOfValue\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Definisi metrik hasil untuk satu modul.
 *
 * Contoh: modul `inventory`, key `stockout_reduction`,
 * name "Pengurangan Kehabisan Stok", unit percent, direction down.
 */
class PovMetric extends Model
{
    use BelongsToTenant;

    protected $table = 'pov_metrics';

    protected $fillable = [
        'tenant_id',
        'module',
        'key',
        'name',
        'description',
        'unit',
        'direction',
        'target_value',
        'is_active',
        'is_verified',
        'verification_note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'target_value' => 'decimal:2',
    ];

    protected $attributes = [
        'unit' => 'percent',
        'direction' => 'up',
        'is_active' => true,
        'is_verified' => false,
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(PovReading::class, 'metric_id');
    }

    public function baselines(): HasMany
    {
        return $this->hasMany(PovBaseline::class, 'metric_id');
    }

    /**
     * Bacaan terkini.
     */
    public function bacaanTerkini(): ?PovReading
    {
        return $this->readings()->orderByDesc('reading_date')->first();
    }

    /**
     * Baseline terkini.
     */
    public function baselineTerkini(): ?PovBaseline
    {
        return $this->baselines()->orderByDesc('baseline_date')->first();
    }

    /**
     * Peratus perubahan sejak baseline.
     */
    public function perubahan(): ?float
    {
        $baseline = $this->baselineTerkini();
        $terkini = $this->bacaanTerkini();

        if (! $baseline || ! $terkini || (float) $baseline->baseline_value == 0.0) {
            return null;
        }

        $baselineValue = (float) $baseline->baseline_value;
        $currentValue = (float) $terkini->value;

        $peratus = (($currentValue - $baselineValue) / abs($baselineValue)) * 100;

        // Untuk metrik "lebih rendah lebih baik", tanda jadi terbalik
        if ($this->direction === 'down') {
            $peratus = abs($peratus);
        }

        return round($peratus, 2);
    }

    /**
     * Adakah sasaran sudah tercapai?
     */
    public function sasaranTercapai(): bool
    {
        if ($this->target_value === null) {
            return false;
        }

        $terkini = $this->bacaanTerkini();

        if (! $terkini) {
            return false;
        }

        $target = (float) $this->target_value;
        $value = (float) $terkini->value;

        return $this->direction === 'up' ? $value >= $target : $value <= $target;
    }
}
