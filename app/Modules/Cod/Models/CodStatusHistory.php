<?php

namespace App\Modules\Cod\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sejarah perubahan status COD — jejak audit penuh.
 */
class CodStatusHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'cod_status_histories';

    protected $fillable = [
        'tenant_id',
        'cod_order_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    protected $attributes = [
        'changed_at' => null,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CodOrder::class, 'cod_order_id');
    }
}
