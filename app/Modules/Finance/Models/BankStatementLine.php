<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BankStatementLine extends Model
{
    use BelongsToTenant;

    public const UNMATCHED = 'unmatched';
    public const MATCHED = 'matched';
    public const IGNORED = 'ignored';

    protected $fillable = [
        'tenant_id', 'bank_account_id', 'txn_date', 'description', 'reference', 'amount', 'balance',
        'line_hash', 'import_batch', 'status', 'matched_type', 'matched_id', 'match_rule', 'matched_by', 'matched_at',
    ];

    protected $casts = ['txn_date' => 'date', 'amount' => 'decimal:2', 'balance' => 'decimal:2', 'matched_at' => 'datetime'];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
