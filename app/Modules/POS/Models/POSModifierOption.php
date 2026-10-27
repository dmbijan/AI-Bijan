<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pilihan dalam satu kumpulan modifier (BAB 88 / T-77).
 *
 * `price` ialah TAMBAHAN harga (0 = percuma), bukan harga penuh.
 */
class POSModifierOption extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_modifier_options';

    protected $fillable = [
        'tenant_id',
        'modifier_group_id',
        'name',
        'price',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'price' => 0,
        'sort_order' => 0,
        'is_active' => true,
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(POSModifierGroup::class, 'modifier_group_id');
    }
}
