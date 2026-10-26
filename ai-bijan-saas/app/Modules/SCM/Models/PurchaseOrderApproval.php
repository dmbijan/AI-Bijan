<?php

namespace App\Modules\SCM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderApproval extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_approvals';

    protected $fillable = [
        'purchase_order_id',
        'approver_id',
        'status',
        'comments',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
