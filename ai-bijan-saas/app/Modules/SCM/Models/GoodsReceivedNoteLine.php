<?php

namespace App\Modules\SCM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedNoteLine extends Model
{
    use HasFactory;

    protected $table = 'goods_received_note_lines';

    protected $fillable = [
        'goods_received_note_id',
        'purchase_order_line_id',
        'item_code',
        'item_name',
        'ordered_quantity',
        'received_quantity',
        'rejected_quantity',
        'uom',
    ];

    protected $casts = [
        'ordered_quantity' => 'integer',
        'received_quantity' => 'decimal:2',
        'rejected_quantity' => 'integer',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function poLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }
}
