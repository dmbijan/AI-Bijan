<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kumpulan modifier (BAB 88 / T-77) — dipindah daripada B-POS `ModifierGroup`.
 *
 * Contoh: "Saiz" (min 1, max 1), "Tambahan" (min 0, max 3).
 * `min_select > 0` bermaksud pilihan WAJIB sebelum baris boleh ditambah.
 */
class POSModifierGroup extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_modifier_groups';

    protected $fillable = [
        'tenant_id',
        'name',
        'min_select',
        'max_select',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_select' => 'integer',
        'max_select' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'min_select' => 0,
        'max_select' => 1,
        'sort_order' => 0,
        'is_active' => true,
    ];

    public function options(): HasMany
    {
        return $this->hasMany(POSModifierOption::class, 'modifier_group_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function wajib(): bool
    {
        return $this->min_select > 0;
    }
}
