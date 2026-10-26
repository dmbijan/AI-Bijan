<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id',
        'entry_number',
        'entry_date',
        'status',
        'reference_number',
        'description',
        'narration_bm',
        'fiscal_year',
        'period',
        'source_module',
        'source_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Lines in this journal entry
     */
    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Source document (polymorphic)
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: posted entries only
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope: draft entries only
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Generate next entry number
     */
    public static function generateEntryNumber(): string
    {
        $year = date('Y');
        $prefix = "JE-{$year}-";
        
        $lastEntry = self::where('entry_number', 'like', "{$prefix}%")
            ->orderBy('entry_number', 'desc')
            ->first();
        
        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "{$prefix}{$newNumber}";
    }

    /**
     * Calculate total debits
     */
    public function getTotalDebitsAttribute(): float
    {
        return $this->lines()->sum('debit');
    }

    /**
     * Calculate total credits
     */
    public function getTotalCreditsAttribute(): float
    {
        return $this->lines()->sum('credit');
    }

    /**
     * Check if entry is balanced
     */
    public function isBalanced(): bool
    {
        $debits = (float) $this->total_debits;
        $credits = (float) $this->total_credits;
        
        return abs($debits - $credits) < 0.01;
    }
}
