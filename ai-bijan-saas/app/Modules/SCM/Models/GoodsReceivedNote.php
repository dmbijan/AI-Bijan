<?php

namespace App\Modules\SCM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedNote extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'goods_received_notes';

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'grn_number',
        'received_date',
        'notes',
        'condition',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines()
    {
        return $this->hasMany(GoodsReceivedNoteLine::class);
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }

    public static function generateGRNNumber(): string
    {
        $year = date('Y');
        $prefix = "GRN-{$year}-";
        
        $lastGRN = self::where('grn_number', 'like', "{$prefix}%")
            ->orderBy('grn_number', 'desc')
            ->first();
        
        if ($lastGRN) {
            $lastNumber = (int) substr($lastGRN->grn_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "{$prefix}{$newNumber}";
    }
}
