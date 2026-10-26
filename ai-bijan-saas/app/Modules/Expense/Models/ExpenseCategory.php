<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Kategori perbelanjaan — pautan ke `chart_of_accounts` (6xxx = Operating Expenses).
 *
 * `merchant_keywords` digunakan untuk auto-klasifikasi AI + heuristik.
 */
class ExpenseCategory extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'expense_categories';

    protected $fillable = [
        'tenant_id',
        'chart_of_account_id',
        'parent_id',
        'code',
        'name',
        'name_bm',
        'description',
        'requires_receipt',
        'receipt_threshold',
        'requires_approval',
        'is_billable',
        'is_tax_deductible',
        'merchant_keywords',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'requires_receipt' => 'boolean',
        'receipt_threshold' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_billable' => 'boolean',
        'is_tax_deductible' => 'boolean',
        'merchant_keywords' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Finance\Models\ChartOfAccount::class, 'chart_of_account_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    /**
     * Adakah resit wajib bagi jumlah tertentu?
     */
    public function resitWajib(float $jumlah): bool
    {
        if (! $this->requires_receipt) {
            return false;
        }

        return $jumlah >= (float) $this->receipt_threshold;
    }

    /**
     * Adakah kategori ini sepadan dengan nama merchant tertentu?
     */
    public function sepadanMerchant(string $merchant): bool
    {
        $merchant = mb_strtolower(trim($merchant));

        if ($merchant === '') {
            return false;
        }

        foreach ((array) ($this->merchant_keywords ?? []) as $kata) {
            $kata = mb_strtolower(trim((string) $kata));

            if ($kata !== '' && str_contains($merchant, $kata)) {
                return true;
            }
        }

        return false;
    }
}
