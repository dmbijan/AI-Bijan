<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log kelulusan diskaun (BAB 88 / T-83) — dipindah daripada B-POS
 * `DiscountApprovalLog`.
 *
 * Juruwang minta kelulusan bila diskaun melebihi had peranan
 * (`pos.discount.requires_approval_above`); pengurus luluskan/tolak.
 * Kesan disimpan pada jualan: `discount_approved_by` + `discount_approved_at`.
 */
class POSDiscountApproval extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'pos_discount_approvals';

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'requested_by',
        'approved_by',
        'discount_percent',
        'discount_amount',
        'status',
        'reason',
        'decided_at',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'decided_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'discount_percent' => 0,
        'discount_amount' => 0,
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }
}
