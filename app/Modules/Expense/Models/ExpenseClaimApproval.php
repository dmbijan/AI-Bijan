<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jejak kelulusan tuntutan — audit trail siapa lulus/tolak, bila, berapa.
 */
class ExpenseClaimApproval extends Model
{
    use BelongsToTenant;

    public const ACTION_SUBMIT = 'submit';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_REJECT = 'reject';
    public const ACTION_REIMBURSE = 'reimburse';
    public const ACTION_RETURN = 'return';

    protected $table = 'expense_claim_approvals';

    protected $fillable = [
        'tenant_id',
        'expense_claim_id',
        'approver_user_id',
        'step',
        'action',
        'previous_status',
        'new_status',
        'amount_before',
        'amount_after',
        'comment',
        'ip_address',
    ];

    protected $casts = [
        'step' => 'integer',
        'amount_before' => 'decimal:2',
        'amount_after' => 'decimal:2',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }
}
