<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CashBoxEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cash_box_id', 'entry_date', 'type', 'amount', 'category', 'description', 'reference',
        'bank_account_id', 'cash_shift_id', 'is_reconciled', 'created_by',
    ];

    protected $casts = ['entry_date' => 'date', 'amount' => 'decimal:2', 'is_reconciled' => 'boolean'];

    /** Simpan sebagai Y-m-d sahaja supaya tapisan "hingga tarikh" termasuk hari terakhir. */
    public function setEntryDateAttribute($nilai): void
    {
        $this->attributes['entry_date'] = $nilai === null ? null : \Illuminate\Support\Carbon::parse($nilai)->toDateString();
    }
}
