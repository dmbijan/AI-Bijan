<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSTableOrder extends Model
{
    use BelongsToTenant, HasUlids;

    public const STATUS = ['baru', 'dimasak', 'siap', 'dihantar', 'selesai', 'batal'];

    public const TERBUKA = ['baru', 'dimasak', 'siap', 'dihantar'];

    protected $table = 'pos_table_orders';

    protected $fillable = ['tenant_id', 'table_id', 'public_id', 'customer_name', 'notes', 'status', 'total_amount', 'sale_id'];

    protected $casts = ['total_amount' => 'decimal:2'];

    /** ULID hanya untuk public_id; kunci utama kekal id nombor. */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(POSTable::class, 'table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(POSTableOrderItem::class, 'order_id');
    }
}
