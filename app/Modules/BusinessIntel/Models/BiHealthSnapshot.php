<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BiHealthSnapshot extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_health_snapshots';

    protected $fillable = [
        'tenant_id',
        'snapshot_date',
        'overall_score',
        'grade',
        'sales_score',
        'cashflow_score',
        'operations_score',
        'customer_score',
        'marketing_score',
        'compliance_score',
        'metrics',
        'insights',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'metrics' => 'array',
        'insights' => 'array',
        'overall_score' => 'float',
        'sales_score' => 'float',
        'cashflow_score' => 'float',
        'operations_score' => 'float',
        'customer_score' => 'float',
        'marketing_score' => 'float',
        'compliance_score' => 'float',
    ];

    protected $attributes = [
        'overall_score' => 0,
        'grade' => 'C',
        'sales_score' => 0,
        'cashflow_score' => 0,
        'operations_score' => 0,
        'customer_score' => 0,
        'marketing_score' => 0,
        'compliance_score' => 0,
    ];

    /**
     * Gred daripada skor.
     */
    public static function gredDaripada(float $skor): string
    {
        return match (true) {
            $skor >= 85 => 'A',
            $skor >= 70 => 'B',
            $skor >= 55 => 'C',
            $skor >= 40 => 'D',
            default => 'E',
        };
    }

    /**
     * Dimensi yang paling lemah (untuk cadangan).
     */
    public function dimensiTerlemah(): array
    {
        $dimensi = [
            'sales' => (float) $this->sales_score,
            'cashflow' => (float) $this->cashflow_score,
            'operations' => (float) $this->operations_score,
            'customer' => (float) $this->customer_score,
            'marketing' => (float) $this->marketing_score,
            'compliance' => (float) $this->compliance_score,
        ];

        asort($dimensi);

        return array_slice($dimensi, 0, 2, true);
    }
}
