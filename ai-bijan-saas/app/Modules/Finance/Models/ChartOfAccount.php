<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'name_bm',
        'type',
        'subtype',
        'is_active',
        'is_system',
        'allow_posting',
        'normal_balance',
        'parent_id',
        'is_summary',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'allow_posting' => 'boolean',
        'is_summary' => 'boolean',
        'cost' => 'decimal:2',
    ];

    /**
     * Parent account (hierarchical structure)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Journal entry lines using this account
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\JournalEntryLine::class, 'account_id');
    }

    /**
     * Scope: active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get full code path including parents
     */
    public function getFullPathAttribute(): string
    {
        $parts = [$this->code . ' - ' . $this->name];
        
        if ($this->parent) {
            $parts = array_merge($this->parent->getFullPathAttribute(), $parts);
        }
        
        return implode(' > ', $parts);
    }
}
