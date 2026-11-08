<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Invois cawangan — bil bulanan per cawangan (RM18/cawangan).
 *
 * Berasal daripada B-POS ciri `branch_invoices`.
 */
class PosBranchInvoice extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_branch_invoices';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'tenant_id',
        'invoice_no',
        'branch_name',
        'register_id',
        'billing_month',
        'amount_sen',
        'amount_paid_sen',
        'currency',
        'status',
        'due_at',
        'paid_at',
        'payment_method',
        'chip_reference',
        'note',
        'issued_by',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'amount_sen' => 'integer',
        'amount_paid_sen' => 'integer',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'currency' => 'MYR',
        'amount_sen' => 0,
        'amount_paid_sen' => 0,
    ];

    public function jumlah(): float
    {
        return $this->amount_sen / 100;
    }

    public function baki(): float
    {
        return ($this->amount_sen - $this->amount_paid_sen) / 100;
    }

    public function sudahDibayar(): bool
    {
        return $this->status === self::STATUS_PAID || $this->amount_paid_sen >= $this->amount_sen;
    }
}
