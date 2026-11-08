<?php

namespace App\Modules\WorkManagement\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris bajet projek — perbandingan perbelanjaan rancangan vs sebenar.
 */
class ProjectBudgetLine extends Model
{
    use BelongsToTenant;

    protected $table = 'project_budget_lines';

    public const CATEGORY_LABOUR = 'labour';

    public const CATEGORY_MATERIAL = 'material';

    public const CATEGORY_SUBCONTRACT = 'subcontract';

    public const CATEGORY_SOFTWARE = 'software';

    public const CATEGORY_TRAVEL = 'travel';

    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'category',
        'unit',
        'quantity',
        'unit_cost',
        'planned_cost',
        'actual_cost',
        'spent_to_date',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'planned_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'spent_to_date' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'category' => self::CATEGORY_OTHER,
        'unit' => 'unit',
        'quantity' => 1,
        'unit_cost' => 0,
        'planned_cost' => 0,
        'actual_cost' => 0,
        'spent_to_date' => 0,
        'sort_order' => 0,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Variance kos (negatif = lebih menjimatkan).
     */
    public function variance(): float
    {
        return round((float) $this->actual_cost - (float) $this->planned_cost, 2);
    }

    /**
     * Peratus perbelanjaan berbanding rancangan.
     */
    public function peratusDigunakan(): float
    {
        $rancangan = (float) $this->planned_cost;

        if ($rancangan <= 0) {
            return 0.0;
        }

        return round(((float) $this->spent_to_date / $rancangan) * 100, 2);
    }

    /**
     * @return array<string, string>
     */
    public static function kategori(): array
    {
        return [
            self::CATEGORY_LABOUR => 'Tenaga Kerja',
            self::CATEGORY_MATERIAL => 'Bahan',
            self::CATEGORY_SUBCONTRACT => 'Subkontrak',
            self::CATEGORY_SOFTWARE => 'Perisian',
            self::CATEGORY_TRAVEL => 'Perjalanan',
            self::CATEGORY_OTHER => 'Lain-lain',
        ];
    }
}
